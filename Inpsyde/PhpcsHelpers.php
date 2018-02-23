<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "phpcs-calisthenics-rules" repository
 * found at https://github.com/object-calisthenics
 * Copyright (c) 2014 Doctrine Project
 * released under MIT license.
 */

namespace Inpsyde;

use PHP_CodeSniffer\Exceptions\RuntimeException as CodeSnifferRuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class PhpcsHelpers
{
    const CODE_TO_TYPE_MAP = [
        T_CONST => 'Constant',
        T_CLASS => 'Class',
        T_FUNCTION => 'Function',
        T_TRAIT => 'Trait',
    ];

    /**
     * @param File $file
     * @param int $position
     * @return mixed[]
     */
    public static function classPropertiesTokenIndexes(
        File $file,
        int $position
    ): array {
        $tokens = $file->getTokens();
        $token = $tokens[$position] ?? [];

        if (!array_key_exists('scope_opener', $token)
            || !array_key_exists('scope_closer', $token)
        ) {
            return [];
        }

        $propertyList = [];
        $pointer = (int)$token['scope_opener'];

        while ($pointer) {
            if (self::variableIsProperty($file, $pointer)) {
                $propertyList[] = $pointer;
            }
            $pointer = (int)$file->findNext(
                T_VARIABLE,
                ($pointer + 1),
                $token['scope_closer']
            );
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
        $token = $file->getTokens()[$position];
        if ($token['code'] !== T_VARIABLE) {
            return false;
        }

        $propertyPointer = $file->findPrevious(
            [T_STATIC, T_WHITESPACE, T_COMMENT],
            $position - 1,
            null,
            true
        );

        $propertyPointerToken = $file->getTokens()[$propertyPointer] ?? [];

        return in_array(
            ($propertyPointerToken['code'] ?? ''),
            [T_PRIVATE, T_PROTECTED, T_PUBLIC, T_VAR],
            true
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function functionIsMethod(File $file, int $position)
    {
        $tokens = $file->getTokens();
        $functionToken = $tokens[$position];
        if ($functionToken['code'] !== T_FUNCTION) {
            return false;
        }

        $classPointer = $file->findPrevious(
            [T_CLASS, T_INTERFACE, T_TRAIT],
            $position - 1
        );

        if (!$classPointer) {
            return false;
        }

        $classToken = $tokens[$classPointer];
        if ($classToken['level'] !== $functionToken['level'] - 1) {
            return false;
        }

        $openerPosition = $classToken['scope_opener'] ?? -1;
        $closerPosition = $classToken['scope_closer'] ?? -1;

        return
            $openerPosition > 0
            && $closerPosition > 0
            && $closerPosition > ($openerPosition + 1)
            && $openerPosition < ($position - 1)
            && $closerPosition > $position + 4; // 4 because: (){}
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function functionIsArrayAccess(File $file, int $position)
    {
        $token = $file->getTokens()[$position] ?? null;
        if (!$token || $token['code'] !== T_FUNCTION) {
            return false;
        }

        try {
            return in_array(
                $file->getDeclarationName($position),
                [
                    'offsetSet',
                    'offsetGet',
                    'offsetUnset',
                    'offsetExists',
                ],
                true
            );
        } catch (CodeSnifferRuntimeException $exception) {
            return false;
        }
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isFunctionCall(File $file, int $position): bool
    {
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? -1;
        if (!in_array($code, [T_VARIABLE, T_STRING], true)) {
            return false;
        }

        $nextNonWhitePosition = $file->findNext(
            [T_WHITESPACE],
            $position + 1,
            null,
            true,
            null,
            true
        );

        if (!$nextNonWhitePosition
            || $tokens[$nextNonWhitePosition]['code'] !== T_OPEN_PARENTHESIS
        ) {
            return false;
        }

        $previousNonWhite = $file->findPrevious(
            [T_WHITESPACE],
            $position - 1,
            null,
            true,
            null,
            true
        );

        if ($previousNonWhite && ($tokens[$previousNonWhite]['code'] ?? -1) === T_NS_SEPARATOR) {
            $previousNonWhite = $file->findPrevious(
                [T_WHITESPACE, T_STRING, T_NS_SEPARATOR],
                $previousNonWhite - 1,
                null,
                true,
                null,
                true
            );
        }

        if ($previousNonWhite && $tokens[$previousNonWhite]['code'] === T_NEW) {
            return false;
        }

        $closeParenthesisPosition = $file->findNext(
            [T_CLOSE_PARENTHESIS],
            $position + 2,
            null,
            false,
            null,
            true
        );

        $parenthesisCloserPosition = $tokens[$nextNonWhitePosition]['parenthesis_closer'] ?? -1;

        return
            $closeParenthesisPosition
            && $closeParenthesisPosition === $parenthesisCloserPosition;
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenTypeName(File $file, int $position): string
    {
        $token = $file->getTokens()[$position];
        $tokenCode = $token['code'];
        if (isset(self::CODE_TO_TYPE_MAP[$tokenCode])) {
            return self::CODE_TO_TYPE_MAP[$tokenCode];
        }

        if ($token['code'] === T_VARIABLE) {
            if (self::variableIsProperty($file, $position)) {
                return 'Property';
            }

            return 'Variable';
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
        $name = $file->getTokens()[$position]['content'] ?? '';

        if (strpos($name, '$') === 0) {
            return trim($name, '$');
        }

        $namePosition = $file->findNext(T_STRING, $position, $position + 3);

        return $file->getTokens()[$namePosition]['content'];
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param array ...$types
     * @return array[]
     */
    public static function filterTokensByType(
        int $start,
        int $end,
        File $file,
        ...$types
    ): array {

        return array_filter(
            $file->getTokens(),
            function (array $token, int $position) use ($start, $end, $types): bool {
                return
                    $position >= $start
                    && $position <= $end
                    && in_array($token['code'] ?? '', $types, true);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param File $file
     * @param int $closurePosition
     * @param bool $lookForFilters
     * @param bool $lookForActions
     * @return bool
     */
    public static function isHookClosure(
        File $file,
        int $closurePosition,
        bool $lookForFilters = true,
        bool $lookForActions = true
    ): bool {

        $tokens = $file->getTokens();
        if (($tokens[$closurePosition]['code'] ?? '') !== T_CLOSURE) {
            return false;
        }

        $lookForComma = $file->findPrevious(
            [T_WHITESPACE],
            $closurePosition - 1,
            null,
            true,
            null,
            true
        );

        if (!$lookForComma || ($tokens[$lookForComma]['code'] ?? '') !== T_COMMA) {
            return false;
        }

        $functionCallOpen = $file->findPrevious(
            [T_OPEN_PARENTHESIS],
            $lookForComma - 2,
            null,
            false,
            null,
            true
        );

        if (!$functionCallOpen) {
            return false;
        }

        $functionCall = $file->findPrevious(
            [T_WHITESPACE],
            $functionCallOpen - 1,
            null,
            true,
            null,
            true
        );

        $actions = [];
        $lookForFilters and $actions[] = 'add_filter';
        $lookForActions and $actions[] = 'add_action';

        return in_array(($tokens[$functionCall]['content'] ?? ''), $actions, true);
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return bool
     */
    public static function isHookFunction(File $file, int $functionPosition): bool
    {
        $tokens = $file->getTokens();
        if (($tokens[$functionPosition]['code'] ?? '') !== T_FUNCTION) {
            return false;
        }

        $findDocEnd = $file->findPrevious(
            [T_WHITESPACE, T_FINAL, T_PUBLIC, T_ABSTRACT],
            $functionPosition - 1,
            null,
            true,
            null,
            true
        );

        if (!$findDocEnd || ($tokens[$findDocEnd]['code'] ?? '') !== T_DOC_COMMENT_CLOSE_TAG) {
            return false;
        }

        $findDocStart = $file->findPrevious(
            [T_DOC_COMMENT_OPEN_TAG],
            $findDocEnd,
            null,
            false,
            null,
            true
        );

        if (!$findDocStart
            || ($tokens[$findDocStart]['comment_closer'] ?? '') !== $findDocEnd
        ) {
            return false;
        }

        $docTokens = self::filterTokensByType($findDocStart, $findDocEnd, $file, T_DOC_COMMENT_TAG);

        foreach ($docTokens as $token) {
            if ($token['content'] === '@wp-hook') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array
     */
    public static function functionBoundaries(File $file, int $functionPosition): array
    {
        $tokens = $file->getTokens();
        $functionStart = $tokens[$functionPosition]['scope_opener'] ?? 0;
        $functionEnd = $tokens[$functionPosition]['scope_closer'] ?? 0;
        if (!$functionStart || !$functionEnd || $functionStart >= ($functionEnd - 1)) {
            return [-1, -1];
        }

        return [$functionStart, $functionEnd];
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array
     */
    public static function countReturns(File $file, int $functionPosition): array
    {
        list($functionStart, $functionEnd) = self::functionBoundaries($file, $functionPosition);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return [0, 0];
        }

        $nonVoidReturnCount = $voidReturnCount = $nullReturnCount = 0;
        $scopeClosers = new \SplStack();
        $tokens = $file->getTokens();
        for ($i = $functionStart + 1; $i < $functionEnd; $i++) {
            if ($scopeClosers->count() && $scopeClosers->top() === $i) {
                $scopeClosers->pop();
                continue;
            }
            if (in_array($tokens[$i]['code'], [T_FUNCTION, T_CLOSURE], true)) {
                $scopeClosers->push($tokens[$i]['scope_closer']);
                continue;
            }

            if (!$scopeClosers->count() && $tokens[$i]['code'] === T_RETURN) {
                PhpcsHelpers::isVoidReturn($file, $i) ? $voidReturnCount++ : $nonVoidReturnCount++;
                PhpcsHelpers::isNullReturn($file, $i) and $nullReturnCount++;
            }
        }

        return [$nonVoidReturnCount, $voidReturnCount, $nullReturnCount];
    }

    /**
     * @param File $file
     * @param int $returnPosition
     * @return bool
     */
    public static function isVoidReturn(File $file, int $returnPosition): bool
    {
        $tokens = $file->getTokens();

        if (($tokens[$returnPosition]['code'] ?? '') !== T_RETURN) {
            return false;
        }

        $returnPosition++;
        $nextToReturn = $file->findNext([T_WHITESPACE], $returnPosition, null, true, null, true);
        $nextToReturnType = $tokens[$nextToReturn]['code'] ?? '';

        return in_array($nextToReturnType, [T_SEMICOLON, T_NULL], true);
    }

    /**
     * @param File $file
     * @param int $returnPosition
     * @return bool
     */
    public static function isNullReturn(File $file, int $returnPosition): bool
    {
        $tokens = $file->getTokens();

        if (($tokens[$returnPosition]['code'] ?? '') !== T_RETURN) {
            return false;
        }

        $returnPosition++;
        $nextToReturn = $file->findNext([T_WHITESPACE], $returnPosition, null, true, null, true);
        $nextToReturnType = $tokens[$nextToReturn]['code'] ?? '';

        return $nextToReturnType === T_NULL;
    }

    /**
     * @param string $tag
     * @param File $file
     * @param int $functionPosition
     * @return string[]
     */
    public static function functionDocBlockTag(
        string $tag,
        File $file,
        int $functionPosition
    ): array {

        $tokens = $file->getTokens();
        if (!array_key_exists($functionPosition, $tokens)
            || !in_array($tokens[$functionPosition]['code'], [T_FUNCTION, T_CLOSURE], true)
        ) {
            return [];
        }

        $exclude = array_values(Tokens::$methodPrefixes);
        $exclude[] = T_WHITESPACE;

        $lastBeforeFunc = $file->findPrevious($exclude, $functionPosition - 1, null, true);

        if (!$lastBeforeFunc
            || !array_key_exists($lastBeforeFunc, $tokens)
            || $tokens[$lastBeforeFunc]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            || empty($tokens[$lastBeforeFunc]['comment_opener'])
            || $tokens[$lastBeforeFunc]['comment_opener'] >= $lastBeforeFunc
        ) {
            return [];
        }

        $tags = [];
        $inTag = false;
        $start = $tokens[$lastBeforeFunc]['comment_opener'] + 1;
        $end = $lastBeforeFunc - 1;

        for ($i = $start; $i < $end; $i++) {

            if ($inTag && $tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                $tags[] .= $tokens[$i]['content'];
                continue;
            }

            if ($inTag && $tokens[$i]['code'] !== T_DOC_COMMENT_WHITESPACE) {
                $inTag = false;
                continue;
            }

            if ($tokens[$i]['code'] === T_DOC_COMMENT_TAG
                && (ltrim($tokens[$i]['content'], '@') === ltrim($tag, '@'))
            ) {
                $inTag = true;
            }
        }

        return $tags;
    }

    public static function findNamespace(File $file, int $position): array
    {
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

        if (!$end || !array_key_exists($end, $tokens)) {
            return [null, null];
        }

        if ($tokens[$end]['code'] === T_OPEN_CURLY_BRACKET
            && ! empty($tokens[$end]['scope_closer'])
            && $tokens[$end]['scope_closer'] < $position
        ) {
            return [null, null];
        }

        $namespace = '';
        for ($i = $namespacePos + 1; $i < $end; $i++) {
            $code = $tokens[$i]['code'] ?? null;
            if (in_array($code, [T_STRING, T_NS_SEPARATOR], true)) {
                $namespace .= $tokens[$i]['content'] ?? '';
            }
        }

        return [$namespacePos, $namespace];
    }
}
