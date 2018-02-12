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

namespace Inpsyde\InpsydeCodingStandard\Sniffs\CodeQuality;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Modified version of the `LineLengthSniff` from "Generic" standard that
 * ignores long lines when they contain string
 *
 * @package Inpsyde\InpsydeCodingStandard\Sniffs\CodeQuality
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
    public $lineLimit = 120;

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
        $tokens = $file->getTokens();
        $start = max(1, $position);
        for ($i = $start; $i < $file->numTokens; $i++) {
            if ($tokens[$i]['column'] === 1) {
                $this->checkLineLength($file, $tokens, $i);
            }
        }

        $this->checkLineLength($file, $tokens, $i);

        return ($file->numTokens + 1);
    }

    /**
     * @param File $file
     * @param array $tokens
     * @param int $position
     */
    protected function checkLineLength(File $file, array $tokens, int $position)
    {
        // The passed token is the first on the line.
        $position--;

        if ($tokens[$position]['column'] === 1
            && $tokens[$position]['length'] === 0
        ) {
            // Blank line.
            return;
        }

        if ($tokens[$position]['column'] !== 1
            && $tokens[$position]['content'] === $file->eolChar
        ) {
            $position--;
        }

        $length = $tokens[$position]['column'] + $tokens[$position]['length'] - 1;

        if ($length <= $this->lineLimit
            || $this->shouldIgnoreLine($tokens, $file, $position)
        ) {
            return;
        }

        $file->addWarning(
            'Line exceeds %s characters; contains %s characters',
            $position,
            'TooLong',
            [$this->lineLimit, $length]
        );
    }

    /**
     * Don't warn for lines that exceeds limit, but either are part of
     * translations function first argument or cointain single words that alone
     * are longer that line limit (e.g. long URLs).
     *
     * @param array $tokens
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function shouldIgnoreLine(
        array $tokens,
        File $file,
        int $position
    ): bool {

        $line = $tokens[$position]['line'];
        $index = $position;

        while ($index > 0) {
            if ($this->isI18nFunction($tokens, $index, $file)
                || $this->containLongWords($tokens, $index)
            ) {
                return true;
            }
            if ($tokens[$index]['line'] !== $line) {
                break;
            }
            $index--;
        }

        return false;
    }

    /**
     * @param array $tokens
     * @param int $position
     * @return bool
     */
    private function containLongWords(
        array $tokens,
        int $position
    ): bool {

        $string = $tokens[$position] ?? null;
        if (!$string
            || !in_array($string['code'], self::STRING_TYPES, true)
            || strlen($string['content']) < $this->lineLimit
        ) {
            return false;
        }

        $words = array_filter(preg_split('~[ ,.;:?!¿¡]~', $string['content']));
        foreach ($words as $word) {
            if (strlen($word) >= $this->lineLimit) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $tokens
     * @param int $position
     * @param File $file
     * @return bool
     */
    private function isI18nFunction(
        array $tokens,
        int $position,
        File $file
    ): bool {

        $string = $tokens[$position] ?? null;
        if (!$string
            || $string['code'] !== T_CONSTANT_ENCAPSED_STRING
            || strlen($string['content']) < $this->lineLimit
        ) {
            return false;
        }

        $previousPos = $file->findPrevious(
            self::IGNORE_TYPES,
            $position - 1,
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
            return false;
        }

        $next = $file->findNext(
            [T_WHITESPACE],
            $previousPos + 1,
            null,
            true,
            null,
            true
        );

        return $tokens[$next]['code'] === T_OPEN_PARENTHESIS;
    }
}
