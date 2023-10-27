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

use Inpsyde\CodingStandard\Helpers\FunctionDocBlock;
use Inpsyde\CodingStandard\Helpers\FunctionReturnStatement;
use Inpsyde\CodingStandard\Helpers\Functions;
use Inpsyde\CodingStandard\Helpers\Misc;
use Inpsyde\CodingStandard\Helpers\WpHooks;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

class ReturnTypeDeclarationSniff implements Sniff
{
    public const TYPE_CODES = [
        T_STRING,
        T_ARRAY_HINT,
        T_CALLABLE,
        T_SELF,
    ];

    public const METHODS_WHITELIST = [
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'current',
        'key',
        'valid',
        'count',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION, T_CLOSURE];
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
        //  phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $data = FunctionDeclarations::getProperties($phpcsFile, $stackPtr);
        if (! $data['has_body']) {
            return;
        }

        $returnType = $data['return_type'] ?? null;
        $returnTypes = $returnType ? $this->normalizeReturnTypes($phpcsFile, $data) : [];
        $returnInfo = FunctionReturnStatement::allInfo($phpcsFile, $stackPtr);

        if ($returnTypes) {
            $this->checkNonEmptyReturnTypes($phpcsFile, $stackPtr, $returnTypes, $returnInfo);

            return;
        }

        if ($this->checkMissingGeneratorReturnType($phpcsFile, $stackPtr)) {
            return;
        }

        $docTags = FunctionDocBlock::tag('return', $phpcsFile, $stackPtr);
        $docTypes = (count($docTags) === 1)
            ? FunctionDocBlock::normalizeTypesString(reset($docTags))
            : [];

        if (
            ! Functions::isNonDeclarableDocBlockType($docTypes, true)
            && ! $this->shouldIgnore($phpcsFile, $stackPtr, $tokens)
        ) {
            $phpcsFile->addWarning('Return type is missing', $stackPtr, 'NoReturnType');

            return;
        }

