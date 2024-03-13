<?php

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\PassedParameters;

class EncodingCommentSniff implements Sniff
{
    private const DISALLOWED_COMMENT = '-*- coding: utf-8 -*-';

    /**
     * @return list<int>
     */
    public function register(): array
    {
        return [T_COMMENT];
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

        $tokens = $phpcsFile->getTokens();

        $comment = isset($tokens[$stackPtr]['content']) && is_string($tokens[$stackPtr]['content'])
            ? $tokens[$stackPtr]['content']
            : '';

        if (strpos($comment, self::DISALLOWED_COMMENT) === false) {
            return;
        }

        $fix = $phpcsFile->addFixableWarning(
            'Found outdated encoding declaration in comment.',
            $stackPtr,
            'EncodingComment'
        );

        if ($fix) {
            $this->fix($phpcsFile, $stackPtr);
        }
    }

    private function fix(File $phpcsFile, int $position): void
    {
        $phpcsFile->fixer->beginChangeset();

        $phpcsFile->fixer->replaceToken($position, '');

        $phpcsFile->fixer->endChangeset();
    }
}
