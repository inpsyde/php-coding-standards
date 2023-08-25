<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class NoAccessorsSniff implements Sniff
{
    public const ALLOWED_NAMES = [
        'getIterator',
        'getInnerIterator',
        'getChildren',
        'setUp',
    ];

    public bool $skipForPrivate = true;
    public bool $skipForProtected = false;

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
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        if (!PhpcsHelpers::functionIsMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if (!$functionName || in_array($functionName, self::ALLOWED_NAMES, true)) {
            return;
        }

        if ($this->skipForPrivate || $this->skipForProtected) {
            $modifierPointerPosition = $phpcsFile->findPrevious(
                [T_WHITESPACE, T_ABSTRACT],
                $stackPtr - 1,
                null,
                true,
                null,
                true
            );

            /** @var array<int, array<string, mixed>> $tokens */
            $tokens = $phpcsFile->getTokens();
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
            $phpcsFile->addWarning(
                'Setters are discouraged. Try to use immutable objects, constructor injection '
                . 'and for objects that really needs changing state try behavior naming instead, '
                . 'e.g. changeName() instead of setName().',
                $stackPtr,
                'NoSetter'
            );

            return;
        }

        $phpcsFile->addWarning(
            'Getters are discouraged. "Tell Don\'t Ask" principle should be applied if possible, '
            . 'and if getters are really needed consider naming methods after properties, '
            . 'e.g. id() instead of getId().',
            $stackPtr,
            'NoGetter'
        );
    }
}
