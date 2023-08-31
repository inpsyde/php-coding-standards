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

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\Helpers\Misc;
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
