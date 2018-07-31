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

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHPCompatibility;

class ReturnTypeDeclarationSniff implements Sniff
{
    const TYPE_CODES = [
        T_STRING,
        T_ARRAY_HINT,
        T_CALLABLE,
        T_SELF,
    ];

    const METHODS_WHITELIST = [
        '__to_string',
        'serialize',
        'jsonSerialize',
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'current',
        'key',
        'valid',
        'count',
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
        if (PhpcsHelpers::functionIsArrayAccess($file, $position)) {
            return;
        }

        list($functionStart, $functionEnd) = PhpcsHelpers::functionBoundaries($file, $position);

        if (($functionStart < 0) || ($functionEnd <= 0)) {
            return;
        }

        list(
            $hasNonVoidReturnType,
            $hasVoidReturnType,
            $hasNoReturnType,
            $hasNullableReturn,
            $returnsGenerator
            ) = $this->returnTypeInfo($file, $position);

        list($nonVoidReturnCount, $voidReturnCount, $nullReturnCount) = PhpcsHelpers::countReturns(
            $file,
            $position
        );

        $yieldCount = $this->countYield($functionStart, $functionEnd, $file);

        if ($yieldCount || $returnsGenerator) {
            $this->maybeGeneratorErrors(
                $yieldCount,
                $returnsGenerator,
                $nonVoidReturnCount,
                $file,
                $position
            );

            return;
        }

        $this->maybeErrors(
            $hasNonVoidReturnType,
            $hasVoidReturnType,
            $hasNoReturnType,
            $hasNullableReturn,
            $nonVoidReturnCount,
            $nullReturnCount,
            $voidReturnCount,
            $file,
            $position
        );
    }

