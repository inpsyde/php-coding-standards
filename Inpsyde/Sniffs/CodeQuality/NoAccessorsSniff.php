<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class NoAccessorsSniff implements Sniff
{
    const ALLOWED_NAMES = [
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'setUp',
    ];

    /**
     * @var bool
     */
    public $skipForPrivate = true;

    /**
     * @var bool
     */
    public $skipForProtected = false;

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

        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     */
    public function process(File $file, $position)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        if (!PhpcsHelpers::functionIsMethod($file, $position)) {
            return;
        }

        $functionName = $file->getDeclarationName($position);
        if (!$functionName || in_array($functionName, self::ALLOWED_NAMES, true)) {
            return;
        }

        if ($this->skipForPrivate || $this->skipForProtected) {
            $modifierPointerPosition = $file->findPrevious(
                [T_WHITESPACE, T_ABSTRACT],
                $position - 1,
                null,
                true,
                null,
                true
            );

            /** @var array<int, array<string, mixed>> $tokens */
            $tokens = $file->getTokens();
            $modifierPointer = $tokens[$modifierPointerPosition] ?? null;
            if (
                $modifierPointer
                && !in_array($modifierPointer['code'], Tokens::$scopeModifiers, true)
            ) {
                $modifierPointer = null;
            }

            $modifier = $modifierPointer ? $modifierPointer['code'] ?? null : null;
            if (
                ($modifier === T_PRIVATE && $this->skipForPrivate)
                || ($modifier === T_PROTECTED && $this->skipForProtected)
            ) {
                return;
            }
        }

        preg_match('/^(set|get)[_A-Z0-9]+/', $functionName, $matches);
        if (!$matches) {
            return;
        }

        if ($matches[1] === 'set') {
            $file->addWarning(
                'Setters are discouraged. Try to use immutable objects, constructor injection '
                . 'and for objects that really needs changing state try behavior naming instead, '
                . 'e.g. changeName() instead of setName().',
                $position,
                'NoSetter'
            );

            return;
        }

        $file->addWarning(
            'Getters are discouraged. "Tell Don\'t Ask" principle should be applied if possible, '
            . 'and if getters are really needed consider naming methods after properties, '
            . 'e.g. id() instead of getId().',
            $position,
            'NoGetter'
        );
    }
}
