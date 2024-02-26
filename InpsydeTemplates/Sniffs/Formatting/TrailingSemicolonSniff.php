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

namespace InpsydeTemplates\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @psalm-type Token = array{
 *     type: string,
 *     code: string|int,
 *     line: int
 *     }
 */
final class TrailingSemicolonSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_SEMICOLON,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        /** @var array<int, Token> $tokens */
        $tokens = $phpcsFile->getTokens();
        $currentLine = $tokens[$stackPtr]['line'];

        $nextNonEmptyPosition = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($stackPtr + 1),
            null,
            true
        );

        if (!is_int($nextNonEmptyPosition) || !isset($tokens[$nextNonEmptyPosition])) {
            return;
        }

        $nextNonEmptyToken = $tokens[$nextNonEmptyPosition];

        if ($nextNonEmptyToken['line'] !== $currentLine) {
            return;
        }

        if ($nextNonEmptyToken['code'] !== T_CLOSE_TAG) {
            return;
        }

        $message = sprintf('Trailing semicolon found in line %d.', $currentLine);

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'Found')) {
            $this->fix($stackPtr, $phpcsFile);
        }
    }

    /**
     * @param int $position
     * @param File $file
     */
    private function fix(int $position, File $file): void
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($position, '');

        $fixer->endChangeset();
    }
}
