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

        foreach ($longLinesData as $lineNum => $lineData) {
            $file->addWarning(
                sprintf(
                    'Line %d exceeds %s characters; contains %s characters.',
                    $lineNum,
                    $this->lineLimit,
                    $lineData->length
                ),
                $lineData->start,
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
        $linesData = [];
        $lastLine = null;
        for ($i = $start; $i < $file->numTokens; $i++) {
            // Still processing previous line: increment length and continue.
            if ($lastLine && ($tokens[$i]['line'] === $lastLine)) {
                $linesData[$lastLine]->length += strlen($tokens[$i]['content']);
                $linesData[$lastLine]->nonEmptyLength += strlen(trim($tokens[$i]['content']));
                continue;
            }

            // A new line started: let's set "end" for the previous line (if this isn't 1st line)
            if ($lastLine && isset($linesData[$lastLine])) {
                $linesData[$lastLine]->end = $i - 1;
            }

            $lastLine = $tokens[$i]['line'];
            $linesData[$lastLine] = (object)[
                'length' => strlen($tokens[$i]['content']),
                'nonEmptyLength' => strlen(trim($tokens[$i]['content'])),
                'start' => $i,
                'end' => null,
            ];
        }

        // We still have to set the "end" for last file line.
        if ($lastLine && (($linesData[$lastLine]->end ?? 0) === null)) {
            $linesData[$lastLine]->end = $i - 1;
        }

        $longLines = [];
        foreach ($linesData as $lineNumber => $lineData) {
            if (
                (($lineData->length - $this->lineLimit) <= 1) // 1 char of tolerance
                || ($lineData->nonEmptyLength === 0) // ignore empty lines
                || $this->isLongUse($file, $tokens, $lineData->start, $lineData->end)
                || $this->isLongI10nFunction($file, $tokens, $lineData->start, $lineData->end)
                || $this->isLongWord($file, $tokens, $lineData->start, $lineData->end)
            ) {
                continue;
            }

            $longLines[$lineNumber] = $lineData;
        }

        return $longLines;
    }

    /**
     * We don't want to split a single word in multiple lines.
     * So if there's a long word (e.g. an URL) that alone is above max line length, we don't show
     * warnings for it.
     *
     * @param File $file
     * @param array $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isLongWord(File $file, array $tokens, int $start, int $end): bool
    {
        $targetTypes = Tokens::$textStringTokens;
        $targetTypes[] = T_DOC_COMMENT_STRING;

        $foundString = false;
        ($start === $end) and $end++;

        while ($start && ($start < $end)) {
            $stringPos = $file->findNext($targetTypes, $start, $end);
            if ($stringPos === false || $foundString) {
                return false;
            }

            $isHtml = $tokens[$stringPos]['code'] === T_INLINE_HTML;
            $isLong = $isHtml
                ? $this->isLongHtmlAttribute($stringPos, $file, $tokens, $start, $end)
                : $this->isLongSingleWord($stringPos, $file, $tokens, $start, $end);

            if (!$isLong || $isHtml) {
                return $isLong && $isHtml;
            }

            $foundString = true;
            $start = $stringPos + 1;
        }

        return true;
    }

    /**
     * @param int $position
     * @param File $file
     * @param array $tokens
     * @param int $lineStart
     * @param int $lineEnd
     * @return bool
     */
    private function isLongHtmlAttribute(
        int $position,
        File $file,
        array $tokens,
        int $lineStart,
        int $lineEnd
    ): bool {

        $inPhp = false;
        $content = '';
        for ($i = $lineStart; $i <= $lineEnd; $i++) {
            $code = $tokens[$i]['code'];
            if (($code === T_OPEN_TAG || $code === T_OPEN_TAG_WITH_ECHO) && !$inPhp) {
                $inPhp = true;
            }
            if ($tokens[$i]['code'] === T_INLINE_HTML || $inPhp) {
                $tokenContent = $tokens[$i]['content'];
                $content .= $inPhp ? str_repeat('x', strlen($tokenContent)) : $tokenContent;
            }
            if ($tokens[$i]['code'] === T_CLOSE_TAG && $inPhp) {
                $inPhp = false;
            }
        }

        // Instead of counting single _word_ length we will count single _attribute_ length
        preg_match_all('~[^\s]+\s*=\s*["\'][^"\']*["\']~', $content, $matches);
        $attributesNumber = count($matches[0]);

        // When multiple HTML attributes are there, each attribute can go in a separate line
        if ($attributesNumber > 1) {
            return false;
        }

        // When a single HTML attribute is too long, we are not going to trigger warnings,
        // because we don't want to split one attribute in multiple lines
        if ($attributesNumber === 1) {
            return true;
        }

        // no HTML attributes found, let's use standard approach

        return $this->isLongSingleWord($position, $file, $tokens, $lineStart, $lineEnd);
    }

    /**
     * @param int $position
     * @param File $file
     * @param array $tokens
     * @param int $lineStart
     * @param int $lineEnd
     * @return bool
     */
    private function isLongSingleWord(
        int $position,
        File $file,
        array $tokens,
        int $lineStart,
        int $lineEnd
    ): bool {

        $words = preg_split('~\s+~', $tokens[$position]['content'], 2, PREG_SPLIT_NO_EMPTY);

        // If multiple words exceed line limit, we can split each word in its own line
        if ($words === false || count($words) !== 1) {
            return false;
        }

        $word = (string)reset($words);
        $firstNonWhite = $file->findNext(T_WHITESPACE, $lineStart, $lineEnd, true);
        $tolerance = is_array($firstNonWhite) ? (($firstNonWhite['column'] ?? 1) + 3) : 4;

        return (strlen($word) + $tolerance) > $this->lineLimit;
    }

    /**
     * We can't split text in WordPress translation functions in multiple lines, or WPCS will
     * complain because of PHP code used instead of just strings.
     * So when line limit exceed is caused by a long string passed as argument to a WP translation
     * function we don't show warnings for it.
     *
     * @param File $file
     * @param array $tokens
     * @param $start
     * @param $end
     * @return bool
     */
    private function isLongI10nFunction(File $file, array $tokens, $start, $end): bool
    {
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
     * With deep namespace structure and long namespace/class names it might happen that a line
     * of `use` statements becomes longer than limit.
     * We do not trigger a warning in that case because it is not possible to split the line.
     *
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
