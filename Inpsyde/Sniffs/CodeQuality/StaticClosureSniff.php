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
use Inpsyde\CodingStandard\Helpers\FunctionDocBlock;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class StaticClosureSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_CLOSURE, T_FN];
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

        [$functionStart, $functionEnd] = Boundaries::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $isStatic = $phpcsFile->findPrevious(T_STATIC, $stackPtr, $stackPtr - 3, false, null, true);
        if ($isStatic !== false) {
            return;
        }

        $thisFound = false;
        $i = $functionStart + 1;

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        while (!$thisFound && ($i < $functionEnd)) {
            $token = $tokens[$i];
            $content = (string) ($token['content'] ?? '');
            $thisFound = (($token['code'] === T_VARIABLE) && ($content === '$this'))
                || (
                    in_array($token['code'], [T_DOUBLE_QUOTED_STRING, T_HEREDOC], true)
                    && (strpos($content, '$this->') !== false)
                );
            $i++;
        }

        if ($thisFound) {
            return;
        }

        $boundDoc = FunctionDocBlock::tag('@bound', $phpcsFile, $stackPtr);
        if ($boundDoc) {
            return;
        }

        $varDoc = FunctionDocBlock::tag('@var', $phpcsFile, $stackPtr);
        foreach ($varDoc as $content) {
            if (preg_match('~(?:^|\s+)\$this(?:$|\s+)~', $content)) {
                return;
            }
        }

        $line = (int) $tokens[$stackPtr]['line'];
        $message = sprintf('Closure found at line %d could be static.', $line);

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'PossiblyStaticClosure')) {
            $this->fix($stackPtr, $phpcsFile);
        }
    }

    /**
     * @param int $position
     * @param File $file
     * @return void
     */
    private function fix(int $position, File $file): void
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($position, 'static function');

        $fixer->endChangeset();
    }
}