    /**
     * @param bool $hasNonVoidReturnType
     * @param bool $hasVoidReturnType
     * @param bool $hasNullableReturn
     * @param bool $hasNoReturnType
     * @param int $nonVoidReturnCount
     * @param int $voidReturnCount
     * @param int $nullReturnCount
     * @param File $file
     * @param int $position
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    private function maybeErrors(
        bool $hasNonVoidReturnType,
        bool $hasVoidReturnType,
        bool $hasNoReturnType,
        bool $hasNullableReturn,
        int $nonVoidReturnCount,
        int $nullReturnCount,
        int $voidReturnCount,
        File $file,
        int $position
    ) {

        $hasNullableReturn
            ? $nonVoidReturnCount += $nullReturnCount
            : $voidReturnCount += $nullReturnCount;

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

        if (PhpcsHelpers::isHookClosure($file, $position)
            || PhpcsHelpers::isHookFunction($file, $position)
        ) {
            return;
        }

        if (!$this->areNullableReturnTypesSupported()
            && $this->hasReturnNullDocBloc($file, $position)
        ) {
            return;
        }

        if (PhpcsHelpers::functionIsMethod($file, $position)
            && in_array($file->getDeclarationName($position), self::METHODS_WHITELIST, true)
        ) {
            return;
        }

        if ($hasNoReturnType) {
            $file->addWarning('Return type is missing', $position, 'NoReturnType');
        }
    }

    /**
     * @param int $yieldCount
     * @param bool $returnsGenerator
     * @param int $nonVoidReturnCount
     * @param File $file
     * @param int $position
     */
    private function maybeGeneratorErrors(
        int $yieldCount,
        bool $returnsGenerator,
        int $nonVoidReturnCount,
        File $file,
        int $position
    ) {

        if ($nonVoidReturnCount > 1) {
            $file->addWarning(
                'A generator should only contain a single return point.',
                $position,
                'InvalidGeneratorManyReturns'
            );
        }

        if ($yieldCount && $returnsGenerator) {
            return;
        }

        if (!$yieldCount) {
            $file->addError(
                'Found a generator return type in non-yielding function.',
                $position,
                'GeneratorReturnTypeWithoutYield'
            );

            return;
        }

        if (!$nonVoidReturnCount) {
            $file->addWarning(
                'Found a function that yield values but missing Generator return type.',
                $position,
                'NoGeneratorReturnType'
            );

            return;
        }

        $returnType = $this->returnTypeContent($file, $position);
        if ($returnType === 'Traversable' || $returnType === 'Iterator') {
            return;
        }

        $file->addError(
            'Found a function that yield values but declare a return type different than Generator.',
            $position,
            'IncorrectReturnTypeForGenerator'
        );
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return string
     */
    private function returnTypeContent(File $file, int $functionPosition): string
    {
        $info = $file->getMethodProperties($functionPosition);
        if (array_key_exists('return_type', $info) && is_string($info['return_type'])) {
            return ltrim($info['return_type'], '\\');
        }

        $tokens = $file->getTokens();
        $returnTypeToken = $file->findNext(
            [T_RETURN_TYPE],
            $functionPosition + 3, // 3: open parenthesis, close parenthesis, colon
            ($tokens[$functionPosition]['scope_opener'] ?? 0) - 1
        );

        $returnType = $tokens[$returnTypeToken] ?? null;
        if (!$returnType || $returnType['code'] !== T_RETURN_TYPE) {
            return '';
        }

        return ltrim($returnType['content'] ?? '', '\\');
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array
     */
    private function returnTypeInfo(File $file, int $functionPosition): array
    {
        $tokens = $file->getTokens();

        $returnTypeContent = $this->returnTypeContent($file, $functionPosition);

        if (!$returnTypeContent) {
            return [false, false, true, false, false];
        }

        $start = $tokens[$functionPosition]['parenthesis_closer'] + 1;
        $end = $tokens[$functionPosition]['scope_opener'];
        $hasNullable = false;
        for ($i = $start; $i < $end; $i++) {
            if ($tokens[$i]['code'] === T_NULLABLE) {
                $hasNullable = true;
                break;
            }
            if ($tokens[$i]['code'] === T_WHITESPACE) {
                continue;
            }
        }

        $hasNonVoidReturnType = $returnTypeContent !== 'void';
        $hasVoidReturnType = $returnTypeContent === 'void';
        $returnsGenerator = $returnTypeContent === 'Generator';

        return [$hasNonVoidReturnType, $hasVoidReturnType, false, $hasNullable, $returnsGenerator];
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return bool
     */
    private function hasReturnNullDocBloc(File $file, int $functionPosition): bool
    {
        $return = PhpcsHelpers::functionDocBlockTag('@return', $file, $functionPosition);
        if (!$return) {
            return false;
        }

        $returnContentParts = preg_split('~\s+~', reset($return));
        $returnTypes = $returnContentParts ? explode('|', reset($returnContentParts)) : [];
        $returnTypes and $returnTypes = array_map('strtolower', $returnTypes);

        return
            $returnTypes
            && count($returnTypes) < 3
            && !in_array('mixed', $returnTypes, true)
            && in_array('null', $returnTypes, true);
    }

    /**
     * Return true if _min_ supported version is PHP 7.1.
     *
     * @return bool
     */
    private function areNullableReturnTypesSupported(): bool
    {
        $testVersion = trim(PHPCompatibility\PHPCSHelper::getConfigData('testVersion') ?: '');
        if (!$testVersion) {
            return false;
        }

        preg_match('`^(\d+\.\d+)(?:\s*-\s*(?:\d+\.\d+)?)?$`', $testVersion, $matches);
        $min = $matches[1] ?? null;

        return $min && version_compare($min, '7.1', '>=');
    }

    /**
     * @param int $functionStart
     * @param int $functionEnd
     * @return int
     */
    private function countYield(int $functionStart, int $functionEnd, File $file): int
    {
        $count = 0;
        $tokens = $file->getTokens();
        for ($i = $functionStart + 1; $i < $functionEnd; $i++) {
            if ($tokens[$i]['code'] === T_CLOSURE) {
                $i = $tokens[$i]['scope_closer'];
                continue;
            }
            if ($tokens[$i]['code'] === T_YIELD || $tokens[$i]['code'] === T_YIELD_FROM) {
                $count++;
            }
        }

        return $count;
    }
}
