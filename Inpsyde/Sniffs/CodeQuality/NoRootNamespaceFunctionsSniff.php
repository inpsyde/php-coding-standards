<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Scopes;

class NoRootNamespaceFunctionsSniff implements Sniff
{
    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_FUNCTION];
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
        if (Scopes::isOOMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $namespace = Namespaces::determineNamespace($phpcsFile, $stackPtr);
        if ($namespace !== '') {
            return;
        }
        $name = FunctionDeclarations::getName($phpcsFile, $stackPtr);
        if (($name === null) || ($name === '')) {
            return;
        }

        $message = sprintf('The function "%s" is in root namespace.', $name);

        $phpcsFile->addError($message, $stackPtr, 'Found');
    }
}
