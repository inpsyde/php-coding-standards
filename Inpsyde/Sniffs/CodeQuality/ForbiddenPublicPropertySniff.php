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
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Scopes;

class ForbiddenPublicPropertySniff implements Sniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_VARIABLE];
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

        if (!Scopes::isOOProperty($phpcsFile, $stackPtr)) {
            return;
        }

        // Skip sniff classes, they have public properties for configuration (unfortunately)
        if ($this->isSniffClass($phpcsFile, $stackPtr)) {
            return;
        }

        $scopeModifierToken = $this->propertyScopeModifier($phpcsFile, $stackPtr);
        if ($scopeModifierToken['code'] === T_PUBLIC) {
            $phpcsFile->addError(
                'Do not use public properties. Use method access instead.',
                $stackPtr,
                'Found'
            );
        }
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function isSniffClass(File $file, int $position): bool
    {
        $classNameTokenPosition = $file->findNext(
            T_STRING,
            (int)$file->findPrevious(T_CLASS, $position)
        );

        if ($classNameTokenPosition === false) {
            return false;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $classNameToken = $tokens[$classNameTokenPosition];

        if (substr((string)$classNameToken['content'], -5, 5) === 'Sniff') {
            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $position
     * @return array<string, mixed>
     */
    private function propertyScopeModifier(File $file, int $position): array
    {
        $scopeModifierPosition = $file->findPrevious(Tokens::$scopeModifiers, ($position - 1));
        if ($scopeModifierPosition === false) {
            return ['code' => T_PUBLIC];
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        return $tokens[$scopeModifierPosition];
    }
}
