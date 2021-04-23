<?php

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
     * @return array<int>
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function register()
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        return [T_OPEN_TAG];
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function process(File $file, $position)
    {
        // phpcs:enable Inpsyde.CodeQuality.ArgumentTypeDeclaration
        // phpcs:enable Inpsyde.CodeQuality.ReturnTypeDeclaration

        $longLinesData = $this->collectLongLinesData($file, max(1, $position));
        if (!$longLinesData) {
            return $file->numTokens + 1;
        }

        foreach ($longLinesData as $lineNum => list($length, $position)) {
            $file->addWarning(
                sprintf(
                    'Line %d exceeds %s characters; contains %s characters.',
                    $lineNum,
                    $this->lineLimit,
                    $length
                ),
                $position,
                'TooLong'
            );
        }
        // phpcs:enable

        return $file->numTokens + 1;
    }

    /**
     * @param File $file
     * @param int $start
     * @return array<int, array{int, int}>
     */
    private function collectLongLinesData(File $file, int $start): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        /** @var array<
         *  int,
         *  array{length:int, nonEmptyLength:int, start:int, end:int|null}
         * > $linesData
         */
        $linesData = [];
        /** @var int|null $lastLine */
        $lastLine = null;
        for ($i = $start; $i < $file->numTokens; $i++) {
            // Still processing previous line: increment length and continue.
            if ($lastLine && ($tokens[$i]['line'] === $lastLine)) {
                $content = (string)$tokens[$i]['content'];
                $linesData[$lastLine]['length'] += strlen($content);
                $linesData[$lastLine]['nonEmptyLength'] += strlen(trim($content));
                continue;
            }

            // A new line started: let's set "end" for the previous line (if this isn't 1st line)
            if ($lastLine && isset($linesData[$lastLine])) {
                $linesData[$lastLine]['end'] = $i - 1;
            }

            $lastLine = (int)$tokens[$i]['line'];
            $content = (string)$tokens[$i]['content'];
            $linesData[$lastLine] = [
                'length' => strlen($content),
                'nonEmptyLength' => strlen(trim($content)),
                'start' => $i,
                'end' => null,
            ];
        }

        // We still have to set the "end" for last file line.
        if ($lastLine && ($linesData[$lastLine]['end'] === null)) {
            $linesData[$lastLine]['end'] = $i - 1;
        }

        $longLines = [];
        foreach ($linesData as $lineNumber => $lineData) {
            $lineEnd = $lineData['end'] ?? $lineData['start'];
            if (
                (($lineData['length'] - $this->lineLimit) <= 1) // 1 char of tolerance
                || ($lineData['nonEmptyLength'] === 0) // ignore empty lines
                || $this->isLongUse($file, $tokens, $lineData['start'], $lineEnd)
                || $this->isLongI10nFunction($file, $tokens, $lineData['start'], $lineEnd)
                || $this->isLongWord($file, $tokens, $lineData['start'], $lineEnd)
            ) {
                continue;
            }

            $longLines[$lineNumber] = [$lineData['length'], $lineData['start']];
        }

        return $longLines;
    }

    /**
     * We don't want to split a single word in multiple lines.
     * So if there's a long word (e.g. an URL) that alone is above max line length, we don't show
     * warnings for it.
     *
     * @param File $file
     * @param array<int, array<string, mixed>> $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isLongWord(File $file, array $tokens, int $start, int $end): bool
    {
        $targetTypes = Tokens::$textStringTokens;
        $targetTypes[] = T_DOC_COMMENT_STRING;

        $foundString = false;

        while ($start && ($start <= $end)) {
            if (!in_array($tokens[$start]['code'], $targetTypes, true)) {
                $start++;
                continue;
            }

            if ($foundString) {
                return false;
            }

            $foundString = true;
            $isHtml = $tokens[$start]['code'] === T_INLINE_HTML;
            $isLong = $isHtml
                ? $this->isLongHtmlAttribute($start, $end, $file, $tokens)
                : $this->isLongSingleWord($start, $end, $file, $tokens);

            if (!$isLong || $isHtml) {
                return $isLong && $isHtml;
            }

            $start++;
        }

        return true;
    }

    /**
     * @param int $position
     * @param int $lineEnd
     * @param File $file
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     */
    private function isLongHtmlAttribute(
        int $position,
        int $lineEnd,
        File $file,
        array $tokens
    ): bool {

        $inPhp = false;
        $content = '';
        for ($i = $position; $i <= $lineEnd; $i++) {
            $code = $tokens[$i]['code'];
            if (($code === T_OPEN_TAG || $code === T_OPEN_TAG_WITH_ECHO) && !$inPhp) {
                $inPhp = true;
            }
            if ($tokens[$i]['code'] === T_INLINE_HTML || $inPhp) {
                $tokenContent = (string)$tokens[$i]['content'];
                $content .= $inPhp ? str_repeat('x', strlen($tokenContent)) : $tokenContent;
            }
            if ($tokens[$i]['code'] === T_CLOSE_TAG && $inPhp) {
                $inPhp = false;
            }
        }

        // Instead of counting single _word_ length we will count single _attribute_ length
        preg_match_all('~[^\s]+\s*=\s*["\'][^"\']*["\']~', $content, $matches);
        /** @var non-empty-array<int, array> $matches */
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

        return $this->isLongSingleWord($position, $lineEnd, $file, $tokens);
    }

    /**
     * @param int $position
     * @param int $lineEnd
     * @param File $file
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     */
    private function isLongSingleWord(
        int $position,
        int $lineEnd,
        File $file,
        array $tokens
    ): bool {

        $words = preg_split('~\s+~', (string)$tokens[$position]['content'], 2, PREG_SPLIT_NO_EMPTY);

        // If multiple words exceed line limit, we can split each word in its own line
        if ($words === false || count($words) !== 1) {
            return false;
        }

        $word = reset($words);
        $firstNonWhitePos = $file->findNext(T_WHITESPACE, $position, $lineEnd, true);
        $firstNonWhite = ($firstNonWhitePos === false) ? null : $tokens[$firstNonWhitePos];
        $tolerance = is_array($firstNonWhite) ? ((int)($firstNonWhite['column'] ?? 1) + 3) : 4;

        return (strlen($word) + $tolerance) > $this->lineLimit;
    }

    /**
     * We can't split text in WordPress translation functions in multiple lines, or WPCS will
     * complain because of PHP code used instead of just strings.
     * So when line limit exceed is caused by a long string passed as argument to a WP translation
     * function we don't show warnings for it.
     *
     * @param File $file
     * @param array<int, array<string, mixed>> $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isLongI10nFunction(File $file, array $tokens, int $start, int $end): bool
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

        $function = strtolower((string)$tokens[$functionPos]['content']);
        if (!in_array($function, self::I18N_FUNCTIONS, true)) {
            return false;
        }

        $close = $file->findEndOfStatement($open);

        $targetLine = $tokens[$stringPos]['line'];
        $textLen = 0;
        for ($i = $open + 1; $i < $close - 1; $i++) {
            if ($tokens[$i]['line'] !== $targetLine) {
                continue;
            }
            $textLen += max(1, strlen((string)$tokens[$i]['content']));
        }

        return ($textLen + 2) > $this->lineLimit;
    }

    /**
     * With deep namespace structure and long namespace/class names it might happen that a line
     * of `use` statements becomes longer than limit.
     * We do not trigger a warning in that case because it is not possible to split the line.
     *
     * @param File $file
     * @param array<int, array<string, mixed>> $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isLongUse(File $file, array $tokens, int $start, int $end): bool
    {
        $usePos = $file->findNext(T_USE, $start, $end);
        if ($usePos === false) {
            return false;
        }

        $endUse = $file->findEndOfStatement($usePos);
        $useLen = 0;
        for ($i = $usePos; $i <= $endUse; $i++) {
            $useLen += strlen((string)$tokens[$i]['content']);
        }

        return $useLen > $this->lineLimit;
    }
}
