<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "phpcs-neutron-standard" repository
 * found at https://github.com/Automattic/phpcs-neutron-standard
 * Copyright (c) Automattic Inc.
 * released under MIT license.
 */

namespace Inpsyde\InpsydeCodingStandard\Sniffs\CodeQuality;

use Inpsyde\InpsydeCodingStandard\Helpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ReturnTypeDeclarationSniff implements Sniff
{
    const TYPE_CODES = [
        T_STRING,
        T_ARRAY_HINT,
        T_CALLABLE,
        T_SELF,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_FUNCTION, T_CLOSURE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $file, $position)
    {
        list($functionStart, $functionEnd) = Helpers::functionBoundaries($file, $position);
        if (!$functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        list($hasNonVoidReturnType, $hasVoidReturnType, $hasNoReturnType) = $this->returnTypeInfo(
            $file,
            $position,
            $functionEnd
        );

        list($nonVoidReturnCount, $voidReturnCount) = Helpers::countReturns($file, $position);

        $this->maybeErrors(
            $hasNonVoidReturnType,
            $hasVoidReturnType,
            $hasNoReturnType,
            $nonVoidReturnCount,
            $voidReturnCount,
            $file,
            $position
        );
    }

    /**
     * @param bool $hasNonVoidReturnType
     * @param bool $hasVoidReturnType
     * @param bool $hasNoReturnType
     * @param int $nonVoidReturnCount
     * @param int $voidReturnCount
     * @param File $file
     * @param int $position
     */
    private function maybeErrors(
        bool $hasNonVoidReturnType,
        bool $hasVoidReturnType,
        bool $hasNoReturnType,
        int $nonVoidReturnCount,
        int $voidReturnCount,
        File $file,
        int $position
    ) {
        if ($hasNonVoidReturnType && ($nonVoidReturnCount === 0 || $voidReturnCount > 0)) {
            $msg = 'Return type with';
            $file->addError(
                $nonVoidReturnCount === 0 ? "{$msg} no return" : "{$msg} void return",
                $position,
                $nonVoidReturnCount === 0 ? 'MissingReturn' : 'IncorrectVoidReturn'
            );
        }

        if ($nonVoidReturnCount <= 0) {
            return;
        }

        if ($hasVoidReturnType) {
            $file->addError(
                'Void return type when returning non-void',
                $position,
                'IncorrectVoidReturnType'
            );
        }

        if (Helpers::isHookClosure($file, $position) || Helpers::isHookFunction($file, $position)) {
            return;
        }

        if ($hasNoReturnType) {
            $file->addWarning('Return type is missing', $position, 'NoReturnType');
        }
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @param int $functionEnd
     * @return array
     */
    private function returnTypeInfo(File $file, int $functionPosition, int $functionEnd): array
    {
        $returnTypeToken = $file->findNext(
            [T_RETURN_TYPE],
            $functionPosition + 3, // 3: open parenthesis, close parenthesis, colon
            $functionEnd - 1
        );

        $returnType = $file->getTokens()[$returnTypeToken] ?? null;
        if ($returnType && $returnType['type'] !== "T_RETURN_TYPE") {
            $returnType = null;
        }

        $hasNonVoidReturnType = $returnType && $returnType['content'] !== 'void';
        $hasVoidReturnType = $returnType && $returnType['content'] === 'void';
        $hasNoReturnType = !$returnType;

        return [$hasNonVoidReturnType, $hasVoidReturnType, $hasNoReturnType];
    }
}
