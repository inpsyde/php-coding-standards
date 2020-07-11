<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class ForbiddenPublicPropertySniff implements Sniff
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

        return [T_VARIABLE];
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

        if (!PhpcsHelpers::variableIsProperty($file, $position)) {
            return;
        }

        // Skip sniff classes, they have public properties for configuration (unfortunately)
        if ($this->isSniffClass($file, $position)) {
            return;
        }

        $scopeModifierToken = $this->propertyScopeModifier($file, $position);
        if ($scopeModifierToken['code'] === T_PUBLIC) {
            $file->addError(
                'Do not use public properties. Use method access instead.',
                $position,
                'Found'
            );
        }
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function isSniffClass(File $file, int $position): bool
    {
        $classNameTokenPosition = $file->findNext(
            T_STRING,
            (int)$file->findPrevious(T_CLASS, $position)
        );

        if ($classNameTokenPosition === false) {
            return false;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $classNameToken = $tokens[$classNameTokenPosition];

        if (substr((string)$classNameToken['content'], -5, 5) === 'Sniff') {
            return true;
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $position
     * @return array
     */
    private function propertyScopeModifier(File $file, int $position): array
    {
        $scopeModifierPosition = $file->findPrevious(Tokens::$scopeModifiers, ($position - 1));
        if ($scopeModifierPosition === false) {
            return ['code' => T_PUBLIC];
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        return $tokens[$scopeModifierPosition];
    }
}
