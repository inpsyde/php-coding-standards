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

use Inpsyde\Helpers\FunctionDocBlock;
use Inpsyde\Helpers\Functions;
use Inpsyde\Helpers\WpHooks;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

class ArgumentTypeDeclarationSniff implements Sniff
{
    public const METHODS_WHITELIST = [
        'unserialize',
        'seek',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION, T_CLOSURE, T_FN];
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

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        if ($this->shouldIgnore($phpcsFile, $stackPtr, $tokens)) {
            return;
        }

        /** @var array<array{name: string, type_hint?: string|false}> $parameters */
        $parameters = FunctionDeclarations::getParameters($phpcsFile, $stackPtr);
        $docBlockTypes = FunctionDocBlock::allParamTypes($phpcsFile, $stackPtr);

        $errors = [];
        foreach ($parameters as $parameter) {
            if ($parameter['type_hint'] ?? null) {
                continue;
            }

            $docTypes = $docBlockTypes[$parameter['name']] ?? [];
            if (!Functions::isNonDeclarableDocBlockType($docTypes, false)) {
                $errors[] = $parameter['name'];
            }
        }

        if (!$errors) {
            return;
        }

        $allErrors = implode('", "', $errors);
        $phpcsFile->addWarning(
            sprintf('Argument type is missing for parameter(s) "%s"', $allErrors),
            $stackPtr,
            'NoArgumentType'
        );
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    private function shouldIgnore(File $phpcsFile, $stackPtr, array $tokens): bool
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        $tokenCode = $tokens[$stackPtr]['code'] ?? '';
        $name = ($tokenCode !== T_FN) ? FunctionDeclarations::getName($phpcsFile, $stackPtr) : '';

        return Functions::isArrayAccess($phpcsFile, $stackPtr)
            || WpHooks::isHookClosure($phpcsFile, $stackPtr)
            || WpHooks::isHookFunction($phpcsFile, $stackPtr)
            || Functions::isPsrMethod($phpcsFile, $stackPtr)
            || FunctionDeclarations::isSpecialMethod($phpcsFile, $stackPtr)
            || (
                Scopes::isOOMethod($phpcsFile, $stackPtr)
                && in_array($name, self::METHODS_WHITELIST, true)
            );
    }
}
