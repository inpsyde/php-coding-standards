<?php

/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class StaticClosureSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register()
    {
        return [T_CLOSURE];
    }

    /**
     * @param File $file
     * @param int $stackPtr
     * @return void
     */
    public function process(File $file, $stackPtr)
    {
        list($functionStart, $functionEnd) = PhpcsHelpers::functionBoundaries($file, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $isStatic = $file->findPrevious(T_STATIC, $stackPtr, $stackPtr - 3, false, null, true);
        if ($isStatic) {
            return;
        }

        $thisFound = false;
        $i = $functionStart + 1;
        $tokens = $file->getTokens();
        while (!$thisFound && ($i < $functionEnd)) {
            $thisFound = $tokens[$i]['content'] === '$this';
            $i++;
        }

        if ($thisFound) {
            return;
        }

        $docTokens = array_values(PhpcsHelpers::functionDocTokens($file, $stackPtr));

        foreach ($docTokens as $index => $docToken) {
            $content = $docToken['content'] ?? '';
            $code = $docToken['code'] ?? '';
            $prevToken = $docTokens[$index - 1] ?? [];
            $prevContent = $prevToken['content'] ?? '';
            $prevCode = $prevToken['code'] ?? '';

            if (
                ($code === T_DOC_COMMENT_TAG && $content === '@bound')
                || (
                    ($prevCode === T_DOC_COMMENT_TAG && $prevContent === '@var')
                    && $code === T_DOC_COMMENT_STRING
                    && substr_count($content, '$this')
                )
            ) {
                return;
            }
        }

        $message = sprintf('Closure found at line %d could be static.', $tokens[$stackPtr]['line']);
        if ($file->addFixableWarning($message, $stackPtr, 'PossiblyStaticClosure')) {
            $this->fix($stackPtr, $file);
        }
    }

    private function fix(int $stackPtr, File $file)
    {
        $fixer = $file->fixer;
        $fixer->beginChangeset();

        $fixer->replaceToken($stackPtr, 'static function');

        $fixer->endChangeset();
    }
}
