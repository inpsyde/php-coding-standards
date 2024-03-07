<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests;

use RuntimeException;

class E2eTest extends TestCase
{
    private string $testPackagePath = '';
    private string $phpCsBinary = '';

    protected function setUp(): void
    {
        $libPath = (string) getenv('LIB_PATH');
        $this->testPackagePath = $libPath . '/tests/e2e/test-package';
        $this->phpCsBinary = $libPath . '/vendor/bin/phpcs';
    }

    public function testInpsydeAndTemplatesRulesets(): void
    {
        $output = [];
        exec(
            sprintf(
                'cd %s && %s',
                $this->testPackagePath,
                $this->phpCsBinary
            ),
            $output
        );

        /** @var array<string> $output> */
        if ($output[0] !== 'EE 2 / 2 (100%)') {
            throw new RuntimeException(implode("\n", $output));
        }

        $json = end($output);

        self::assertSame([
            'index.php' => [
                [
                    'source' => 'Inpsyde.CodeQuality.NoElse.ElseFound',
                    'line' => 12,
                ],
                [
                    'source' => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
                    'line' => 13,
                ],
            ],
            'template.php' => [

                [
                    'source' => 'WordPress.Security.EscapeOutput.OutputNotEscaped',
                    'line' => 12,
                ],
                [
                    'source' => 'InpsydeTemplates.Formatting.TrailingSemicolon.Found',
                    'line' => 12,
                ],
                [
                    'source' => 'Inpsyde.CodeQuality.DisableCallUserFunc.call_user_func_call_user_func',
                    'line' => 15,
                ],

            ],
        ], $this->phpCsNormalizedOutput($json));
    }

    /**
     * @psalm-return array<string, list<array{source: string, line: positive-int}>>
     */
    private function phpCsNormalizedOutput(string $json): array
    {
        /** @var array{
         *     files: array<string, array{
         *         messages: list<array{
         *             source: string,
         *             line: positive-int,
         *             ...
         *             }>
         *        }>
         *     } $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $result = [];
        foreach ($data['files'] as $fileName => $fileData) {
            $baseName = basename($fileName);
            $result[$baseName] = [];
            foreach ($fileData['messages'] as ['source' => $source, 'line' => $line]) {
                $result[$baseName][] = ['source' => $source, 'line' => $line];
            }
        }

        return $result;
    }
}