        $this->checkNonEmptyReturnTypes($phpcsFile, $stackPtr, $docTypes, $returnInfo);
    }

    /**
     * @param File $file
     * @param int $position
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     */
    private function shouldIgnore(File $file, int $position, array $tokens): bool
    {
        $tokenCode = $tokens[$position]['code'] ?? '';
        $name = ($tokenCode !== T_FN) ? FunctionDeclarations::getName($file, $position) : '';

        return Functions::isArrayAccess($file, $position)
            || Functions::isPsrMethod($file, $position)
            || FunctionDeclarations::isSpecialMethod($file, $position)
            || WpHooks::isHookClosure($file, $position)
            || WpHooks::isHookFunction($file, $position)
            || (
                Scopes::isOOMethod($file, $position)
                && in_array($name, self::METHODS_WHITELIST, true)
            );
    }

    /**
     * @param File $file
     * @param $returnType
     * @param array $data
     * @return list<string>
     */
    private function normalizeReturnTypes(File $file, array $data): array
    {
        /** @var int $start */
        $start = is_int($data['return_type_token'] ?? null) ? $data['return_type_token'] : -1;
        /** @var int $end */
        $end = is_int($data['return_type_end_token'] ?? null) ? $data['return_type_end_token'] : -1;

        if (($start > 0) && ($end > 0)) {
            $returnTypesStr = Misc::tokensSubsetToString($start, $end, $file, []);
            if ($data['nullable_return_type'] ?? false) {
                $returnTypesStr .= '|null';
            }

            return FunctionDocBlock::normalizeTypesString($returnTypesStr);
        }

        return [];
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array $returnInfo
     * @return void
     */
    private function checkNonEmptyReturnTypes(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo
    ): void {

        if (($returnTypes === ['void']) || ($returnTypes === ['null'])) {
            $this->checkIsActualVoid($file, $position, $returnInfo, $returnTypes === ['null']);

            return;
        }

        $this->checkInvalidGenerator($file, $position, $returnTypes, $returnInfo)
            || $this->checkMissingReturn($file, $position, $returnTypes, $returnInfo)
            || $this->checkIncorrectVoid($file, $position, $returnTypes, $returnInfo);
    }

    /**
     * @param File $file
     * @param int $position
     * @param array $returnInfo
     * @param bool $checkNull
     * @return void
     */
    private function checkIsActualVoid(
        File $file,
        int $position,
        array $returnInfo,
        bool $checkNull
    ): void {

        $key = $checkNull ? 'null' : 'void';

        if (($returnInfo['total'] >= 0) && ($returnInfo['total'] === $returnInfo[$key])) {
            return;
        }

        $file->addError(
            sprintf(
                'Return type is declared "%s" but incompatible return statement(s) found',
                $checkNull ? 'null' : 'void'
            ),
            $position,
            $checkNull ? 'IncorrectNullReturnType' : 'IncorrectVoidReturnType'
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array $returnInfo
     * @return bool
     */
    private function checkIncorrectVoid(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo
    ): bool {

        $hasReturnNull = $returnInfo['null'] > 0;

        if (
            ($hasReturnNull && ! in_array('null', $returnTypes, true))
            || (! in_array('void', $returnTypes, true) && ($returnInfo['void'] > 0))
        ) {
            $file->addError(
                sprintf(
                    'Return type %s but %s found',
                    $hasReturnNull ? 'is not nullable' : 'contains no void',
                    $hasReturnNull ? 'return null' : 'void return',
                ),
                $position,
                $hasReturnNull ? 'IncorrectNullReturn' : 'IncorrectVoidReturn'
            );

            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array $returnInfo
     * @return bool
     */
    private function checkMissingReturn(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo
    ): bool {

        $nonEmptyTypes = array_diff($returnTypes, ['void', 'null', 'never']);
        if ($nonEmptyTypes !== $returnTypes) {
            return false;
        }

        $hasNull = $returnInfo['null'] > 0;
        $hasVoid = $returnInfo['void'] > 0;

        if ($hasNull || $hasVoid) {
            $file->addError(
                sprintf(
                    'Non-empty return type declared, but %s return found',
                    $hasNull ? ($hasVoid ? 'empty' : 'null') : 'empty'
                ),
                $position,
                $hasNull ? 'IncorrectNullReturn' : 'IncorrectVoidReturn'
            );

            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array $returnInfo
     * @return bool
     */
    private function checkInvalidGenerator(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo
    ): bool {

        $hasGenerator = false;
        while (! $hasGenerator && $returnTypes) {
            $returnType = explode('&', rtrim(ltrim(array_shift($returnTypes), '('), ')'));
            $hasGenerator = in_array('Generator', $returnType, true)
                || in_array('\Generator', $returnType, true)
                || in_array('Traversable', $returnType, true)
                || in_array('\Traversable', $returnType, true)
                || in_array('Iterator', $returnType, true)
                || in_array('\Iterator', $returnType, true)
                || in_array('iterable', $returnType, true);
        }

        $yieldCount = Functions::countYieldInBody($file, $position);

        $return = false;

        if ($hasGenerator || ($yieldCount > 0)) {
            if ($returnInfo['total'] > 1) {
                $file->addError(
                    'A function returning a Generator should only contain a single return point.',
                    $position,
                    'InvalidGeneratorManyReturns'
                );
            }
            $return = true;
        }

        if ($hasGenerator && ($yieldCount === 0)) {
            $file->addError(
                'Return type contains "Generator" but no yield found in the function body',
                $position,
                'GeneratorReturnTypeWithoutYield'
            );

            return true;
        }

        if (! $hasGenerator && ($yieldCount > 0)) {
            $file->addError(
                'Return type does not contain "Generator" but yield found in the function body',
                $position,
                'NoGeneratorReturnType'
            );

            return true;
        }

        return $return;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function checkMissingGeneratorReturnType(File $file, int $position): bool
    {
        $yield = Functions::countYieldInBody($file, $position);
        if ($yield > 0) {
            $file->addError(
                'Return type does not contain "Generator" but yield found in the function body',
                $position,
                'NoGeneratorReturnType'
            );

            return true;
        }

        return false;
    }
}
