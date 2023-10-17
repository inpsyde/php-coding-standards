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

class HookPrioritySniff implements Sniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_STRING];
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
        $functionName = $tokens[$stackPtr]['content'] ?? '';

        if ($functionName !== 'add_filter' && $functionName !== 'add_action') {
            return;
        }

        $parameters = $this->stringParameters($phpcsFile, $stackPtr + 1);

        if (in_array('PHP_INT_MAX', $parameters, true) && $functionName === 'add_filter') {
            $phpcsFile->addWarning(
                'PHP_INT_MAX applied as filter priority.',
                $stackPtr,
                'HookPriorityLimit'
            );
            return;
        }

        if (in_array('PHP_INT_MIN', $parameters, true)) {
            $phpcsFile->addWarning(
                'PHP_INT_MIN applied as filter or action priority.',
                $stackPtr,
                'HookPriorityLimit'
            );
        }
    }

    private function stringParameters(File $phpcsFile, int $position): array
    {
        $tokens = $phpcsFile->getTokens();

        $openingParenthesis = $tokens[$position]['parenthesis_opener'] ?? 0;
        $closingParenthesis = $tokens[$position]['parenthesis_closer'] ?? 0;
        $stringParameters = [];

        for ($i = (int)$openingParenthesis + 1; $i < $closingParenthesis; $i++) {
            $tokenCode = $tokens[$i]['code'] ?? 0;
            if ($tokenCode !== T_STRING) {
                continue;
            }

            $stringParameters[] = $tokens[$i]['content'] ?? '';
        }

        return $stringParameters;
    }
}
