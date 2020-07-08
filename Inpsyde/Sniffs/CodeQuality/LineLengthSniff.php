<?php

/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "PHP_CodeSniffer" repository
 * found at https://github.com/squizlabs/PHP_CodeSniffer
 * Copyright (c) 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * released under BSD license.
 */

declare(strict_types=1);

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

class LineLengthSniff implements Sniff
{
    const I18N_FUNCTIONS = [
        '__',
        '_e',
        '_x',
        '_n',
        '_nx',
        '_ex',
        '_n_noop',
        '_nx_noop',
        'esc_html__',
        'esc_html_e',
        'esc_html_x',
        'esc_attr__',
        'esc_attr_e',
        'esc_attr_x',
    ];

    /**
     * The limit that the length of a line should not exceed.
     *
     * @var integer
     */
    public $lineLimit = 100;

    /**
     * @return int[]
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public function process(File $file, $position)
    {
        $longLinesData = $this->collectLongLinesData($file, max(1, $position));
        if (!$longLinesData) {
            return $file->numTokens + 1;
        }

        foreach ($longLinesData as $lineNum => list($length, $start, $end)) {
            $file->addWarning(
                sprintf(
                    'Line %d exceeds %s characters; contains %s characters.',
                    $lineNum,
                    $this->lineLimit,
                    $length
                ),
                $end,
                'TooLong'
            );
        }
        // phpcs:enable

        return $file->numTokens + 1;
    }

    /**
     * @param File $file
     * @param int $start
     * @return array
     */
    private function collectLongLinesData(File $file, int $start): array
    {
        $tokens = $file->getTokens();
        $lines = $counted = [];
        $lastLine = null;
        for ($i = $start; $i < $file->numTokens; $i++) {
            if ($tokens[$i]['line'] === $lastLine) {
                $lines[$lastLine] .= $tokens[$i]['content'];
                continue;
            }

            if ($lastLine && array_key_exists($lastLine, $counted)) {
                $counted[$lastLine][0] = strlen(ltrim($lines[$lastLine]));
                $counted[$lastLine][2] = $i - 1;
            }

            $lastLine = $tokens[$i]['line'];
            $lines[$lastLine] = $tokens[$i]['content'];
            $counted[$lastLine] = [0, $i, -1];
        }

        if ($lastLine && array_key_exists($lastLine, $counted) && $counted[$lastLine][2] === -1) {
            $counted[$lastLine][0] = strlen(ltrim($lines[$lastLine]));
            $counted[$lastLine][2] = $i - 1;
        }

        $longLines = [];
        foreach ($counted as list($length, $start, $end)) {
            if (($length < $this->lineLimit) || ($start < 0) || ($end < 0)) {
                continue;
            }

            if (
                $this->isLongWord($file, $tokens, $start, $end)
                || $this->isLongUse($file, $tokens, $start, $end)
                || $this->isLongI10nFunction($file, $tokens, $start, $end)
            ) {
                continue;
            }

            $longLines[] = [$length, $start, $end];
        }

        return $longLines;
    }

    /**
     * @param File $file
     * @param array $tokens
     * @param $start
     * @param $end
     * @return bool
     */
    private function isLongWord(File $file, array $tokens, $start, $end): bool
    {
        $targetTypes = Tokens::$textStringTokens;
        $targetTypes[] = T_DOC_COMMENT_STRING;

        $foundString = null;

        if ($start === $end)  {
            return true;
        }

        while ($start && ($start < $end)) {
            $stringPos = $file->findNext($targetTypes, $start, $end);
            if ($stringPos === false || $foundString) {
                return false;
            }

            $foundString = $tokens[$stringPos]['content'];
            $start = $stringPos + 1;
        }

        return $foundString && (strlen($foundString) + 10) > $this->lineLimit;
    }

    /**
     * @param File $file
     * @param array $tokens
     * @param $start
     * @param $end
     * @return bool
     */
    private function isLongI10nFunction(File $file, array $tokens, $start, $end): bool
    {
        $tokens = $file->getTokens();

        $stringPos = $file->findNext(
            [T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING],
            $start,
            $end
        );

        if ($stringPos === false) {
            return false;
        }

        $open = $file->findPrevious(T_OPEN_PARENTHESIS, $stringPos, null, false, null, true);
        if ($open === false) {
            return false;
        }

        $functionPos = $file->findPrevious(T_STRING, $open, null, false, null, true);
        if ($functionPos === false) {
            return false;
        }

        $function = strtolower($tokens[$functionPos]['content']);
        if (!in_array($function, self::I18N_FUNCTIONS, true)) {
            return false;
        }

        $close = $file->findEndOfStatement($open);
        if ($close === false) {
            return false;
        }

        $targetLine = $tokens[$stringPos]['line'];
        $textLen = 0;
        for ($i = $open + 1; $i < $close - 1; $i++) {
            if ($tokens[$i]['line'] !== $targetLine) {
                continue;
            }
            $textLen += max(1, strlen(trim($tokens[$i]['content'])));
        }


        return ($textLen + 2) > $this->lineLimit;
    }

    /**
     * @param File $file
     * @param array $tokens
     * @param $start
     * @param $end
     * @return bool
     */
    private function isLongUse(File $file, array $tokens, $start, $end): bool
    {
        $usePos = $file->findNext(T_USE, $start, $end);
        if ($usePos === false) {
            return false;
        }

        $endUse = $file->findEndOfStatement($usePos);
        if ($endUse === false) {
            return false;
        }

        $useLen = 0;
        for ($i = $usePos; $i <= $endUse; $i++) {
            $useLen += strlen($tokens[$i]['content']);
        }

        return $useLen > $this->lineLimit;
    }
}
