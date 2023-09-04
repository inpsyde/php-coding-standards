<?php

/*
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (c) 2023 Inpsyde GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Inpsyde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Scopes;

final class Names
{
    public const NAMEABLE_TOKENS = [
        T_CLASS,
        T_NAMESPACE,
        T_TRAIT,
        T_INTERFACE,
        T_CONST,
        T_FUNCTION,
        T_VARIABLE,
        T_ENUM,
        T_ENUM_CASE,
    ];

    /**
     * @param File $file
     * @param int $position
     * @return string|null Null is an error, empty string is fine.
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public static function nameableTokenName(File $file, int $position): ?string
    {
        // phpcs:enable Generic.Metrics.CyclomaticComplexity

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? null;

        if (!in_array($code, self::NAMEABLE_TOKENS, true)) {
            return null;
        }

        if ($code === T_VARIABLE) {
            $name = ltrim((string)($tokens[$position]['content'] ?? ''), '$');

            return ($name === '') ? null : $name;
        }

        if ($code === T_NAMESPACE) {
            return Namespaces::isDeclaration($file, $position)
                ? (Namespaces::getDeclaredName($file, $position) ?: '')
                : null;
        }

        $namePosition = $file->findNext(T_STRING, $position, null, false, null, true);
        $name = ($namePosition === false) ? null : (string)$tokens[$namePosition]['content'];

        return ($name === '') ? null : $name;
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public static function tokenTypeName(File $file, int $position): string
    {
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength
        // phpcs:enable Generic.Metrics.CyclomaticComplexity

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $code = $tokens[$position]['code'] ?? -1;

        switch ($code) {
            case T_CLASS:
            case T_ANON_CLASS:
                return 'Class';
            case T_ENUM:
                return 'Enum';
            case T_ENUM_CASE:
                return 'Enum case';
            case T_TRAIT:
                return 'Trait';
            case T_INTERFACE:
                return 'Interface';
            case T_CONST:
                return 'Constant';
            case T_FUNCTION:
                return 'Function';
            case T_VARIABLE:
                return Scopes::isOOProperty($file, $position) ? 'Property' : 'Variable';
            case T_LNUMBER:
            case T_DNUMBER:
                return 'Number';
            case T_STRING:
                return 'String';
            case T_THIS:
                return 'Property';
            case T_WHITESPACE:
                return 'White space';
        }

        $operators = array_merge(
            array_keys(\PHP_CodeSniffer\Util\Tokens::$arithmeticTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$assignmentTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$equalityTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$arithmeticTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$operators),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$booleanOperators),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$castTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$bracketTokens),
            array_keys(\PHP_CodeSniffer\Util\Tokens::$heredocTokens),
            array_keys(Collections::objectOperators()),
            array_keys(Collections::incrementDecrementOperators()),
            array_keys(Collections::phpOpenTags()),
            array_keys(Collections::namespaceDeclarationClosers()),
            [
                T_COMMA,
                T_ASPERAND,
                T_BACKTICK,
                T_STRING_CONCAT,
                T_COLON,
                T_FN_ARROW,
                T_MATCH_ARROW,
                T_TYPE_UNION,
                T_ATTRIBUTE_END,
                T_TYPE_INTERSECTION,
                T_ELLIPSIS,
            ],
        );

        switch (true) {
            case in_array($code, $operators, true):
                return 'Operator';
            case in_array($code, \PHP_CodeSniffer\Util\Tokens::$textStringTokens, true):
                return 'Text';
            case in_array($code, \PHP_CodeSniffer\Util\Tokens::$commentTokens, true):
                return 'Comment';
        }

        return 'Keyword';
    }
}
