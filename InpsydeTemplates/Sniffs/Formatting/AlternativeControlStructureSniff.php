<?php

declare(strict_types=1);

namespace InpsydeTemplates\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\ControlStructures;

/**
 * The implementation is inspired by Universal.ControlStructures.DisallowAlternativeSyntaxSniff.
 *
 * @link https://github.com/PHPCSStandards/PHPCSExtra/blob/ed86bb117c340f654eab603a06b95a437ac619c9/Universal/Sniffs/ControlStructures/DisallowAlternativeSyntaxSniff.php
 *
 * @psalm-type Token = array{
 *     type: string,
 *     code: string|int,
 *     line: int,
 *     scope_opener?: int,
 *     scope_closer?: int,
 *     scope_condition?: int,
 *     content: string,
 *  }
 */
final class AlternativeControlStructureSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_IF,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
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
        if (ControlStructures::hasBody($phpcsFile, $stackPtr) === false) {
            // Single line control structure is out of scope.
            return;
        }

        /** @var array<int, Token> $tokens */
        $tokens = $phpcsFile->getTokens();
        /** @var int | null $scopeOpener */
        $openerPtr = $tokens[$stackPtr]['scope_opener'] ?? null;
        /** @var int | null $scopeCloser */
        $closerPtr = $tokens[$stackPtr]['scope_closer'] ?? null;

        if (!isset($openerPtr, $closerPtr, $tokens[$openerPtr])) {
            // Inline control structure or parse error.
            return;
        }

        if ($tokens[$openerPtr]['code'] === T_COLON) {
            // Alternative control structure.
            return;
        }

        $chainedIssues = $this->findChainedIssues($phpcsFile, $stackPtr);

        $message = 'Control structure having inline HTML should use alternative syntax.'
            . ' Found "%s".';
        foreach ($chainedIssues as $conditionPtr) {
            $phpcsFile->addWarning(
                $message,
                $conditionPtr,
                'Encouraged',
                [$tokens[$conditionPtr]['content']]
            );
        }
    }

    /**
     * We consider if - else (else if) chain as the single structure
     * as they should be replaced with alternative syntax altogether.
     *
     * @return list<int> List of scope condition positions
     */
    private function findChainedIssues(File $phpcsFile, int $stackPtr): array
    {
        /** @var array<int, Token> $tokens */
        $tokens = $phpcsFile->getTokens();
        $hasInlineHtml = false;
        $currentPtr = $stackPtr;
        $chainedIssues = [];

        do {
            $openerPtr = $tokens[$currentPtr]['scope_opener'] ?? null;
            $closerPtr = $tokens[$currentPtr]['scope_closer'] ?? null;
            if (!isset($openerPtr, $closerPtr)) {
                // Something went wrong.
                break;
            }

            $chainedIssues[] = $currentPtr;
            if (!$hasInlineHtml) {
                $hasInlineHtml = $phpcsFile->findNext(T_INLINE_HTML, ($currentPtr + 1), $closerPtr) !== false;
            }

            $currentPtr = $this->findNextChainPointer($phpcsFile, $closerPtr);
        } while (
            is_int($currentPtr)
        );

        return $hasInlineHtml ? $chainedIssues : [];
    }

    /**
     * Find 3 possible options:
     *  - else
     *  - elseif
     *  - else if
     */
    private function findNextChainPointer(File $phpcsFile, int $closerPtr): ?int
    {
        /** @var array<int, Token> $tokens */
        $tokens = $phpcsFile->getTokens();
        $firstPtr = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($closerPtr + 1),
            null,
            true
        );

        if (!is_int($firstPtr) || !isset($tokens[$firstPtr])) {
            return null;
        }

        if ($tokens[$firstPtr]['code'] === T_ELSEIF) {
            return $firstPtr;
        }

        if ($tokens[$firstPtr]['code'] !== T_ELSE) {
            return null;
        }

        $secondPtr = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($firstPtr + 1),
            null,
            true
        );

        $isIfOpenerPtr = is_int($secondPtr) && isset($tokens[$secondPtr]) && $tokens[$secondPtr]['code'] === T_IF;

        return $isIfOpenerPtr ? $secondPtr : $firstPtr;
    }
}
