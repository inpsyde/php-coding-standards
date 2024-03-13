<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NoElseSniff implements Sniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_ELSE];
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

        $phpcsFile->addWarning(
            'Do not use "else". Prefer early return statement instead.',
            $stackPtr,
            'ElseFound'
        );
    }
}
