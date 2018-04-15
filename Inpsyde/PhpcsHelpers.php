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
    public static function classPropertiesTokenIndexes(File $file, int $position): array
    {
        $tokens = $file->getTokens();

        if (!in_array($tokens[$position]['code'] ?? '', [T_CLASS, T_ANON_CLASS, T_TRAIT], true)) {
            return [];
        }

        $opener = $tokens[$position]['scope_opener'] ?? -1;
        $closer = $tokens[$position]['scope_closer'] ?? -1;

        if ($opener <= 0 || $closer <= 0 || $closer <= $opener || $closer <= $position) {
            return [];
        }

        $propertyList = [];
        for ($i = $opener + 1; $i < $closer; $i++) {
            if (self::variableIsProperty($file, $i)) {
                $propertyList[] = $i;
            }
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
        $tokens = $file->getTokens();
        $varToken = $tokens[$position];
        if ($varToken['code'] !== T_VARIABLE) {
            return false;
        }

        $classes = [T_CLASS, T_ANON_CLASS, T_TRAIT];

        $classPointer = $file->findPrevious($classes, $position - 1);
        if (!$classPointer
            || !array_key_exists($classPointer, $tokens)
            || $tokens[$classPointer]['level'] ?? -1 !== (($varToken['level'] ?? -1) - 1)
            || !in_array($tokens[$classPointer]['code'], $classes, true)
        ) {
            return false;
        }

        $opener = $tokens[$classPointer]['scope_opener'] ?? -1;
        $closer = $tokens[$classPointer]['scope_closer'] ?? -1;

        if ($opener <= 0
            || $closer <= 0
            || $closer <= $opener
            || $closer <= $position
            || $opener >= $position
        ) {
            return false;
        }

        $exclude = Tokens::$emptyTokens;
        $exclude[] = T_STATIC;
        $propertyModifierPointer = $file->findPrevious($exclude, $position - 1, null, true);
        if (!$propertyModifierPointer || !array_key_exists($propertyModifierPointer, $tokens)) {
            return false;
        }

        $propertyModifierCode = $tokens[$propertyModifierPointer]['code'] ?? '';
        $modifiers = Tokens::$scopeModifiers;
        $modifiers[] = T_VAR;

        return in_array($propertyModifierCode, $modifiers, true);
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
        if (($functionToken['code'] ?? '') !== T_FUNCTION) {
            return false;
        }

        $classes = [T_CLASS, T_ANON_CLASS, T_TRAIT, T_INTERFACE];
        $classPointer = $file->findPrevious($classes, $position - 1);

        if (!$classPointer
            || !array_key_exists($classPointer, $tokens)
            || $tokens[$classPointer]['level'] ?? -1 !== (($functionToken['level'] ?? -1) - 1)
            || !in_array($tokens[$classPointer]['code'] ?? '', $classes, true)
        ) {
            return false;
        }

        $opener = $tokens[$classPointer]['scope_opener'] ?? -1;
        $closer = $tokens[$classPointer]['scope_closer'] ?? -1;

        return
            $opener > 0
            && $closer > 1
            && $closer > ($position + 3)
            && $opener < $position;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function functionIsArrayAccess(File $file, int $position)
    {
        $token = $file->getTokens()[$position] ?? null;
        if (!$token || $token['code'] !== T_FUNCTION || !self::functionIsMethod($file, $position)) {
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

        return in_array($tokens[$functionCall]['content'] ?? '', $actions, true);
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
            [T_WHITESPACE, T_FINAL, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_ABSTRACT],
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

            if ($tokens[$i]['code'] === T_RETURN && !$scopeClosers->count()) {
                $void = PhpcsHelpers::isVoidReturn($file, $i);
                $null = PhpcsHelpers::isNullReturn($file, $i);
                $void and $voidReturnCount++;
                $null and $nullReturnCount++;
                (!$void && !$null) and $nonVoidReturnCount++;
            }
        }

        return [$nonVoidReturnCount, $voidReturnCount, $nullReturnCount];
    }

    /**
     * @param File $file
     * @param int $returnPosition
     * @param bool $includeNull
     * @return bool
     */
    public static function isVoidReturn(File $file, int $returnPosition, $includeNull = false): bool
    {
        $tokens = $file->getTokens();

        if (($tokens[$returnPosition]['code'] ?? '') !== T_RETURN) {
            return false;
        }

        $returnPosition++;
        $exclude = Tokens::$emptyTokens;
        $includeNull and $exclude[] = T_NULL;

        $nextToReturn = $file->findNext($exclude, $returnPosition, null, true, null, true);

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

    /**
     * @param File $file
     * @param int $position
     * @return array
     */
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
            && !empty($tokens[$end]['scope_closer'])
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
