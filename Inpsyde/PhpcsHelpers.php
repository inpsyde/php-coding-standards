<?php

declare(strict_types=1);

namespace Inpsyde;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class PhpcsHelpers
{
    /**
     * @param File $file
     * @param int $position
     * @return array<int>
     */
    public static function allPropertiesTokenPositions(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? '';

        if (!in_array($code, Tokens::$ooScopeTokens, true)) {
            return [];
        }

        $opener = (int)($tokens[$position]['scope_opener'] ?? -1);
        $closer = (int)($tokens[$position]['scope_closer'] ?? -1);

        if ($opener <= 0 || $closer <= 0 || $closer <= $opener || $closer <= $position) {
            return [];
        }

        $propertyList = [];
        $pos = $opener + 1;
        while ($pos < $closer) {
            if ($tokens[$pos]['code'] === T_CLASS || $tokens[$pos]['code'] === T_ANON_CLASS) {
                $pos = ((int)($tokens[$pos]['scope_closer'] ?? $pos)) + 1;
                continue;
            }

            if (self::variableIsProperty($file, $pos)) {
                $propertyList[] = $pos;
            }

            $pos++;
        }

        return $propertyList;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function variableIsProperty(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (
            (($tokens[$position]['code'] ?? '') !== T_VARIABLE)
            || !static::hasOopCondition($file, $position)
        ) {
            return false;
        }

        $prev = $file->findPrevious(Tokens::$emptyTokens, $position - 1, null, true, null, true);

        $modifiers = [T_PRIVATE, T_PUBLIC, T_PROTECTED, T_STATIC, T_VAR];

        return $prev && in_array($tokens[$prev]['code'], $modifiers, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function functionIsMethod(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        return (($tokens[$position]['code'] ?? '') === T_FUNCTION)
            && static::hasOopCondition($file, $position);
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function hasOopCondition(File $file, int $position): bool
    {
        return static::findOopContext($file, $position) !== 0;
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public static function findOopContext(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (
            empty($tokens[$position]['conditions'])
            || ((int)($tokens[$position]['level'] ?? 0) <= 0)
            || !is_array($tokens[$position]['conditions'])
        ) {
            return 0;
        }

        $targetLevel = (int)$tokens[$position]['level'] - 1;

        foreach ($tokens[$position]['conditions'] as $condPosition => $condCode) {
            $condLevel = (int)($tokens[$condPosition]['level'] ?? -1);

            if (
                in_array($condCode, Tokens::$ooScopeTokens, true)
                && ($condLevel === $targetLevel)
            ) {
                return (int)$condPosition;
            }
        }

        return 0;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function functionIsArrayAccess(File $file, int $position): bool
    {
        $methods = ['offsetSet', 'offsetGet', 'offsetUnset', 'offsetExists'];

        return self::functionIsMethod($file, $position)
            && in_array($file->getDeclarationName($position), $methods, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function looksLikeFunctionCall(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? -1;
        if (!in_array($code, [T_VARIABLE, T_STRING], true)) {
            return false;
        }

        $empty = Tokens::$emptyTokens;

        $callOpen = $file->findNext($empty, $position + 1, null, true, null, true);
        if (!$callOpen || $tokens[$callOpen]['code'] !== T_OPEN_PARENTHESIS) {
            return false;
        }

        $prevExclude = $empty;
        $prevMeaningful = $file->findPrevious($prevExclude, $position - 1, null, true, null, true);

        if ($prevMeaningful && ($tokens[$prevMeaningful]['code'] ?? -1) === T_NS_SEPARATOR) {
            $prevExclude = array_merge($prevExclude, [T_STRING, T_NS_SEPARATOR]);
            $prevStart = $prevMeaningful - 1;
            $prevMeaningful = $file->findPrevious($prevExclude, $prevStart, null, true, null, true);
        }

        $prevMeaningfulCode = $prevMeaningful ? $tokens[$prevMeaningful]['code'] : null;
        if ($prevMeaningfulCode && in_array($prevMeaningfulCode, [T_NEW, T_FUNCTION], true)) {
            return false;
        }

        $callClose = $file->findNext([T_CLOSE_PARENTHESIS], $callOpen + 1, null, false, null, true);
        $expectedCallClose = $tokens[$callOpen]['parenthesis_closer'] ?? -1;

        return $callClose && $callClose === $expectedCallClose;
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenTypeName(File $file, int $position): string
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        switch ((int)($tokens[$position]['code'] ?? -1)) {
            case T_CLASS:
            case T_ANON_CLASS:
                return 'Class';
            case T_TRAIT:
                return 'Trait';
            case T_INTERFACE:
                return 'Interface';
            case T_CONST:
                return 'Constant';
            case T_FUNCTION:
                return 'Function';
            case T_VARIABLE:
                return self::variableIsProperty($file, $position) ? 'Property' : 'Variable';
        }

        return '';
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenName(File $file, int $position): string
    {
        static $nameable;
        $nameable or $nameable = [T_CLASS, T_TRAIT, T_INTERFACE, T_CONST, T_FUNCTION, T_VARIABLE];

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? null;

        if (!in_array($code, (array)$nameable, true)) {
            return '';
        } elseif ($code === T_VARIABLE) {
            return ltrim((string)($tokens[$position]['content'] ?? ''), '$');
        }

        $namePosition = $file->findNext(T_STRING, $position, null, false, null, true);

        return $namePosition === false ? '' : (string)$tokens[$namePosition]['content'];
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param int|string ...$types
     * @return array<int, array<string, mixed>>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public static function filterTokensByType(int $start, int $end, File $file, ...$types): array
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $filtered = [];
        foreach ($tokens as $position => $token) {
            if (
                ($position >= $start)
                && ($position <= $end)
                && in_array($token['code'] ?? '', $types, true)
            ) {
                $filtered[$position] = $token;
            }
        }

        return $filtered;
    }

    /**
     * @param File $file
     * @param int $position
     * @param bool $lookForFilters
     * @param bool $lookForActions
     * @return bool
     */
    public static function isHookClosure(
        File $file,
        int $position,
        bool $lookForFilters = true,
        bool $lookForActions = true
    ): bool {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (($tokens[$position]['code'] ?? '') !== T_CLOSURE) {
            return false;
        }

        $empty = Tokens::$emptyTokens;

        $exclude = $empty;
        $exclude[] = T_STATIC;
        $commaPos = $file->findPrevious($exclude, $position - 1, null, true, null, true);
        if (!$commaPos || ($tokens[$commaPos]['code'] ?? '') !== T_COMMA) {
            return false;
        }

        $openType = [T_OPEN_PARENTHESIS];
        $openCallPos = $file->findPrevious($openType, $commaPos - 2, null, false, null, true);
        if (!$openCallPos) {
            return false;
        }

        $functionCallPos = $file->findPrevious($empty, $openCallPos - 1, null, true, null, true);
        if (!$functionCallPos || $tokens[$functionCallPos]['code'] !== T_STRING) {
            return false;
        }

        $actions = [];
        $lookForFilters and $actions[] = 'add_filter';
        $lookForActions and $actions[] = 'add_action';

        return in_array($tokens[$functionCallPos]['content'] ?? '', $actions, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @param bool $normalizeContent
     * @return array<string, array<string>>
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public static function functionDocBlockTags(
        File $file,
        int $position,
        bool $normalizeContent = true
    ): array {
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength
        // phpcs:enable Generic.Metrics.CyclomaticComplexity

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (
            !array_key_exists($position, $tokens)
            || !in_array($tokens[$position]['code'], [T_FUNCTION, T_CLOSURE], true)
        ) {
            return [];
        }

        $closeType = T_DOC_COMMENT_CLOSE_TAG;
        $closeTag = $file->findPrevious($closeType, $position - 1, null, false, null, true);

        if (!$closeTag || empty($tokens[$closeTag]['comment_opener'])) {
            return [];
        }

        $functionLine = (int)($tokens[$position]['line'] ?? -1);
        $closeLine = (int)($tokens[$closeTag]['line'] ?? -1);
        if ($closeLine !== ($functionLine - 1)) {
            return [];
        }

        /** @var array<int, array{string, string}> $tags */
        $tags = [];
        $start = (int)$tokens[$closeTag]['comment_opener'] + 1;
        $key = -1;
        $inTag = false;

        for ($i = $start; $i < $closeTag; $i++) {
            $code = $tokens[$i]['code'];
            if ($code === T_DOC_COMMENT_STAR) {
                continue;
            }

            $content = (string)$tokens[$i]['content'];
            if (($tokens[$i]['code'] === T_DOC_COMMENT_TAG)) {
                $inTag = true;
                $key++;
                $tags[$key] = [$content, ''];
                continue;
            }

            if ($inTag) {
                $tags[$key][1] .= $content;
            }
        }

        $normalizedTags = [];
        static $rand;
        $rand or $rand = bin2hex(random_bytes(3));
        foreach ($tags as list($tagName, $tagContent)) {
            empty($normalizedTags[$tagName]) and $normalizedTags[$tagName] = [];
            if (!$normalizeContent) {
                $normalizedTags[$tagName][] = $tagContent;
                continue;
            }

            $lines = array_filter(array_map('trim', explode("\n", $tagContent)));
            $normalized = preg_replace('~\s+~', ' ', implode("%LB%{$rand}%LB%", $lines)) ?? '';
            $normalizedTags[$tagName][] = trim(str_replace("%LB%{$rand}%LB%", "\n", $normalized));
        }

        return $normalizedTags;
    }

    /**
     * @param string $tag
     * @param File $file
     * @param int $position
     * @return array<string>
     */
    public static function functionDocBlockTag(string $tag, File $file, int $position): array
    {
        $tagName = '@' . ltrim($tag, '@');
        $tags = static::functionDocBlockTags($file, $position);
        if (empty($tags[$tagName])) {
            return [];
        }

        return $tags[$tagName];
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array<string, array<string>>
     */
    public static function functionDocBlockParamTypes(File $file, int $functionPosition): array
    {
        $params = PhpcsHelpers::functionDocBlockTag('@param', $file, $functionPosition);
        if (!$params) {
            return [];
        }

        $types = [];
        foreach ($params as $param) {
            preg_match('~^([^$]+)\s*(\$(?:[^\s]+))~', trim($param), $matches);
            if (empty($matches[1]) || empty($matches[2])) {
                continue;
            }

            $types[$matches[2]] = array_map('trim', explode('|', $matches[1]));
        }

        return $types;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isHookFunction(File $file, int $position): bool
    {
        return (bool)self::functionDocBlockTag('@wp-hook', $file, $position);
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function functionBody(File $file, int $position): string
    {
        list($start, $end) = static::functionBoundaries($file, $position);
        if ($start < 0 || $end < 0) {
            return '';
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $body = '';
        for ($i = $start + 1; $i < $end; $i++) {
            $body .= (string)$tokens[$i]['content'];
        }

        return $body;
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    public static function functionBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (!in_array(($tokens[$position]['code'] ?? null), [T_FUNCTION, T_CLOSURE], true)) {
            return [-1, -1];
        }

        $functionStart = (int)($tokens[$position]['scope_opener'] ?? 0);
        $functionEnd = (int)($tokens[$position]['scope_closer'] ?? 0);
        if ($functionStart <= 0 || $functionEnd <= 0 || $functionStart >= ($functionEnd - 1)) {
            return [-1, -1];
        }

        return [$functionStart, $functionEnd];
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    public static function classBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (!in_array(($tokens[$position]['code'] ?? null), Tokens::$ooScopeTokens, true)) {
            return [-1, -1];
        }

        $start = (int)($tokens[$position]['scope_opener'] ?? 0);
        $end = (int)($tokens[$position]['scope_closer'] ?? 0);
        if ($start <= 0 || $end <= 0 || $start >= ($end - 1)) {
            return [-1, -1];
        }

        return [$start, $end];
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{nonEmpty:int, void:int, null:int, total:int}
     */
    public static function returnsCountInfo(File $file, int $position): array
    {
        $returnCount = ['nonEmpty' => 0, 'void' => 0, 'null' => 0, 'total' => 0];

        list($start, $end) = self::functionBoundaries($file, $position);
        if ($start < 0 || $end <= 0) {
            return $returnCount;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $pos = $start + 1;
        while ($pos < $end) {
            list(, $innerFunctionEnd) = self::functionBoundaries($file, $pos);
            list(, $innerClassEnd) = self::classBoundaries($file, $pos);
            if ($innerFunctionEnd > 0 || $innerClassEnd > 0) {
                $pos = ($innerFunctionEnd > 0) ? $innerFunctionEnd + 1 : $innerClassEnd + 1;
                continue;
            }

            if ($tokens[$pos]['code'] === T_RETURN) {
                $returnCount['total']++;
                $void = PhpcsHelpers::isVoidReturn($file, $pos);
                $null = PhpcsHelpers::isNullReturn($file, $pos);
                $void and $returnCount['void']++;
                $null and $returnCount['null']++;
                (!$void && !$null) and $returnCount['nonEmpty']++;
            }

            $pos++;
        }

        return $returnCount;
    }

    /**
     * @param File $file
     * @param int $returnPosition
     * @param bool $includeNull
     * @return bool
     */
    public static function isVoidReturn(
        File $file,
        int $returnPosition,
        bool $includeNull = false
    ): bool {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (($tokens[$returnPosition]['code'] ?? '') !== T_RETURN) {
            return false;
        }

        $exclude = Tokens::$emptyTokens;
        $includeNull and $exclude[] = T_NULL;

        $nextToReturn = $file->findNext($exclude, $returnPosition + 1, null, true, null, true);

        return ($tokens[$nextToReturn]['code'] ?? '') === T_SEMICOLON;
    }

    /**
     * @param File $file
     * @param int $returnPosition
     * @return bool
     */
    public static function isNullReturn(File $file, int $returnPosition): bool
    {
        return
            !self::isVoidReturn($file, $returnPosition, false)
            && self::isVoidReturn($file, $returnPosition, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{null, null}|array{int, string}
     */
    public static function findNamespace(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $namespacePos = $file->findPrevious([T_NAMESPACE], $position - 1);
        if (!$namespacePos || !array_key_exists($namespacePos, $tokens)) {
            return [null, null];
        }

        $end = $file->findNext(
            [T_SEMICOLON, T_OPEN_CURLY_BRACKET],
            $namespacePos + 1,
            null,
            false,
            null,
            true
        );

        if (!$end) {
            return [null, null];
        }

        if (
            $tokens[$end]['code'] === T_OPEN_CURLY_BRACKET
            && !empty($tokens[$end]['scope_closer'])
            && $tokens[$end]['scope_closer'] < $position
        ) {
            return [null, null];
        }

        $namespace = '';
        for ($i = $namespacePos + 1; $i < $end; $i++) {
            $code = $tokens[$i]['code'] ?? null;
            if (in_array($code, [T_STRING, T_NS_SEPARATOR], true)) {
                $namespace .= (string)($tokens[$i]['content'] ?? '');
            }
        }

        return [$namespacePos, $namespace];
    }

    /**
     * @return string
     */
    public static function minPhpTestVersion(): string
    {
        $testVersion = trim(Config::getConfigData('testVersion') ?: '');
        if (!$testVersion) {
            return '';
        }

        preg_match('`^(\d+\.\d+)(?:\s*-\s*(?:\d+\.\d+)?)?$`', $testVersion, $matches);

        return $matches[1] ?? '';
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isUntypedPsrMethod(File $file, int $position): bool
    {
        $tokens = $file->getTokens();

        if (($tokens[$position]['type'] ?? '') !== 'T_FUNCTION') {
            return false;
        }

        $classPos = static::findOopContext($file, $position);
        $type = $tokens[$classPos]['type'] ?? null;
        if (!$classPos || !in_array($type, ['T_CLASS', 'T_ANON_CLASS'], true)) {
            return false;
        }

        $names = $file->findImplementedInterfaceNames($classPos);

        if (!$names) {
            return false;
        }

        /** @var array<string> $psrInterfaces */
        static $psrInterfaces;
        $psrInterfaces or $psrInterfaces = [
            'LoggerInterface',
            'CacheItemInterface',
            'CacheItemPoolInterface',
            'MessageInterface',
            'RequestInterface',
            'ServerRequestInterface',
            'ResponseInterface',
            'StreamInterface',
            'UriInterface',
            'UploadedFileInterface',
            'ContainerInterface',
            'LinkInterface',
            'EvolvableLinkInterface',
            'LinkProviderInterface',
            'EvolvableLinkProviderInterface',
            'CacheInterface',
            'RequestFactoryInterface',
            'ResponseFactoryInterface',
            'ServerRequestFactoryInterface',
            'StreamFactoryInterface',
        ];

        /** @var string $name */
        foreach ($names as $name) {
            $lastName = array_slice(explode('\\', $name), -1, 1)[0];
            if (in_array($lastName, $psrInterfaces, true)) {
                return true;
            }
        }

        return false;
    }
}
