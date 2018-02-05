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

namespace Inpsyde\InpsydeCodingStandard;

use PHP_CodeSniffer\Files\File;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class Helpers
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
            if (self::isProperty($file, $pointer)) {
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
     * @param int $variablePosition
     * @return bool
     */
    public static function isProperty(File $file, int $variablePosition): bool
    {
        $propertyPointer = $file->findPrevious(
            [T_STATIC, T_WHITESPACE, T_COMMENT],
            $variablePosition - 1,
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
            if (self::isProperty($file, $position)) {
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

        $returnTokens = self::filterTokensByType(
            $functionStart,
            $functionEnd,
            $file,
            T_RETURN
        );

        if (!$returnTokens) {
            return [0, 0];
        }

        $nonVoidReturnCount = $voidReturnCount = 0;
        $scopeClosers = [];
        foreach ($returnTokens as $i => $token) {
            if ($scopeClosers && $i === $scopeClosers[0]) {
                array_shift($scopeClosers);
            }
            if ($token['type'] === 'T_FUNCTION') {
                array_unshift($scopeClosers, $token['scope_closer']);
            }
            if (!$scopeClosers) {
                Helpers::isVoidReturn($file, $i) ? $voidReturnCount++ : $nonVoidReturnCount++;
            }
        }

        return [$nonVoidReturnCount, $voidReturnCount];
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

        return $nextToReturn && ($tokens[$nextToReturn]['type'] ?? '') === 'T_SEMICOLON';
    }
}
