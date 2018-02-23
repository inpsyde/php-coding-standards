<?php declare(strict_types=1); # -*- coding: utf-8 -*-
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

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Modified version of the `LineLengthSniff` from "Generic" standard that
 * ignores long lines when they contain string
 *
 * @package Inpsyde\Sniffs\CodeQuality
 */
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

    const IGNORE_TYPES = [
        T_WHITESPACE,
        T_OPEN_PARENTHESIS,
        T_COMMENT,
        T_DOC_COMMENT,
        T_DOC_COMMENT_CLOSE_TAG,
        T_DOC_COMMENT_OPEN_TAG,
        T_DOC_COMMENT_STAR,
        T_DOC_COMMENT_WHITESPACE,
        T_DOC_COMMENT_STRING,
        T_DOC_COMMENT_TAG,
    ];

    const STRING_TYPES = [
        T_CONSTANT_ENCAPSED_STRING,
        T_COMMENT,
        T_DOC_COMMENT_STRING,
    ];

    /**
     * The limit that the length of a line should not exceed.
     *
     * @var integer
     */
    public $lineLimit = 100;

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_OPEN_TAG];
    }

    /**
     * @inheritdoc
     */
    public function process(File $file, $position)
    {
        $longLinesData = $this->collectLongLinesData($file, max(1, $position));
        if (!$longLinesData) {
            return $file->numTokens + 1;
        }

        // phpcs:disable VariableAnalysis
        foreach ($longLinesData as $lineNum => list($length, $start, $end)) {
            if ($this->shouldIgnoreLine($file, $start, $end)) {
                continue;
            }

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
    protected function collectLongLinesData(File $file, int $start): array
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

        return array_filter(
            $counted,
            function (array $line): bool {
                return $line[0] > $this->lineLimit && $line[1] > 0 && $line[2] > 0;
            }
        );
    }

    /**
     * Don't warn for lines that exceeds limit, but either are part of
     * translations function first argument or contain single words that alone
     * are longer that line limit (e.g. long URLs).
     *
     * @param File $file
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function shouldIgnoreLine(File $file, int $start, int $end): bool
    {
        return
            $this->containLongWords($file, $start, $end)
            || $this->isI18nFunction($file, $start, $end);
    }

    /**
     * @param File $file
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function containLongWords(File $file, int $start, int $end): bool
    {
        $tokens = $file->getTokens();

        for ($i = $start; $i <= $end; $i++) {
            if (!in_array($tokens[$i]['code'], self::STRING_TYPES, true)) {
                continue;
            }

            $words = array_filter(preg_split('~[\s+]~', $tokens[$i]['content']));
            if (count($words) > 2) {
                return false;
            }

            return (bool)array_filter(
                array_map('strlen', $words),
                function (int $len): bool {
                    return ($len + 3) > $this->lineLimit;
                }
            );
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isI18nFunction(File $file, int $start, int $end): bool
    {
        $tokens = $file->getTokens();

        for ($i = $start; $i <= $end; $i++) {
            if ($tokens[$i]['code'] !== T_CONSTANT_ENCAPSED_STRING
                || (strlen($tokens[$i]['content']) + 3) < $this->lineLimit
            ) {
                continue;
            }

            $previousPos = $file->findPrevious(
                self::IGNORE_TYPES,
                $i - 1,
                null,
                true,
                null,
                true
            );

            $previous = $previousPos ? $tokens[$previousPos] ?? null : null;
            if (!$previous
                || $previous['code'] !== T_STRING
                || !in_array($previous['content'], self::I18N_FUNCTIONS, true)
            ) {
                continue;
            }

            $next = $file->findNext(
                [T_WHITESPACE],
                $previousPos + 1,
                null,
                true,
                null,
                true
            );

            if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
                return true;
            }
        }

        return false;
    }
}
