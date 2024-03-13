<?php

declare(strict_types=1);

namespace InpsydeTemplates\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @psalm-type Token = array{
 *     type: string,
 *     code: string|int,
 *     line: int
 *     }
 */
final class TrailingSemicolonSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_SEMICOLON,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        /** @var array<int, Token> $tokens */
        $tokens = $phpcsFile->getTokens();
        $currentLine = $tokens[$stackPtr]['line'];

        $nextNonEmptyPosition = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($stackPtr + 1),
            null,
            true
        );

        if (!is_int($nextNonEmptyPosition) || !isset($tokens[$nextNonEmptyPosition])) {
            return;
        }

        $nextNonEmptyToken = $tokens[$nextNonEmptyPosition];

        if ($nextNonEmptyToken['line'] !== $currentLine) {
            return;
        }

        if ($nextNonEmptyToken['code'] !== T_CLOSE_TAG) {
            return;
        }

        $message = sprintf('Trailing semicolon found in line %d.', $currentLine);

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'Found')) {
            $this->fix($stackPtr, $phpcsFile);
        }
    }

    /**
     * @param int $position
     * @param File $file
     */
    private function fix(int $position, File $file): void
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($position, '');

        $fixer->endChangeset();
    }
}
