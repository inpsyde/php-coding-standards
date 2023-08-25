<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
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

        if (!PhpcsHelpers::isHookClosure($phpcsFile, $stackPtr)) {
            return;
        }

        [$functionStart, $functionEnd] = PhpcsHelpers::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $returnData = PhpcsHelpers::returnsCountInfo($phpcsFile, $stackPtr);
        $nonVoidReturnCount = $returnData['nonEmpty'];
        $voidReturnCount = $returnData['void'];
        $nullReturnsCount = $returnData['null'];

        // Allow a filter to return null on purpose
        $nonVoidReturnCount += $nullReturnsCount;

        $isFilterClosure = PhpcsHelpers::isHookClosure($phpcsFile, $stackPtr, true, false);

        if ($isFilterClosure && (!$nonVoidReturnCount || $voidReturnCount)) {
            $phpcsFile->addError(
                'No (or void) return from filter closure.',
                $stackPtr,
                'NoReturnFromFilter'
            );
        }

        if (!$isFilterClosure && $nonVoidReturnCount) {
            $phpcsFile->addError(
                'Return value from action closure.',
                $stackPtr,
                'ReturnFromAction'
            );
        }
    }
}
