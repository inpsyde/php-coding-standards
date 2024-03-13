<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

class HookPrioritySniff implements Sniff
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

        $tokens = $phpcsFile->getTokens();
        $functionName = $tokens[$stackPtr]['content'] ?? '';

        if ($functionName !== 'add_filter' && $functionName !== 'add_action') {
            return;
        }

        $parameter = PassedParameters::getParameter($phpcsFile, $stackPtr, 3, 'priority');
        $parameter = $parameter['clean'] ?? '';

        if ($parameter === 'PHP_INT_MAX' && $functionName === 'add_filter') {
            $phpcsFile->addWarning(
                'Found PHP_INT_MAX used as hook priority. '
                . 'This makes it hard, if not impossible to reliably filter the callback output.',
                $stackPtr,
                'HookPriority'
            );
            return;
        }

        if ($parameter === 'PHP_INT_MIN') {
            $phpcsFile->addWarning(
                'Found PHP_INT_MIN used as hook priority. '
                . 'This makes it hard, if not impossible to reliably remove the callback.',
                $stackPtr,
                'HookPriority'
            );
        }
    }
}
