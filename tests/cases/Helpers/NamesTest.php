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
use Inpsyde\Helpers\Names;

class NamesTest extends TestCase
{
    /**
     * @test
     */
    public function testNameableTokenName(): void
    {
        $php = <<<'PHP'
<?php
namespace {

    interface a {
        public static function b(): string;
    }
    function c(string $d): string {
        return '';
    }
    abstract class e implements a {
        const f = 'f';
        function g(): string {
            return c($h = 'h');
        }
    }
    trait i {
        public function j() {
        }
    }
    class k {
        use i;
        function l() {
        }
    }
    enum m {
        case n;
        case o;
    }
    enum p {
        case q = 'q';
        case r = 'r';
    }
    ((new k())->l());
    $s = 's';
}

namespace t {

}
PHP;
        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $names = [];
        foreach ($tokens as $pos => $token) {
            $name = Names::nameableTokenName($file, $pos);
            $name and $names[] = $name;
        }

        static::assertSame(range('a', 't'), $names);
    }
}
