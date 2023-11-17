<?php

/*
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (c) 2023 Inpsyde GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests;

use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

class FixturesTest extends TestCase
{
    /**
     * @return \Generator
     */
    public static function fixtureProvider(): \Generator
    {
        foreach ((glob(getenv('FIXTURES_PATH') . '/*.php') ?: []) as $fixtureFile) {
            $name = pathinfo($fixtureFile, PATHINFO_FILENAME);
            yield $name => [pathinfo($fixtureFile, PATHINFO_BASENAME)];
        }
    }

    /**
     * @test
     * @dataProvider fixtureProvider
     */
    public function testAllFixtures(string $fixtureFile): void
    {
        $parser = new FixtureContentParser();
        $failures = new \SplStack();
        $this->validateFixture(getenv('FIXTURES_PATH') . "/{$fixtureFile}", $parser, $failures);

        $previous = null;
        foreach ($failures as $failure) {
            if (!$failure instanceof \Throwable) {
                continue;
            }

            $previous = new AssertionFailedError(
                $failure->getMessage(),
                $failure->getCode(),
                $previous
            );
        }
        if ($previous) {
            throw $previous;
        }
    }

    /**
     * @param string $fixtureFile
     * @param FixtureContentParser $parser
     * @param \SplStack $failures
     */
    private function validateFixture(
        string $fixtureFile,
        FixtureContentParser $parser,
        \SplStack $failures
    ): void {

        $fixtureBasename = basename($fixtureFile);

        try {
            /**
             * @var string $sniffClass
             * @var SniffMessages $expected
             * @var array $properties
             */
            [$sniffClass, $expected, $properties] = $parser->parse($fixtureFile);

            $file = $this->createPhpcsForFixture($sniffClass, $fixtureFile, $properties);
            $actual = (new SniffMessagesExtractor($file))->extractMessages();
        } catch (\Throwable $throwable) {
            $failures->push($throwable);
            return;
        }

        $this->validateCodes($expected, $actual, $fixtureBasename, $sniffClass);
        $this->validateTotals($expected, $actual, $fixtureBasename, $sniffClass);
    }

    /**
     * @param SniffMessages $expected
     * @param SniffMessages $actual
     * @param string $fixture
     * @param string $sniffClass
     */
    private function validateCodes(
        SniffMessages $expected,
        SniffMessages $actual,
        string $fixture,
        string $sniffClass
    ): void {

        $where = sprintf("\nin fixture file '%s' line %%d\nfor sniff '%s'", $fixture, $sniffClass);

        foreach ($expected->messages() as $line => $code) {
            $actualCode = $actual->messageIn($line);
            $this->validateCode('message', $code, sprintf($where, $line), $actualCode);
        }

        foreach ($expected->warnings() as $line => $code) {
            $actualCode = $actual->warningIn($line);
            $this->validateCode('warning', $code, sprintf($where, $line), $actualCode);
        }

        foreach ($expected->errors() as $line => $code) {
            $actualCode = $actual->errorIn($line);
            $this->validateCode('error', $code, sprintf($where, $line), $actualCode);
        }
    }

    /**
     * @param string $type
     * @param string $code
     * @param string $where
     * @param string|null $actualCode
     */
    private function validateCode(
        string $type,
        string $code,
        string $where,
        string $actualCode = null
    ): void {

        $message = $code
            ? sprintf('Expected %s code \'%s\' was not found', $type, $code)
            : sprintf('Expected %s was not found', $type);

        $code === ''
            ? static::assertNotNull($actualCode, "{$message} {$where}.")
            : static::assertSame($code, $actualCode, "{$message} {$where}.");
    }

    /**
     * @param SniffMessages $expected
     * @param SniffMessages $actual
     * @param string $fixtureFile
     * @param string $sniffClass
     */
    private function validateTotals(
        SniffMessages $expected,
        SniffMessages $actual,
        string $fixtureFile,
        string $sniffClass
    ): void {

        $expectedTotal = $expected->total();
        $actualTotal = $actual->total();
        $unexpected = array_diff($actual->messageLines(), $expected->messageLines());
        $notRaised = array_diff($expected->messageLines(), $actual->messageLines());
        $mismatch = array_unique(array_merge($unexpected, $notRaised));

        self::assertSame(
            $expectedTotal,
            $actualTotal,
            sprintf(
                'Fixture \'%s\', for sniff \'%s\', expected a total of %d messages, '
                . 'but actually a total of %d messages found.'
                . ' (mismatch found at %s %s)',
                $fixtureFile,
                $sniffClass,
                $expectedTotal,
                $actualTotal,
                count($mismatch) === 1 ? 'line' : 'lines:',
                implode(', ', $mismatch)
            )
        );
    }

    /**
     * @param string $sniffName
     * @param string $fixtureFile
     * @param array $properties
     * @return File
     */
    private function createPhpcsForFixture(
        string $sniffName,
        string $fixtureFile,
        array $properties
    ): File {

        $sniffFile = str_replace('.', '/', "{$sniffName}Sniff");
        $sniffPath = getenv('SNIFFS_PATH') . "/{$sniffFile}.php";
        if (!file_exists($sniffPath) || !is_readable($sniffPath)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            throw new Exception("Non-existent of unreadable sniff file '{$sniffPath}' found.");
        }

        $config = new Config();
        $config->standards = [dirname(getenv('SNIFFS_PATH'))];
        $config->sniffs = ["Inpsyde.{$sniffName}"];
        $ruleset = new Ruleset($config);

        $baseSniffNamespace = getenv('SNIFFS_NAMESPACE');
        $sniffFqn = str_replace('/', '\\', $sniffFile);
        foreach ($properties as $name => $value) {
            $ruleset->setSniffProperty("{$baseSniffNamespace}\\{$sniffFqn}", $name, $value);
        }

        return new LocalFile($fixtureFile, $ruleset, $config);
    }
}
