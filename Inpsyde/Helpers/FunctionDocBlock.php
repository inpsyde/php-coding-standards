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

final class FunctionDocBlock
{
    /**
     * @param File $file
     * @param int $position
     * @param bool $normalizeContent
     * @return array<string, list<string>>
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    public static function allTags(
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
            || !in_array($tokens[$position]['code'], [T_FUNCTION, T_CLOSURE, T_FN], true)
        ) {
            return [];
        }

        $closeType = T_DOC_COMMENT_CLOSE_TAG;
        $closeTag = $file->findPrevious($closeType, $position - 1, null, false, null, true);

        if (($closeTag === false) || !isset($tokens[$closeTag]['comment_opener'])) {
            return [];
        }

        $functionLine = (int)($tokens[$position]['line'] ?? -1);
        $closeLine = (int)($tokens[$closeTag]['line'] ?? -1);
        if ($closeLine !== ($functionLine - 1)) {
            return [];
        }

        /** @var array<int, array{string, string}> $tags */
        $tags = [];
        $start = (int) $tokens[$closeTag]['comment_opener'] + 1;
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
        foreach ($tags as [$tagName, $tagContent]) {
            isset($normalizedTags[$tagName]) or $normalizedTags[$tagName] = [];
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
     * @return list<string>
     */
    public static function tag(string $tag, File $file, int $position): array
    {
        $tagName = '@' . ltrim($tag, '@');
        $tags = static::allTags($file, $position);
        if (empty($tags[$tagName])) {
            return [];
        }

        return $tags[$tagName];
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array<string, list<string>>
     */
    public static function allParamTypes(File $file, int $functionPosition): array
    {
        $params = static::tag('@param', $file, $functionPosition);
        if (!$params) {
            return [];
        }

        $types = [];
        foreach ($params as $param) {
            preg_match('~^([^$]+)\s*(\$\S+)~', trim($param), $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $types[$matches[2]] = static::normalizeTypesString($matches[1]);
            }
        }

        return $types;
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return list<string>
     */
    public static function normalizeTypesString(string $typesString): array
    {
        $typesString = preg_replace('~\s+~', '', $typesString);
        $splitTypes = explode('|', $typesString ?? '');
        $normalized = [];
        $hasNull = false;
        foreach ($splitTypes as $splitType) {
            if (strpos($splitType, '&') !== false) {
                $splitType = rtrim(ltrim($splitType, '('), ')');
            } elseif (strpos($splitType, '?') === 0) {
                $splitType = substr($splitType, 1);
                $hasNull = $hasNull || (($splitType !== '') && ($splitType !== false));
            }
            /** @psalm-suppress DocblockTypeContradiction */
            if (($splitType === false) || ($splitType === '')) {
                continue;
            }
            if (strtolower($splitType) === 'null') {
                $hasNull = true;
                continue;
            }
            $normalized[] = $splitType;
        }
        $ordered = array_values(array_unique($normalized));
        sort($ordered, SORT_STRING);
        $hasNull and $ordered[] = 'null';

        return $ordered;
    }
}
