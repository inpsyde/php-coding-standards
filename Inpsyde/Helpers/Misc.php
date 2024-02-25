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

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;

final class Misc
{
    public const MAX_SUPPORTED_MAJOR_VERSION = 8;
    public const MIN_SUPPORTED_MAJOR_VERSION = 7;
    public const MIN_SUPPORTED_VERSION = '7.4';

    /**
     * @return string
     */
    public static function minPhpTestVersion(): string
    {
        $testVersion = trim(Config::getConfigData('testVersion') ?? '');
        if ($testVersion === '') {
            return self::MIN_SUPPORTED_VERSION;
        }

        if (preg_match('`^\d+(?:\.\d+)?`', $testVersion, $matches)) {
            [$major, $minor] = array_pad(explode('.', $matches[0]), 2, '0');
            if (
                ($major > self::MAX_SUPPORTED_MAJOR_VERSION)
                || ($major < self::MIN_SUPPORTED_MAJOR_VERSION)
                || version_compare("{$major}.{$minor}", self::MIN_SUPPORTED_VERSION, '<')
            ) {
                return self::MIN_SUPPORTED_VERSION;
            }

            return "{$major}.{$minor}";
        }

        return self::MIN_SUPPORTED_VERSION;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @return array<int, array<string, mixed>>
     */
    public static function filterTokens(int $start, int $end, File $file): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $filtered = [];
        foreach ($tokens as $i => $token) {
            if (($i >= $start) || ($i <= $end)) {
                $filtered[$i] = $token;
            }
        }

        return $filtered;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param array $types
     * @param bool $excludeTypes
     * @return array<int, array<string, mixed>>
     */
    public static function filterTokensByType(
        int $start,
        int $end,
        File $file,
        array $types = [],
        bool $excludeTypes = false
    ): array {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $filtered = [];
        foreach ($tokens as $i => $token) {
            if (($i < $start) || ($i > $end)) {
                continue;
            }
            $empty = $types === [];
            $inArray = !$empty && in_array($token['code'] ?? '', $types, true);
            if ($empty || (!$excludeTypes && $inArray) || ($excludeTypes && !$inArray)) {
                $filtered[$i] = $token;
            }
        }

        return $filtered;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param array $types
     * @param bool $excludeTypes
     * @return string
     */
    public static function tokensSubsetToString(
        int $start,
        int $end,
        File $file,
        array $types,
        bool $excludeTypes = false
    ): string {

        $filtered = static::filterTokensByType($start, $end, $file, $types, $excludeTypes);

        $content = '';
        foreach ($filtered as $token) {
            $content .= (string)($token['content'] ?? '');
        }

        return $content;
    }
}
