<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\InpsydeCodingStandard\Tests;

use PHPUnit\Framework\Exception;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class FixtureContentParser
{
    const TOKEN_SNIFF = '@phpcsSniff';
    const TOKEN_PROCESS_START = '@phpcsProcessFixtureStart';
    const TOKEN_PROCESS_END = '@phpcsProcessFixtureEnd';
    const TOKEN_PROPERTIES_START = '@phpcsSniffPropertiesStart';
    const TOKEN_PROPERTIES_END = '@phpcsSniffPropertiesEnd';

    /**
     * @param string $fixturePath
     * @return array
     */
    public function parse(string $fixturePath): array
    {
        if (!file_exists($fixturePath) || !is_readable($fixturePath)) {
            throw new Exception("Fixture file {$fixturePath} is not readable.");
        }

        $accumulator = (object)[
            'sniff' => null,
            'warnings' => [],
            'errors' => [],
            'messages' => [],
            'properties' => (object)[
                'start' => false,
                'end' => false,
                'values' => [],
            ],
            'process' => (object)[
                'start' => false,
                'end' => false,
                'content' => '',
            ],
        ];

        // phpcs:disable VariableAnalysis
        foreach ($this->readFile($fixturePath) as list($lineNum, $line)) {
            $this->readLine($lineNum, $line, $accumulator);
        }
        // phpcs:enable

        return $this->processResults($accumulator, $fixturePath);
    }

    /**
     * @param \stdClass $accumulator
     * @param string $fixturePath
     * @return array
     */
    private function processResults(\stdClass $accumulator, string $fixturePath): array
    {
        $results = [
            $accumulator->sniff,
            $accumulator->messages,
            $accumulator->warnings,
            $accumulator->errors,
            $accumulator->properties->values,
        ];

        if (!$accumulator->process->content) {
            return [
                $this->checkSniffName(array_shift($results)),
                new SniffMessages($results[1], $results[2], $results[0]),
                $accumulator->properties->values
            ];
        }

        // phpcs:disable
        eval("\$cb = {$accumulator->process->content};");
        /** @var callable $cb */
        $results = $cb(...$results);
        // phpcs:enable

        if ($accumulator->process->content
            && !is_array($results)
            || count($results) !== 5
            || !is_string($results[0] ?? null)
            || !is_array($results[1] ?? null)
            || !is_array($results[2] ?? null)
            || !is_array($results[3] ?? null)
            || !is_array($results[4] ?? null)
        ) {
            throw new Exception(
                sprintf(
                    "Process callback for fixture '%s' (lines #%s:#%s) returned invalid output.",
                    $fixturePath,
                    $accumulator->process->start,
                    $accumulator->process->end
                )
            );
        }

        return [
            $this->checkSniffName(array_shift($results)),
            new SniffMessages($results[1], $results[2], $results[0]),
            $results[3]
        ];
    }

    /**
     * @param string|null $sniff
     * @return string
     */
    private function checkSniffName(string $sniff = null): string
    {
        if ($sniff === null) {
            throw new Exception("No sniff class found for the test.");
        }

        static $regex;
        if (!$regex) {
            $chars = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
            $regex = "({$chars})(({$chars}|\.)*({$chars}))";
        }

        if (!preg_match('~^' . $regex . '$~', $sniff)) {
            throw new Exception("Invalid sniff name '{$sniff}'.");
        }

        return $sniff;
    }

    /**
     * @param string $file
     * @return \Generator
     */
    private function readFile(string $file)
    {
        $handle = fopen($file, 'rb');
        $lineNum = 1;

        while (($line = fgets($handle)) !== false) {
            yield [$lineNum++, rtrim($line, "\r\n")];
        }

        fclose($handle);
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param \stdClass $accumulator
     */
    private function readLine(int $lineNum, string $line, \stdClass $accumulator)
    {
        if (!$this->readProcessLine($lineNum, $line, $accumulator)
            && !$this->readSniffLine($line, $accumulator)
            && !$this->readPropertiesLine($lineNum, $line, $accumulator)
        ) {
            $this->readTokenLine($lineNum, $line, $accumulator);
        }
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param \stdClass $accumulator
     * @return bool
     */
    private function readProcessLine(int $lineNum, string $line, \stdClass $accumulator): bool
    {
        if ($accumulator->process->end !== false) {
            return false;
        }

        if (substr_count($line, self::TOKEN_PROCESS_END)) {
            $accumulator->process->end = $lineNum;
            return true;
        }

        if ($accumulator->process->start !== false) {
            $accumulator->process->content .= $line . PHP_EOL;
            return true;
        }

        if (substr_count($line, self::TOKEN_PROCESS_START)) {
            $accumulator->process->start = $lineNum;
            return true;
        }

        return false;
    }

    /**
     * @param string $line
     * @param \stdClass $accumulator
     * @return bool
     */
    private function readSniffLine(string $line, \stdClass $accumulator): bool
    {
        if ($accumulator->sniff) {
            return false;
        }

        static $regex;
        $regex or $regex = '~' . preg_quote(self::TOKEN_SNIFF, '~') . '\s+([^\s]+)~';

        preg_match($regex, $line, $matches);
        if (!empty($matches[1])) {
            $accumulator->sniff = $matches[1];

            return true;
        }

        return false;
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param \stdClass $accumulator
     * @return bool
     */
    private function readPropertiesLine(int $lineNum, string $line, \stdClass $accumulator): bool
    {
        static $pattern;
        $pattern or $pattern = '~\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=\s*([^;]+);~';

        if ($accumulator->properties->end !== false) {
            return false;
        }

        if (substr_count($line, self::TOKEN_PROPERTIES_END)) {
            $accumulator->properties->end = $lineNum;
            return true;
        }

        if ($accumulator->properties->start !== false && preg_match($pattern, $line, $matches)) {
            // phpcs:disable
            /** @var mixed $value */
            eval('$value = ' . $matches[2] . ';');
            $accumulator->properties->values[$matches[1]] = $value;
            // phpcs:enable
        }

        if (substr_count($line, self::TOKEN_PROPERTIES_START)) {
            $accumulator->properties->start = $lineNum;
            return true;
        }

        return false;
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param \stdClass $accumulator
     */
    private function readTokenLine(int $lineNum, string $line, \stdClass $accumulator)
    {
        static $pattern;
        if (!$pattern) {
            $typePattern = '(?<type>Warning|Error|Message)';
            $hasCodePattern = '(?<has_code>Code)?';

            $linePattern = 'On(?<line>This|Next|Previous)Line';
            $codePattern = '(?<code>\s+[^\s]+)?';
            $pattern = '@phpcs' . $typePattern . $hasCodePattern . $linePattern . $codePattern;
        }

        preg_match("~{$pattern}~", $line, $matches);
        if (!$matches) {
            return;
        }

        $prop = 'messages';
        if ($matches['type'] !== 'Message') {
            $prop = $matches['type'] === 'Warning' ? 'warnings' : 'errors';
        }

        $increment = 0;
        if ($matches['line'] !== 'This') {
            $increment = $matches['line'] === 'Next' ? 1 : -1;
        }

        $code = true;
        if (!empty($matches['has_code']) && !empty($matches['code'])) {
            $matchedCode = trim($matches['code']);
            $matchedCode and $code = $matchedCode;
        }

        $accumulator->{$prop}[$lineNum + $increment] = $code;
    }
}
