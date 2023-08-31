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
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Arrays;

final class Boundaries
{
    /**
     * @param File $file
     * @param int $position
     * @return list{int, int}
     */
    public static function functionBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (
            !in_array(
                $tokens[$position]['code'] ?? null,
                array_keys(Collections::functionDeclarationTokens()),
                true
            )
        ) {
            return [-1, -1];
        }

        return static::startEnd($file, $position);
    }

    /**
     * @param File $file
     * @param int $position
     * @return list{int, int}
     */
    public static function objectBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (!in_array(($tokens[$position]['code'] ?? null), Tokens::$ooScopeTokens, true)) {
            return [-1, -1];
        }

        return static::startEnd($file, $position);
    }

    /**
     * @param File $file
     * @param int $position
     * @return list{int, int}
     */
    public static function arrayBoundaries(File $file, int $position): array
    {
        $openClose = Arrays::getOpenClose($file, $position);
        if (
            !is_array($openClose)
            || !is_int($openClose['opener'] ?? null)
            || !is_int($openClose['closer'] ?? null)
        ) {
            return [-1, -1];
        }

        return [(int)$openClose['opener'], (int)$openClose['closer']];
    }

    /**
     * @param File $file
     * @param int $position
     * @return list{int, int}
     */
    private static function startEnd(File $file, int $position): array
    {
        /** @var array<string, mixed> $token */
        $token = $file->getTokens()[$position] ?? [];
        if (($token['code'] ?? '') === T_FN) {
            $start = $file->findNext(T_FN_ARROW, $position + 1, null, false, null, true);
            if (!$start) {
                return [-1, -1];
            }

            return [$start + 1, $file->findEndOfStatement($start)];
        }

        $start = (int)($token['scope_opener'] ?? 0);
        $end = (int)($token['scope_closer'] ?? 0);
        if (($start <= 0) || ($end <= 0) || ($start >= ($end - 1))) {
            return [-1, -1];
        }

        return [$start, $end];
    }
}
