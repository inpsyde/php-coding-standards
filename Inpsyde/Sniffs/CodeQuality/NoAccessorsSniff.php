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

class NoAccessorsSniff implements Sniff
{
    public const ALLOWED_NAMES = [
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'setUp',
        'setUpBeforeClass',
    ];

    public bool $skipForPrivate = true;
    public bool $skipForProtected = false;

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
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        if (!Scopes::isOOMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $functionName = $phpcsFile->getDeclarationName($stackPtr) ?? '';
        if (($functionName === '') || in_array($functionName, self::ALLOWED_NAMES, true)) {
            return;
        }

        if ($this->skipForPrivate || $this->skipForProtected) {
            $modifierPointerPosition = $phpcsFile->findPrevious(
                [T_WHITESPACE, T_ABSTRACT],
                $stackPtr - 1,
                null,
                true,
                null,
                true
            );

            /** @var array<int, array<string, mixed>> $tokens */
            $tokens = $phpcsFile->getTokens();
            $modifierPointer = $tokens[$modifierPointerPosition] ?? null;
            if (
                is_array($modifierPointer)
                && !in_array($modifierPointer['code'], Tokens::$scopeModifiers, true)
            ) {
                $modifierPointer = null;
            }

            $modifier = ($modifierPointer !== null) ? ($modifierPointer['code'] ?? null) : null;
            if (
                (($modifier === T_PRIVATE) && $this->skipForPrivate)
                || (($modifier === T_PROTECTED) && $this->skipForProtected)
            ) {
                return;
            }
        }

        preg_match('/^(set|get)[_A-Z0-9]+/', $functionName, $matches);
        if (!$matches) {
            return;
        }

        if ($matches[1] === 'set') {
            $phpcsFile->addWarning(
                'Setters are discouraged. Try to use immutable objects, constructor injection '
                . 'and for objects that really needs changing state try behavior naming instead, '
                . 'e.g. changeName() instead of setName().',
                $stackPtr,
                'NoSetter'
            );

            return;
        }

        $phpcsFile->addWarning(
            'Getters are discouraged. "Tell Don\'t Ask" principle should be applied if possible, '
            . 'and if getters are really needed consider naming methods after properties, '
            . 'e.g. id() instead of getId().',
            $stackPtr,
            'NoGetter'
        );
    }
}
