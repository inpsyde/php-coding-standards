<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
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

    const METHODS_WHITELIST = [
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
     * @return array<int|string>
     *
     * phpcs:disable Inpsyde.CodeQuality
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality

        return [T_FUNCTION, T_CLOSURE];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality
     */
    public function process(File $file, $position)
    {
        //  phpcs:enable Inpsyde.CodeQuality

        if (
            PhpcsHelpers::functionIsArrayAccess($file, $position)
            || PhpcsHelpers::isUntypedPsrMethod($file, $position)
        ) {
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

        $returnData = PhpcsHelpers::returnsCountInfo($file, $position);
        $nonVoidReturnCount = $returnData['nonEmpty'];
        $voidReturnCount = $returnData['void'];
        $nullReturnCount = $returnData['null'];

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
     * @param bool $hasNoReturnType
     * @param bool $hasNullableReturn
     * @param int $nonVoidReturnCount
     * @param int $nullReturnCount
     * @param int $voidReturnCount
     * @param File $file
     * @param int $position
     * @return void
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

        $docBlock = $this->hasReturnNullOrMixedDocBloc($file, $position);

        if (
            $docBlock['mixed']
            || PhpcsHelpers::isHookClosure($file, $position)
            || PhpcsHelpers::isHookFunction($file, $position)
        ) {
            return;
        }

        if (!$this->areNullableReturnTypesSupported() && $docBlock['null']) {
            return;
        }

        $name = (string)$file->getDeclarationName($position);
        if (
            PhpcsHelpers::functionIsMethod($file, $position)
            && (in_array($name, self::METHODS_WHITELIST, true) || strpos($name, '__') === 0)
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
     * @return void
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

        $returnType = $this->returnTypeContent($file, $position);
        if (in_array($returnType, ['Traversable', 'Iterator', 'iterable'], true)) {
            return;
        }

        if (!$nonVoidReturnCount) {
            $file->addWarning(
                'Found a function that yield values but missing compatible return type.',
                $position,
                'NoGeneratorReturnType'
            );

            return;
        }

        $file->addError(
            'Found a function that yield values using a return type incompatible with Generator.',
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

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $returnTypeToken = $file->findNext(
            [T_RETURN_TYPE],
            $functionPosition + 3, // 3: open parenthesis, close parenthesis, colon
            (int)($tokens[$functionPosition]['scope_opener'] ?? 0) - 1
        );

        $returnType = $tokens[$returnTypeToken] ?? null;
        if (!$returnType || $returnType['code'] !== T_RETURN_TYPE) {
            return '';
        }

        return ltrim((string)($returnType['content'] ?? ''), '\\');
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array{bool, bool, bool, bool, bool}
     */
    private function returnTypeInfo(File $file, int $functionPosition): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $returnTypeContent = $this->returnTypeContent($file, $functionPosition);

        if (!$returnTypeContent) {
            return [false, false, true, false, false];
        }

        $start = (int)((int)($tokens[$functionPosition]['parenthesis_closer']) + 1);
        $end = (int)($tokens[$functionPosition]['scope_opener']);
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
     * @return array{'mixed': bool, 'null': bool}
     */
    private function hasReturnNullOrMixedDocBloc(File $file, int $functionPosition): array
    {
        $return = PhpcsHelpers::functionDocBlockTag('@return', $file, $functionPosition);
        if (!$return) {
            return ['mixed' => false, 'null' => false];
        }

        $returnContentParts = preg_split('~\s+~', reset($return), PREG_SPLIT_NO_EMPTY);
        if (!$returnContentParts) {
            return ['mixed' => false, 'null' => false];
        }

        $returnTypes = array_map('strtolower', explode('|', reset($returnContentParts)));
        $returnTypes = array_map('trim', $returnTypes);
        $returnTypesCount = count($returnTypes);
        // Only if 1 or 2 types
        if (!$returnTypesCount || ($returnTypesCount > 2)) {
            return ['mixed' => false, 'null' => false];
        }

        return [
            'mixed' => in_array('mixed', $returnTypes, true),
            'null' => in_array('null', $returnTypes, true),
        ];
    }

    /**
     * @return bool
     */
    private function areNullableReturnTypesSupported(): bool
    {
        $min = PhpcsHelpers::minPhpTestVersion();

        return $min && version_compare($min, '7.1', '>=');
    }

    /**
     * @param int $functionStart
     * @param int $functionEnd
     * @param File $file
     * @return int
     */
    private function countYield(int $functionStart, int $functionEnd, File $file): int
    {
        $count = 0;
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        for ($i = ($functionStart + 1); $i < $functionEnd; $i++) {
            if ($tokens[$i]['code'] === T_CLOSURE) {
                /** @psalm-suppress LoopInvalidation */
                $i = (int)($tokens[$i]['scope_closer']);
                continue;
            }
            if ($tokens[$i]['code'] === T_YIELD || $tokens[$i]['code'] === T_YIELD_FROM) {
                $count++;
            }
        }

        return $count;
    }
}
