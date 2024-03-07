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
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
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

        // phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
        $expectedMessages = [
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
        ];
        // phpcs:enable Inpsyde.CodeQuality.LineLength.TooLong

        self::assertSame($expectedMessages, $this->phpCsMessages($json));
    }

    /**
     * @psalm-return array<string, list<array{source: string, line: positive-int}>>
     */
    private function phpCsMessages(string $json): array
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
