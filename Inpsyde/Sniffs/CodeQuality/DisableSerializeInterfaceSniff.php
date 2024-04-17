<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\ObjectDeclarations;

class DisableSerializeInterfaceSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            \T_CLASS,
            \T_ANON_CLASS,
            \T_ENUM,
            \T_INTERFACE,
        ];
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
        $tokenCode = $phpcsFile->getTokens()[$stackPtr]['code'] ?? null;
        $find = ($tokenCode === \T_INTERFACE)
            ? ObjectDeclarations::findExtendedInterfaceNames($phpcsFile, $stackPtr)
            : ObjectDeclarations::findImplementedInterfaceNames($phpcsFile, $stackPtr);

        if (($find === false) || !in_array('Serializable', $find, true)) {
            return;
        }

        $phpcsFile->addError(
            'The Serializable interface is deprecated, '
            . 'please use __serialize and __unserialize instead.',
            $stackPtr,
            'Found'
        );
    }
}
