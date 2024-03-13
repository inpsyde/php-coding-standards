<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\CodingStandard\Helpers\Names;
use Inpsyde\CodingStandard\Helpers\Objects;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Tokens\Collections;

class PropertyPerClassLimitSniff implements Sniff
{
    public int $maxCount = 10;

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return array_keys(Collections::ooPropertyScopes());
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
        $count = Objects::countProperties($phpcsFile, $stackPtr);
        if ($count <= $this->maxCount) {
            return;
        }

        $message = sprintf(
            '"%s" has too many properties: %d. Can be up to %d properties.',
            Names::tokenTypeName($phpcsFile, $stackPtr),
            $count,
            $this->maxCount
        );

        $phpcsFile->addWarning($message, $stackPtr, 'TooManyProperties');
    }
}
