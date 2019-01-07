<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file contains code from "phpcs-calisthenics-rules" repository
 * found at https://github.com/object-calisthenics
 * Copyright (c) 2014 Doctrine Project
 * released under MIT license.
 */

namespace Inpsyde\Sniffs\CodeQuality;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FunctionLengthSniff implements Sniff
{
    /**
     * @var int
     */
    public $maxLength = 50;

    /**
     * @var true
     */
    public $ignoreDocBlocks = true;

    /**
     * @var true
     */
    public $ignoreComments = true;

    /**
     * @var true
     */
    public $ignoreBlankLines = true;

    /**
     * @return int[]
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position)
    {
        $length = $this->structureLinesCount($file, $position);
        if ($length <= $this->maxLength) {
            return;
        }

        $ignored = [];
        $suffix = '';
        $this->normalizeIgnoreFlags();
        $this->ignoreBlankLines and $ignored[] = 'blank lines';
        $this->ignoreComments and $ignored[] = 'single line comments';
        $this->ignoreDocBlocks and $ignored[] = 'doc blocks';
        if ($ignored) {
            $suffix = ' (ignoring ';
            $last = array_pop($ignored);
            $others = implode(', ', $ignored);
            $suffix .= $others ? "{$others} and {$last})" : "{$last})";
        }

        $error = sprintf(
            'Your function is too long. Currently using %d lines%s, max is %d.',
            $length,
            $suffix,
            $this->maxLength
        );

        $file->addError($error, $position, 'TooLong');
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public function structureLinesCount(File $file, int $position): int
    {
        $tokens = $file->getTokens();
        $token = $tokens[$position] ?? [];

        if (!array_key_exists('scope_opener', $token)
            || !array_key_exists('scope_closer', $token)
        ) {
            return 0;
        }

        $start = $token['scope_opener'];
        $end = $token['scope_closer'];
        $length = $tokens[$end]['line'] - $tokens[$start]['line'];

        if ($length < $this->maxLength) {
            return $length;
        }

        return $length - $this->collectLinesToExclude($start, $end, $tokens);
    }

    /**
     * @param int $start
     * @param int $end
     * @param array $tokens
     * @return int
     */
    private function collectLinesToExclude(
        int $start,
        int $end,
        array $tokens
    ): int {

        $linesData = $docblocks = [];

        $skipLines = [$tokens[$start + 1]['line'], $tokens[$end]['line']];
        for ($i = $start + 1; $i < $end - 1; $i++) {
            if (in_array($tokens[$i]['line'], $skipLines, true)) {
                continue;
            }

            $docblocks = $this->docBlocksData($tokens, $i, $docblocks);
            $linesData = $this->ignoredLinesData($tokens[$i], $linesData);
        }

        $empty = array_filter(array_column($linesData, 'empty'));
        $onlyComment = array_filter(array_column($linesData, 'only-comment'));

        $toExcludeCount = array_sum($docblocks);
        if ($this->ignoreBlankLines) {
            $toExcludeCount += count($empty);
        }
        if ($this->ignoreComments) {
            $toExcludeCount += count($onlyComment) - count($empty);
        }

        return $toExcludeCount;
    }

    /**
     * @param array $token
     * @param array $lines
     * @return array
     */
    private function ignoredLinesData(array $token, array $lines): array
    {
        $line = $token['line'];
        if (!array_key_exists($line, $lines)) {
            $lines[$line] = ['empty' => true, 'only-comment' => true];
        }

        if (!in_array($token['code'], [T_COMMENT, T_WHITESPACE], true)) {
            $lines[$line]['only-comment'] = false;
        }

        if ($token['code'] !== T_WHITESPACE) {
            $lines[$line]['empty'] = false;
        }

        return $lines;
    }

    /**
     * @param array $tokens
     * @param int $position
     * @param array $docBlocks
     * @return array
     */
    private function docBlocksData(
        array $tokens,
        int $position,
        array $docBlocks
    ): array {

        if (!$this->ignoreDocBlocks
            || $tokens[$position]['code'] !== T_DOC_COMMENT_OPEN_TAG
        ) {
            return $docBlocks;
        }

        $closer = $tokens[$position]['comment_closer'] ?? null;
        $docBlocks[] = is_numeric($closer)
            ? 1 + ($tokens[$closer]['line'] - $tokens[$position]['line'])
            : 1;

        return $docBlocks;
    }

    private function normalizeIgnoreFlags()
    {
        $flags = [
            'ignoreBlankLines',
            'ignoreComments',
            'ignoreDocBlocks',
        ];

        foreach ($flags as $flag) {
            if (is_string($this->{$flag})) {
                $this->{$flag} = (bool)filter_var($this->{$flag}, FILTER_VALIDATE_BOOLEAN);
            }
        }
    }
}
