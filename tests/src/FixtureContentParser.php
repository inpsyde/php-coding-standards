<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests;

class FixtureContentParser
{
    public const TOKEN_SNIFF = '@phpcsSniff';
    public const TOKEN_PROCESS_START = '@phpcsProcessFixtureStart';
    public const TOKEN_PROCESS_END = '@phpcsProcessFixtureEnd';
    public const TOKEN_PROPERTIES_START = '@phpcsSniffPropertiesStart';
    public const TOKEN_PROPERTIES_END = '@phpcsSniffPropertiesEnd';

    /**
     * @param string $fixturePath
     * @return array
     */
    public function parse(string $fixturePath): array
    {
        if (!file_exists($fixturePath) || !is_readable($fixturePath)) {
            throw new \Error("Fixture file {$fixturePath} is not readable.");
        }

        $accumulator = (object) [
            'sniff' => null,
            'warnings' => [],
            'errors' => [],
            'messages' => [],
            'properties' => (object) [
                'start' => false,
                'end' => false,
                'values' => [],
            ],
            'process' => (object) [
                'start' => false,
                'end' => false,
                'content' => '',
            ],
        ];

        foreach ($this->readFile($fixturePath) as $lineNum => $line) {
            $this->readLine($lineNum, $line, $accumulator);
        }

        return $this->processResults($accumulator, $fixturePath);
    }

    /**
     * @param object $accumulator
     * @param string $fixturePath
     * @return array
     */
    private function processResults(object $accumulator, string $fixturePath): array
    {
        if (!$accumulator->process->content) {
            return [
                $this->checkSniffName($accumulator->sniff),
                new SniffMessages(
                    $accumulator->warnings,
                    $accumulator->errors,
                    $accumulator->messages
                ),
                $accumulator->properties->values,
            ];
        }

        // phpcs:disable
        eval("\$cb = {$accumulator->process->content};");
        $params = [
            $accumulator->sniff,
            $accumulator->messages,
            $accumulator->warnings,
            $accumulator->errors,
            $accumulator->properties->values,
        ];
        /** @var mixed $cb */
        $results = is_callable($cb) ? $cb(...$params) : null;
        // phpcs:enable

        $results = array_values(array_pad(is_array($results) ? $results : [], 5, null));
        [$sniff, $messages, $warnings, $errors, $properties] = $results;

        if (
            !is_string($sniff)
            || !is_array($messages)
            || !is_array($warnings)
            || !is_array($errors)
            || !is_array($properties)
        ) {
            throw new \Error(
                sprintf(
                    "Process callback for fixture '%s' (lines #%s:#%s) returned invalid output.",
                    $fixturePath,
                    $accumulator->process->start,
                    $accumulator->process->end
                )
            );
        }

        return [
            $this->checkSniffName($sniff),
            new SniffMessages($warnings, $errors, $messages),
            $properties,
        ];
    }

    /**
     * @param string|null $sniff
     * @return string
     */
    private function checkSniffName(?string $sniff): string
    {
        if ($sniff === null) {
            throw new \Error("No sniff class found for the test.");
        }

        static $regex;
        if (!$regex) {
            $chars = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
            $regex = "({$chars})(({$chars}|\.)*({$chars}))";
        }

        if (!preg_match('~^' . $regex . '$~', $sniff)) {
            throw new \Error("Invalid sniff name '{$sniff}'.");
        }

        return $sniff;
    }

    /**
     * @param string $file
     * @return \Generator
     */
    private function readFile(string $file): \Generator
    {
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            throw new \Error("Could not open '{$file}' for reading.");
        }

        $lineNum = 1;

        $line = fgets($handle);
        while ($line !== false) {
            yield $lineNum++ => rtrim($line, "\r\n");
            $line = fgets($handle);
        }

        fclose($handle);
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param object $accumulator
     */
    private function readLine(int $lineNum, string $line, object $accumulator): void
    {
        if (
            !$this->readProcessLine($lineNum, $line, $accumulator)
            && !$this->readSniffLine($line, $accumulator)
            && !$this->readPropertiesLine($lineNum, $line, $accumulator)
        ) {
            $this->readTokenLine($lineNum, $line, $accumulator);
        }
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param object $accumulator
     * @return bool
     */
    private function readProcessLine(int $lineNum, string $line, object $accumulator): bool
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
     * @param object $accumulator
     * @return bool
     */
    private function readSniffLine(string $line, object $accumulator): bool
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
     * @param object $accumulator
     * @return bool
     */
    private function readPropertiesLine(int $lineNum, string $line, object $accumulator): bool
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
     * @param object $accumulator
     */
    private function readTokenLine(int $lineNum, string $line, object $accumulator): void
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

        $code = '';
        if (!empty($matches['has_code']) && !empty($matches['code'])) {
            $matchedCode = trim($matches['code']);
            $matchedCode and $code = $matchedCode;
        }

        $accumulator->{$prop}[$lineNum + $increment] = $code;
    }
}
