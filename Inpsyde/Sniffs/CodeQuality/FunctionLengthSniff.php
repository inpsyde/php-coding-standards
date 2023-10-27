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

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class FunctionLengthSniff implements Sniff
{
    public int $maxLength = 50;
    public bool $ignoreDocBlocks = true;
    public bool $ignoreComments = true;
    public bool $ignoreBlankLines = true;

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $length = $this->structureLinesCount($phpcsFile, $stackPtr);
        if ($length <= $this->maxLength) {
            return;
        }

        $ignored = [];
        $suffix = '';
        $this->normalizeIgnoreFlags();
        $this->ignoreBlankLines and $ignored[] = 'blank lines';
        $this->ignoreComments and $ignored[] = 'single line comments';
        $this->ignoreDocBlocks and $ignored[] = 'doc blocks';
        if ($ignored) {
            $suffix = ' (ignoring ';
            $last = array_pop($ignored);
            $others = implode(', ', $ignored);
            $suffix .= $others ? "{$others} and {$last})" : "{$last})";
        }

        $error = sprintf(
            'Your function is too long. Currently using %d lines%s, max is %d.',
            $length,
            $suffix,
            $this->maxLength
        );

        $phpcsFile->addError($error, $stackPtr, 'TooLong');
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    private function structureLinesCount(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $token = $tokens[$position] ?? [];

        if (
            ! array_key_exists('scope_opener', $token)
            || ! array_key_exists('scope_closer', $token)
        ) {
            return 0;
        }

        $start = (int) $token['scope_opener'];
        $end = (int) $token['scope_closer'];
        $length = (int) $tokens[$end]['line'] - (int) $tokens[$start]['line'];

        if ($length < $this->maxLength) {
            return $length;
        }

        return $length - $this->collectLinesToExclude($start, $end, $tokens);
    }

    /**
     * @param int $start
     * @param int $end
     * @param array<int, array<string, mixed>> $tokens
     * @return int
     */
    private function collectLinesToExclude(int $start, int $end, array $tokens): int
    {
        $docblocks = [];
        $linesData = [];

        $skipLines = [$tokens[$start + 1]['line'], $tokens[$end]['line']];
        for ($i = $start + 1; $i < $end - 1; $i++) {
            if (in_array($tokens[$i]['line'], $skipLines, true)) {
                continue;
            }

            $docblocks = $this->docBlocksData($tokens, $i, $docblocks);
            $linesData = $this->ignoredLinesData($tokens[$i], $linesData);
        }

        $empty = array_filter(array_column($linesData, 'empty'));
        $onlyComment = array_filter(array_column($linesData, 'only-comment'));

        $toExcludeCount = (int) array_sum($docblocks);
        if ($this->ignoreBlankLines) {
            $toExcludeCount += count($empty);
        }
        if ($this->ignoreComments) {
            $toExcludeCount += count($onlyComment) - count($empty);
        }

        return $toExcludeCount;
    }

    /**
     * @param array $token
     * @param array<int, array{empty:bool, only-comment:bool}> $lines
     * @return array<int, array{empty:bool, only-comment:bool}>
     */
    private function ignoredLinesData(array $token, array $lines): array
    {
        $line = (int) $token['line'];
        if (! array_key_exists($line, $lines)) {
            $lines[$line] = ['empty' => true, 'only-comment' => true];
        }

        if (! in_array($token['code'], [T_COMMENT, T_WHITESPACE], true)) {
            $lines[$line]['only-comment'] = false;
        }

        if ($token['code'] !== T_WHITESPACE) {
            $lines[$line]['empty'] = false;
        }

        return $lines;
    }

    /**
     * @param array<int, array<string, mixed>> $tokens
     * @param int $position
     * @param array $docBlocks
     * @return array
     */
    private function docBlocksData(array $tokens, int $position, array $docBlocks): array
    {
        if (
            ! $this->ignoreDocBlocks
            || $tokens[$position]['code'] !== T_DOC_COMMENT_OPEN_TAG
        ) {
            return $docBlocks;
        }

        $closer = $tokens[$position]['comment_closer'] ?? null;
        $docBlocks[] = is_numeric($closer)
            ? 1 + ((int) $tokens[(int) $closer]['line'] - (int) $tokens[$position]['line'])
            : 1;

        return $docBlocks;
    }

    /**
     * @return void
     */
    private function normalizeIgnoreFlags(): void
    {
        $flags = [
            'ignoreBlankLines',
            'ignoreComments',
            'ignoreDocBlocks',
        ];

        foreach ($flags as $flag) {
            if (is_string($this->{$flag})) {
                $this->{$flag} = (bool) filter_var($this->{$flag}, FILTER_VALIDATE_BOOLEAN);
            }
        }
    }
}
