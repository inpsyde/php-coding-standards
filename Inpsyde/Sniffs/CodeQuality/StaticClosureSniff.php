<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class StaticClosureSniff implements Sniff
{
    /**
     * @return list<string>
     */
    public function register(): array
    {
        return [T_CLOSURE];
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

        [$functionStart, $functionEnd] = PhpcsHelpers::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $isStatic = $phpcsFile->findPrevious(T_STATIC, $stackPtr, $stackPtr - 3, false, null, true);
        if ($isStatic) {
            return;
        }

        $thisFound = false;
        $i = $functionStart + 1;

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();
        while (!$thisFound && ($i < $functionEnd)) {
            $token = $tokens[$i];
            $thisFound = ($token['code'] === T_VARIABLE) && ($token['content'] === '$this');
            $i++;
        }

        if ($thisFound) {
            return;
        }

        $boundDoc = PhpcsHelpers::functionDocBlockTag('@bound', $phpcsFile, $stackPtr);
        if ($boundDoc) {
            return;
        }

        $varDoc = PhpcsHelpers::functionDocBlockTag('@var', $phpcsFile, $stackPtr);
        foreach ($varDoc as $content) {
            if (preg_match('~(?:^|\s+)\$this(?:$|\s+)~', $content)) {
                return;
            }
        }

        $line = (int)$tokens[$stackPtr]['line'];
        $message = sprintf('Closure found at line %d could be static.', $line);

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'PossiblyStaticClosure')) {
            $this->fix($stackPtr, $phpcsFile);
        }
    }

    /**
     * @param int $position
     * @param File $file
     * @return void
     */
    private function fix(int $position, File $file)
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($position, 'static function');

        $fixer->endChangeset();
    }
}
