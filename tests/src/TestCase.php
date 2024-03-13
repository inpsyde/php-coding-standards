<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $content
     * @param string|null $minTestVersion
     * @return File
     * @throws \ReflectionException
     */
    protected function factoryFile(string $content, ?string $minTestVersion = null): File
    {
        $args = ($minTestVersion === null)
            ? []
            : ['--runtime-set', 'testVersion', "{$minTestVersion}-"];

        $config = new Config($args, false);
        $config->standards = [];
        $config->extensions = ['php' => 'PHP'];
        $config->setCommandLineValues([]);
        /** @var Ruleset $ruleset */
        $ruleset = (new \ReflectionClass(Ruleset::class))->newInstanceWithoutConstructor();

        $file = new DummyFile($content, $ruleset, $config);
        $file->parse();

        return $file;
    }
}
