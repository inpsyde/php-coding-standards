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

namespace Inpsyde\CodingStandard\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Conditions;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

final class Functions
{
    /**
     * @param File $file
     * @param int $position
     * @param bool $allowStatic
     * @return bool
     */
    public static function looksLikeFunctionCall(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? -1;
        $types = array_keys(Tokens::$functionNameTokens);
        $types[] = T_VARIABLE;

        if (
            !in_array($code, $types, true)
            || (($code === T_VARIABLE) && Scopes::isOOProperty($file, $position))
        ) {
            return false;
        }

        $callOpen = $file->findNext(Tokens::$emptyTokens, $position + 1, null, true, null, true);
        if (($callOpen === false) || $tokens[$callOpen]['code'] !== T_OPEN_PARENTHESIS) {
            return false;
        }

        $prevExclude = Tokens::$emptyTokens;
        $prevMeaningful = $file->findPrevious($prevExclude, $position - 1, null, true, null, true);

        if (
            ($prevMeaningful !== false)
            && ($tokens[$prevMeaningful]['code'] ?? -1) === T_NS_SEPARATOR
        ) {
            $prevExclude = array_merge($prevExclude, [T_STRING, T_NS_SEPARATOR]);
            $prevStart = $prevMeaningful - 1;
            $prevMeaningful = $file->findPrevious($prevExclude, $prevStart, null, true, null, true);
        }

        $prevMeaningfulCode = ($prevMeaningful !== false)
            ? $tokens[$prevMeaningful]['code']
            : null;
        if (in_array($prevMeaningfulCode, [T_NEW, T_FUNCTION], true)) {
            return false;
        }

        $callClose = $file->findNext([T_CLOSE_PARENTHESIS], $callOpen + 1, null, false, null, true);
        $expectedCallClose = $tokens[$callOpen]['parenthesis_closer'] ?? -1;

        return ($callClose !== false) && ($callClose === $expectedCallClose);
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isArrayAccess(File $file, int $position): bool
    {
        $methods = ['offsetSet', 'offsetGet', 'offsetUnset', 'offsetExists'];

        return Scopes::isOOMethod($file, $position)
            && in_array(FunctionDeclarations::getName($file, $position), $methods, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function bodyContent(File $file, int $position): string
    {
        [$start, $end] = Boundaries::functionBoundaries($file, $position);

        if (($start < 0) || ($end < 0)) {
            return '';
        }

        return Misc::tokensSubsetToString($start + 1, $end - 1, $file, []);
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public static function countYieldInBody(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        if ($tokens[$position]['code'] === T_FN) {
            return 0;
        }

        [$start, $end] = Boundaries::functionBoundaries($file, $position);
        if (($start < 0) || ($end <= 0)) {
            return 0;
        }

        $found = 0;

        $pos = $start + 1;
        while ($pos < $end) {
            [, $innerFunctionEnd] = Boundaries::functionBoundaries($file, $pos);
            [, $innerClassEnd] = Boundaries::objectBoundaries($file, $pos);
            if (($innerFunctionEnd > 0) || ($innerClassEnd > 0)) {
                $pos = ($innerFunctionEnd > 0) ? $innerFunctionEnd + 1 : $innerClassEnd + 1;
                continue;
            }

            if (($tokens[$pos]['code'] === T_YIELD) || ($tokens[$pos]['code'] === T_YIELD_FROM)) {
                $found++;
            }

            $pos++;
        }

        return $found;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isPsrMethod(File $file, int $position): bool
    {
        if (!Scopes::isOOMethod($file, $position)) {
            return false;
        }

        $tokens = $file->getTokens();
        $scopes = [T_CLASS, T_ANON_CLASS];

        $classPos = Conditions::getLastCondition($file, $position, $scopes);
        $type = is_int($classPos) ? ($tokens[$classPos]['code'] ?? null) : null;
        if (!in_array($type, $scopes, true)) {
            return false;
        }

        /** @var int $classPos */
        $interfaces = Objects::allInterfacesFullyQualifiedNames($file, $classPos) ?? [];
        foreach ($interfaces as $interface) {
            if (stripos($interface, '\\Psr\\') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sometimes we don't declare the type because we can't, e.g. is the type is "mixed" or
     * it is union, and we are using PHP 7.4.
     * In those cases, we expect to document the type via doc bloc, and this functions aims
     * to return true.
     *
     * @param list<string> $docTypes
     * @param bool $return
     * @return bool
     */
    public static function isNonDeclarableDocBlockType(array $docTypes, bool $return): bool
    {
        if ($docTypes === []) {
            return false;
        }

        $count = count($docTypes);

        $minVer = Misc::minPhpTestVersion();
        $is80 = version_compare($minVer, '8.0', '>=');
        $is81 = version_compare($minVer, '8.1', '>=');
        $is82 = version_compare($minVer, '8.2', '>=');
        $isIntersection = strpos(implode('|', $docTypes), '&') !== false;

        // If "never" is there, this is valid for return types and PHP < 8.1,
        // not valid for argument types.
        if (in_array('never', $docTypes, true)) {
            return $return && !$is81;
        }

        if ($count > 1) {
            // Union type with "mixed" make no sense, just use "mixed"
            if (in_array('mixed', $docTypes, true)) {
                return false;
            }
            // Union type without null, valid if we're not on PHP < 8.0, or on PHP < 8.2 and
            // there's an intersection (DNF)
            if (!in_array('null', $docTypes, true)) {
                return !$is80 || (!$is82 && $isIntersection);
            }
            $docTypes = array_diff($docTypes, ['null']);
            $count = count($docTypes);
        }

        // Union type with "null" plus something else, valid if we're not on PHP < 8.0 or
        // on PHP < 8.2 and there's an intersection (DNF)
        if ($count > 1) {
            return !$is80 || (!$is82 && $isIntersection);
        }

        $singleDocType = reset($docTypes);

        // If the single type is "mixed" is valid if we are on PHP < 8.0.
        // If the single type is "null" is valid if we are on PHP < 8.2.
        // If the single is an intersection, is valid if we are on PHP < 8.1
        return (($singleDocType === 'mixed') && !$is80)
            || (($singleDocType === 'null') && !$is82)
            || ($isIntersection && !$is81);
    }
}
