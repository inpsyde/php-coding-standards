<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\CodingStandard\Helpers\Misc;
use PHP_CodeSniffer\Util\Tokens;

class MiscTest extends TestCase
{
    /**
     * @return list{string, string}
     */
    public static function provideMinVersions(): array
    {
        return [
            ['8.0', '8.0'],
            ['8.1', '8.1'],
            ['8.2.3', '8.2'],
            ['7.2.3', '7.4'],
            ['7', '7.4'],
            ['8', '8.0'],
            ['5.6', '7.4'],
        ];
    }

    /**
     * @test
     * @dataProvider provideMinVersions
     * @runInSeparateProcess
     */
    public function testMinPhpTestVersion(string $input, string $expected): void
    {
        $this->factoryFile('<?php ', $input);
        static::assertSame($expected, Misc::minPhpTestVersion());
    }

    /**
     * @test
     */
    public function tokensSubsetToString(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    return ("foo + bar");
}
PHP;
        $file = $this->factoryFile($php);

        $tokens = $file->getTokens();
        $start = $file->findNext(T_FUNCTION, 1);
        $end = count($tokens) - 1;

        $actual = Misc::tokensSubsetToString($start, $end, $file, [], true);
        $expected = <<<'PHP'
function x(): string {
    return ("foo + bar");
}
PHP;
        static::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function tokensSubsetToStringExclude(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    /** foo */
    return ("foo + bar");
}
PHP;
        $file = $this->factoryFile($php);

        $tokens = $file->getTokens();
        $start = $file->findNext(T_FUNCTION, 1);
        $end = count($tokens) - 1;
        $exclude = array_keys(Tokens::$emptyTokens);
        $exclude[] = T_RETURN;

        $actual = Misc::tokensSubsetToString($start + 1, $end, $file, $exclude, true);
        $expected = 'x():string{("foo + bar");}';

        static::assertSame($expected, $actual);
    }
}
