<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NestingLevelSniff implements Sniff
{
    public int $warningLimit = 3;
    public int $errorLimit = 5;
    public bool $ignoreTopLevelTryBlock = true;

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
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        // Ignore abstract methods.
        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        $start = (int)$tokens[$stackPtr]['scope_opener'];
        $end = (int)$tokens[$stackPtr]['scope_closer'];

        $baseLevel = (int)$tokens[$stackPtr]['level'];
        $nestingLevel = 0;
        $inTry = false;
        $endTry = null;
        $tryTargetLevel = filter_var($this->ignoreTopLevelTryBlock, FILTER_VALIDATE_BOOLEAN)
            ? $baseLevel + 1
            : $baseLevel - 2; // This is an impossible level, so the conditions below will be false

        // Find the maximum nesting level of any token in the function.
        for ($i = ($start + 1); $i < $end; $i++) {
            if ($inTry && $i === $endTry) {
                $inTry = false;
                continue;
            }

            $level = (int)$tokens[$i]['level'];

            if (!$inTry && $tokens[$i]['code'] === T_TRY && $level === $tryTargetLevel) {
                $inTry = true;
                continue;
            }

            if (
                $inTry
                && ($endTry === null)
                && ($tokens[$i]['code'] === T_CATCH || $tokens[$i]['code'] === T_FINALLY)
                && $level === $tryTargetLevel
            ) {
                $endTry = $this->endOfTryBlock($i, $phpcsFile);
                continue;
            }

            if ($inTry) {
                $level--;
            }

            if ($level > $nestingLevel) {
                $nestingLevel = $level;
            }
        }

        // We subtract the nesting level of the function itself .
        $nestingLevel -= ($baseLevel + 1);

        $this->maybeTrigger($nestingLevel, $phpcsFile, $stackPtr);
    }

    /**
     * @param int $nestingLevel
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    private function maybeTrigger(int $nestingLevel, File $phpcsFile, int $stackPtr): void
    {
        $isError = $nestingLevel >= $this->errorLimit;
        $isWarning = !$isError && ($nestingLevel >= $this->warningLimit);

        if (!$isError && !$isWarning) {
            return;
        }

        $message = 'Function\'s nesting level (%s) exceeds %s';
        $message .= $isError ? ', please refactor it.' : ', consider to refactor it.';

        $code = $isError ? 'MaxExceeded' : 'High';
        $limit = $isError ? $this->errorLimit : $this->warningLimit;

        $isError
            ? $phpcsFile->addError($message, $stackPtr, $code, [$nestingLevel, $limit])
            : $phpcsFile->addWarning($message, $stackPtr, $code, [$nestingLevel, $limit]);
    }

    /**
     * @param int $catchPosition
     * @param File $phpcsFile
     * @return int
     */
    private function endOfTryBlock(int $catchPosition, File $phpcsFile): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        $currentEnd = (int)$tokens[$catchPosition]['scope_closer'];
        $nextCatch = $phpcsFile->findNext(T_CATCH, $currentEnd + 1, $currentEnd + 3);
        if ($nextCatch) {
            return $this->endOfTryBlock($nextCatch, $phpcsFile);
        }

        $finally = $phpcsFile->findNext(T_FINALLY, $currentEnd + 1, $currentEnd + 3);

        return $finally ? (int)$tokens[$finally]['scope_closer'] + 1 : $currentEnd + 1;
    }
}
