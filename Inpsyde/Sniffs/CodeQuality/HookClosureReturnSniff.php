<?php

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

        if (!WpHooks::isHookClosure($phpcsFile, $stackPtr)) {
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
