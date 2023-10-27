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
use PHPCSUtils\Utils\PassedParameters;

class EncodingCommentSniff implements Sniff
{
    private const DISALLOWED_COMMENT = '-*- coding: utf-8 -*-';

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_COMMENT];
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

        $tokens = $phpcsFile->getTokens();

        $comment = isset($tokens[$stackPtr]['content']) && is_string($tokens[$stackPtr]['content'])
            ? $tokens[$stackPtr]['content']
            : '';

        if (strpos($comment, self::DISALLOWED_COMMENT) === false) {
            return;
        }

        $fix = $phpcsFile->addFixableWarning(
            'Found outdated encoding declaration in comment.',
            $stackPtr,
            'EncodingComment'
        );

        if ($fix) {
            $this->fix($phpcsFile, $stackPtr);
        }
    }

    private function fix(File $phpcsFile, int $position): void
    {
        $phpcsFile->fixer->beginChangeset();

        $phpcsFile->fixer->replaceToken($position, '');

        $phpcsFile->fixer->endChangeset();
    }
}
