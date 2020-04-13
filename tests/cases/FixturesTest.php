<?php

declare(strict_types=1);

# -*- coding: utf-8 -*-
/*
 * This file is part of the php-coding-standards package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\CodingStandard\Tests\Cases;

use Inpsyde\CodingStandard\Tests\FixtureContentParser;
use Inpsyde\CodingStandard\Tests\SniffMessages;
use Inpsyde\CodingStandard\Tests\SniffMessagesExtractor;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @package php-coding-standards
 * @license http://opensource.org/licenses/MIT MIT
 */
class FixturesTest extends TestCase
{

    public function fixtureProvider(): \Traversable
    {
        $fixtures = glob(getenv('FIXTURES_PATH').DIRECTORY_SEPARATOR.'*.php');
        foreach ($fixtures as $fixtureFile) {
            $testname = pathinfo($fixtureFile, PATHINFO_BASENAME);
            yield $testname => ['fixtrueFile' => $fixtureFile];
        }
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testAllFixtures(string $fixtureFile)
    {
        $parser = new FixtureContentParser();
        $failures = new \SplStack();
        $this->validateFixture($fixtureFile, $parser, $failures);

        $previous = null;
        /** @var \Throwable $failure */
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
    ) {

        $fixtureBasename = basename($fixtureFile);
        fwrite(STDOUT, "- Testing fixture '{$fixtureBasename}'...\n");

        try {
            /**
             * @var string $sniffClass
             * @var SniffMessages $expected
             * @var array $properties
             */
            list($sniffClass, $expected, $properties) = $parser->parse($fixtureFile);

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
    ) {

        $where = sprintf("in fixture file '%s', line %%d, for sniff '%s'", $fixture, $sniffClass);

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
     * @param $code
     * @param string $where
     * @param string|null $actualCode
     */
    private function validateCode(
        string $type,
        $code,
        string $where,
        string $actualCode = null
    ) {

        $message = is_string($code)
            ? sprintf('Expected %s code \'%s\' was not found', $type, $code)
            : sprintf('Expected %s was not found', $type);

        $code === true
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
    ) {

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
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     * @throws \ReflectionException
     */
    private function createPhpcsForFixture(
        string $sniffName,
        string $fixtureFile,
        array $properties
    ): File {

        $sniffName = str_replace('.', DIRECTORY_SEPARATOR, $sniffName) . 'Sniff';
        $sniffFile = getenv('SNIFFS_PATH') . DIRECTORY_SEPARATOR . "{$sniffName}.php";
        if (!file_exists($sniffFile) || !is_readable($sniffFile)) {
            throw new Exception("Non-existent of unreadable sniff file '$sniffFile' found.");
        }

        $config = new Config();
        $config->standards = [];
        /** @var Ruleset $ruleset */
        $ruleset = (new \ReflectionClass(Ruleset::class))->newInstanceWithoutConstructor();
        $ruleset->registerSniffs([$sniffFile], [], []);
        $ruleset->populateTokenListeners();

        $baseSniffNamespace = getenv('SNIFFS_NAMESPACE');
        $sniffFqn = str_replace(DIRECTORY_SEPARATOR, '\\', $sniffName);
        foreach ($properties as $name => $value) {
            $ruleset->setSniffProperty("{$baseSniffNamespace}\\{$sniffFqn}", $name, $value);
        }

        return new LocalFile($fixtureFile, $ruleset, $config);
    }
}
