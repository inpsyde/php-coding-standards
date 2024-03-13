<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

class DisableMagicSerializeSniff implements Sniff
{
    /** @var list<string>  */
    public array $disabledFunctions = [
        '__serialize',
        '__sleep',
        '__unserialize',
        '__wakeup',
    ];

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
        if (!Scopes::isOOMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $name = FunctionDeclarations::getName($phpcsFile, $stackPtr);
        if (in_array($name, $this->disabledFunctions, true)) {
            $phpcsFile->addError(
                sprintf(
                    'The method "%s" is forbidden, please use Serializable interface.',
                    $name
                ),
                $stackPtr,
                'Found'
            );
        }
    }
}
