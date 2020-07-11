<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NoTopLevelDefineSniff implements Sniff
{
    /**
     * @return array<int>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return [T_STRING];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function process(File $file, $position)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if (
            ($tokens[$position]['content'] ?? '') !== 'define'
            || ($tokens[$position]['level'] ?? -1) !== 0
            || !PhpcsHelpers::looksLikeFunctionCall($file, $position)
        ) {
            return;
        }

        $file->addWarning(
            'Do not use "define" for top-level constant definition. Prefer "const" instead.',
            $position,
            'Found'
        );
    }
}
