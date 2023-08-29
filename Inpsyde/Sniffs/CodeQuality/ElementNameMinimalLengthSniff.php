<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ElementNameMinimalLengthSniff implements Sniff
{
    public int $minLength = 3;

    /**
     * @var list<string>
     */
    public array $allowedShortNames = [
        'as',
        'at',
        'be',
        'db',
        'do',
        'ex',
        'go',
        'i',
        'id',
        'if',
        'in',
        'io',
        'is',
        'it',
        'js',
        'my',
        'no',
        'of',
        'ok',
        'on',
        'or',
        'to',
        'up',
        'wp',
    ];

    /** @var list<string> */
    public array $additionalAllowedNames = [];

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_CLASS, T_TRAIT, T_INTERFACE, T_CONST, T_FUNCTION, T_VARIABLE];
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

        $elementName = PhpcsHelpers::tokenName($phpcsFile, $stackPtr);
        $elementNameLength = mb_strlen($elementName);

        if ($this->shouldBeSkipped($elementNameLength, $elementName)) {
            return;
        }

        $typeName = PhpcsHelpers::tokenTypeName($phpcsFile, $stackPtr);
        $message = sprintf(
            '%s name "%s" is only %d chars long. Must be at least %d.',
            $typeName,
            $elementName,
            $elementNameLength,
            $this->minLength
        );

        $phpcsFile->addError($message, $stackPtr, 'TooShort');
    }

    /**
     * @param int $elementNameLength
     * @param string $elementName
     * @return bool
     */
    private function shouldBeSkipped(int $elementNameLength, string $elementName): bool
    {
        return ($elementNameLength >= $this->minLength) || $this->isShortNameAllowed($elementName);
    }

    /**
     * @param string $variableName
     * @return bool
     */
    private function isShortNameAllowed(string $variableName): bool
    {
        $target = strtolower($variableName);

        foreach ($this->allowedShortNames as $allowed) {
            if (strtolower($allowed) === $target) {
                return true;
            }
        }

        foreach ($this->additionalAllowedNames as $allowed) {
            if (strtolower($allowed) === $target) {
                return true;
            }
        }

        return false;
    }
}
