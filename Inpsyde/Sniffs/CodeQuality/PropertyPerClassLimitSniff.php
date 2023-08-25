<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class PropertyPerClassLimitSniff implements Sniff
{
    public int $maxCount = 10;

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return array_values(Tokens::$ooScopeTokens);
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
        $count = count(PhpcsHelpers::allPropertiesTokenPositions($phpcsFile, $stackPtr));
        if ($count <= $this->maxCount) {
            return;
        }

        $message = sprintf(
            '"%s" has too many properties: %d. Can be up to %d properties.',
            PhpcsHelpers::tokenTypeName($phpcsFile, $stackPtr),
            $count,
            $this->maxCount
        );

        $phpcsFile->addWarning($message, $stackPtr, 'TooManyProperties');
    }
}
