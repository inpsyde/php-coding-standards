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

use Inpsyde\CodingStandard\Helpers\Boundaries;
use Inpsyde\CodingStandard\Helpers\FunctionReturnStatement;
use Inpsyde\CodingStandard\Helpers\WpHooks;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class HookClosureReturnSniff implements Sniff
{
    /**
     * @return list<string>
     */
    public function register(): array
    {
        return [T_CLOSURE];
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

        if (! WpHooks::isHookClosure($phpcsFile, $stackPtr)) {
            return;
        }

        [$functionStart, $functionEnd] = Boundaries::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $returnData = FunctionReturnStatement::allInfo($phpcsFile, $stackPtr);
        $nonVoidReturnCount = $returnData['nonEmpty'];
        $voidReturnCount = $returnData['void'];
        $nullReturnsCount = $returnData['null'];

        // Allow a filter to return null on purpose
        $nonVoidReturnCount += $nullReturnsCount;

        $isFilterClosure = WpHooks::isHookClosure($phpcsFile, $stackPtr, true, false);

        if ($isFilterClosure && (! $nonVoidReturnCount || $voidReturnCount)) {
            $phpcsFile->addError(
                'No (or void) return from filter closure.',
                $stackPtr,
                'NoReturnFromFilter'
            );
        }

        if (! $isFilterClosure && $nonVoidReturnCount) {
            $phpcsFile->addError(
                'Return value from action closure.',
                $stackPtr,
                'ReturnFromAction'
            );
        }
    }
}
