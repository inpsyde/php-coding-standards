<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\CodingStandard\Helpers\Functions;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NoTopLevelDefineSniff implements Sniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_STRING];
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

        if (
            ($tokens[$stackPtr]['content'] ?? '') !== 'define'
            || ($tokens[$stackPtr]['level'] ?? -1) !== 0
            || !Functions::looksLikeFunctionCall($phpcsFile, $stackPtr)
        ) {
            return;
        }

        $phpcsFile->addWarning(
            'Do not use "define" for top-level constant definition. Prefer "const" instead.',
            $stackPtr,
            'Found'
        );
    }
}
