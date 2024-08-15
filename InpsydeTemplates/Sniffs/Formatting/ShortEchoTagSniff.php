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
final class ShortEchoTagSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_ECHO,
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

        $prevPtr = $phpcsFile->findPrevious(
            Tokens::$emptyTokens,
            ($stackPtr - 1),
            null,
            true
        );

        if (!is_int($prevPtr) || !isset($tokens[$prevPtr])) {
            return;
        }

        $prevToken = $tokens[$prevPtr];

        if ($prevToken['line'] !== $currentLine) {
            return;
        }

        if ($prevToken['code'] !== T_OPEN_TAG) {
            return;
        }

        $closeTagPtr = $phpcsFile->findNext(
            T_CLOSE_TAG,
            ($stackPtr + 1),
        );

        if (
            !is_int($closeTagPtr)
            || !isset($tokens[$closeTagPtr])
            || $tokens[$closeTagPtr]['line'] !== $currentLine
        ) {
            return;
        }

        $message = sprintf(
            'Single line output on line %d'
            . ' should use short echo tag `<?= ` instead of `<?php echo`.',
            $currentLine
        );

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'Encouraged')) {
            $this->fix($prevPtr, $stackPtr, $phpcsFile);
        }
    }

    private function fix(int $openTagPtr, int $echoPtr, File $file): void
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($echoPtr, '');
        $fixer->replaceToken($openTagPtr, '<?=');

        $fixer->endChangeset();
    }
}
