<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class FunctionBodyStartSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr] ?? [];

        /** @var int $scopeOpener */
        $scopeOpener = $token['scope_opener'] ?? -1;
        $scopeCloser = $token['scope_closer'] ?? -1;

        if ($scopeOpener < 0 || $scopeCloser < 0 || $scopeCloser <= $scopeOpener) {
            return;
        }

        $bodyStart = $phpcsFile->findNext([T_WHITESPACE], $scopeOpener + 1, null, true);
        if (!$bodyStart
            || !array_key_exists($bodyStart, $tokens)
            || $bodyStart <= $scopeOpener
            || $bodyStart >= $scopeCloser
        ) {
            return;
        }

        list($code, $message, $expectedLine) = $this->checkBodyStart(
            $bodyStart,
            $tokens[$scopeOpener]['line'] ?? -1,
            $token['line'] ?? -1,
            $phpcsFile
        );

        if ($code && $message && $phpcsFile->addFixableWarning($message, $stackPtr, $code)) {
            $this->fix($bodyStart, $expectedLine, $scopeOpener, $phpcsFile);
        }
    }

    /**
     * @param int $bodyStart
     * @param int $openerLine
     * @param int $functionLine
     * @param File $phpcsFile
     * @return array
     */
    private function checkBodyStart(
        int $bodyStart,
        int $openerLine,
        int $functionLine,
        File $phpcsFile
    ): array {

        $tokens = $phpcsFile->getTokens();
        $bodyLine = $tokens[$bodyStart]['line'] ?? -1;

        $isMultiLineDeclare = ($openerLine - $functionLine) > 1;
        $isSingleLineDeclare = $openerLine === ($functionLine + 1);
        $isSingleLineSignature = $openerLine && $openerLine === $functionLine;

        $error =
            ($isMultiLineDeclare || $isSingleLineSignature) && $bodyLine !== ($openerLine + 2)
            || $isSingleLineDeclare && $bodyLine !== ($openerLine + 1);

        if (!$error) {
            return [null, null, null];
        }

        $startWithComment = in_array($tokens[$bodyStart]['code'], Tokens::$emptyTokens, true);

        if (!$startWithComment && ($isMultiLineDeclare || $isSingleLineSignature)) {
            $where = $isSingleLineSignature === 'SingleLineSignature'
                ? 'with single-line signature and open curly bracket on same line'
                : 'where arguments declaration spans across multiple lines';
            $code = $isSingleLineSignature
                ? 'WrongForSingleLineSignature'
                : 'WrongForMultiLineDeclaration';

            return [
                $code,
                "In functions {$where}, function body should start with a blank line.",
                $openerLine + 2
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
            $openerLine + 1
        ];
    }

    /**
     * @param int $bodyStart
     * @param int $expectedLine
     * @param int $scopeOpener
     * @param File $file
     */
    private function fix(int $bodyStart, int $expectedLine, int $scopeOpener, File $file)
    {
        $tokens = $file->getTokens();
        $currentLine = $tokens[$bodyStart]['line'] ?? -1;

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
