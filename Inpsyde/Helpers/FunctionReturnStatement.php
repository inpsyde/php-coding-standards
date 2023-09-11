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

final class FunctionReturnStatement
{
    /**
     * @param File $file
     * @param int $position
     * @return array{nonEmpty:int, void:int, null:int, total:int}
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     */
    public static function allInfo(File $file, int $position): array
    {
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        $returnCount = ['nonEmpty' => 0, 'void' => 0, 'null' => 0, 'total' => -1];

        [$start, $end] = Boundaries::functionBoundaries($file, $position);
        if (($start < 0) || ($end <= 0)) {
            return $returnCount;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if ($tokens[$position]['code'] === T_FN) {
            $returnCount['total'] = 1;
            $key = static::isNull($file, $position) ? 'null' : 'nonEmpty';
            $returnCount[$key] = 1;

            return $returnCount;
        }

        $returnCount['total'] = 0;

        $pos = $start + 1;
        while ($pos < $end) {
            [, $innerFunctionEnd] = Boundaries::functionBoundaries($file, $pos);
            [, $innerClassEnd] = Boundaries::objectBoundaries($file, $pos);
            if (($innerFunctionEnd > 0) || ($innerClassEnd > 0)) {
                $pos = ($innerFunctionEnd > 0) ? $innerFunctionEnd + 1 : $innerClassEnd + 1;
                continue;
            }

            if ($tokens[$pos]['code'] === T_RETURN) {
                $returnCount['total']++;
                $void = static::isVoid($file, $pos);
                $null = !$void && static::isNull($file, $pos);
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
     * @param int $position
     * @return bool
     */
    public static function isVoid(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (($tokens[$position]['code'] ?? '') !== T_RETURN) {
            return false;
        }

        $exclude = Tokens::$emptyTokens;

        $nextToReturn = $file->findNext($exclude, $position + 1, null, true, null, true);

        return ($tokens[$nextToReturn]['code'] ?? '') === T_SEMICOLON;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isNull(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? '';

        if (($code !== T_RETURN) && ($code !== T_FN)) {
            return false;
        }

        if ($code === T_FN) {
            $position = $file->findNext(T_FN_ARROW, $position + 1, null, false, null, true);
            if (!$position) {
                return false;
            }
        }

        $returnString = Misc::tokensSubsetToString(
            $position + 1,
            $file->findEndOfStatement($position + 1) - 1,
            $file,
            Tokens::$emptyTokens,
            true
        );

        return strtolower($returnString) === 'null';
    }
}
