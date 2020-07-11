<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class FunctionBodyStartSniff implements Sniff
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

        return [T_FUNCTION];
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
        $token = $tokens[$position] ?? [];

        $scopeOpener = (int)($token['scope_opener'] ?? -1);
        $scopeCloser = (int)($token['scope_closer'] ?? -1);

        if ($scopeOpener < 0 || $scopeCloser < 0 || $scopeCloser <= $scopeOpener) {
            return;
        }

        $bodyStart = $file->findNext([T_WHITESPACE], $scopeOpener + 1, null, true);
        if (
            !$bodyStart
            || !array_key_exists($bodyStart, $tokens)
            || $bodyStart <= $scopeOpener
            || $bodyStart >= $scopeCloser
        ) {
            return;
        }

        list($code, $message, $expectedLine) = $this->checkBodyStart(
            $bodyStart,
            (int)($tokens[$scopeOpener]['line'] ?? -1),
            (int)($token['line'] ?? -1),
            $file
        );

        if (
            $code
            && $message
            && $expectedLine
            && $file->addFixableWarning($message, $position, $code)
        ) {
            $this->fix($bodyStart, $expectedLine, $scopeOpener, $file);
        }
    }

    /**
     * @param int $bodyStart
     * @param int $openerLine
     * @param int $functionLine
     * @param File $file
     * @return array{null, null, null}|array{string, string, int}
     */
    private function checkBodyStart(
        int $bodyStart,
        int $openerLine,
        int $functionLine,
        File $file
    ): array {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $bodyLine = (int)($tokens[$bodyStart]['line'] ?? -1);

        $isMultiLineDeclare = ($openerLine - $functionLine) > 1;
        $isSingleLineDeclare = $openerLine === ($functionLine + 1);
        $isSingleLineSignature = $openerLine && ($openerLine === $functionLine);

        $error =
            ($isMultiLineDeclare || $isSingleLineSignature) && $bodyLine !== ($openerLine + 2)
            || $isSingleLineDeclare && $bodyLine > ($openerLine + 2);

        if (!$error) {
            return [null, null, null];
        }

        $startWithComment = in_array($tokens[$bodyStart]['code'], Tokens::$emptyTokens, true);

        if (!$startWithComment && ($isMultiLineDeclare || $isSingleLineSignature)) {
            $where = $isSingleLineSignature
                ? 'with single-line signature and open curly bracket on same line'
                : 'where arguments declaration spans across multiple lines';
            $code = $isSingleLineSignature
                ? 'WrongForSingleLineSignature'
                : 'WrongForMultiLineDeclaration';

            return [
                $code,
                "In functions {$where}, function body should start with a blank line.",
                $openerLine + 2,
            ];
        }

        if (!$isSingleLineDeclare) {
            return [null, null, null];
        }

        $message = 'In functions where arguments declaration is in a single line and curly bracket '
            . 'is on next line, function body should start in the line below opened curly bracket.';

        return [
            'WrongForSingleLineDeclaration',
            $message,
            $openerLine + 1,
        ];
    }

    /**
     * @param int $bodyStart
     * @param int $expectedLine
     * @param int $scopeOpener
     * @param File $file
     * @return void
     */
    private function fix(int $bodyStart, int $expectedLine, int $scopeOpener, File $file)
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();
        $currentLine = (int)($tokens[$bodyStart]['line'] ?? -1);

        if ($currentLine === $expectedLine) {
            return;
        }

        $fixer = $file->fixer;
        $fixer->beginChangeset();

        if ($currentLine < $expectedLine) {
            for ($i = ($expectedLine - $currentLine); $i > 0; $i--) {
                $fixer->addNewline($scopeOpener);
            }
            $fixer->endChangeset();

            return;
        }

        for ($i = $bodyStart - 1; $i > 0; $i--) {
            $line = $tokens[$i]['line'];
            if ($line === $currentLine) {
                continue;
            }
            if ($line < $expectedLine) {
                break;
            }

            $fixer->replaceToken($i, '');
        }

        $fixer->endChangeset();
    }
}
