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
use Inpsyde\Helpers\Functions;

class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function testLooksLikeFunctionCall(): void
    {
        $php = <<<'PHP'
<?php
function x(): string {
    return ("foo + bar");
}
class Test {
    function x(): string {
        // one: `x()`
        return x();
    }
    
    function y(): callable {
        return function () {
            // two: `sprintf()`
            return sprintf /* comment is valid before parenthesis */(
                "foo %s bar",
                '(+)'
            );
        };
    }
}

("foo + bar");
// three: `x()`
echo (new Test())->x();
// four: `y()`
$y = (new Test())->y();
// five: `$y`
$y();

// six: `require`
require('foo.php');

PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $functionCallContents = [];
        foreach ($tokens as $pos => $token) {
            if (Functions::looksLikeFunctionCall($file, $pos)) {
                $functionCallContents[] = $token['content'];
            }
        }

        static::assertSame(['x', 'sprintf', 'x', 'y', '$y', 'require'], $functionCallContents);
    }

    /**
     * @test
     */
    public function testIsUntypedPsrMethodWithClass(): void
    {
        $php = <<<'PHP'
<?php
use \Psr\Container\ContainerInterface;

class Container implements Foo, Bar\X, ContainerInterface {

    private $data = [];

    public function get($id)
    {
        return $this->data[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->data[$id]);
    }
}
PHP;
        $file = $this->factoryFile($php);

        $getFunc = $file->findNext(T_FUNCTION, 1);
        $hasFunc = $file->findNext(T_FUNCTION, $getFunc + 2);

        static::assertSame('get', $file->getDeclarationName($getFunc));
        static::assertSame('has', $file->getDeclarationName($hasFunc));

        $isPsrGet = Functions::isPsrMethod($file, $getFunc);
        $isPsrHas = Functions::isPsrMethod($file, $hasFunc);

        static::assertTrue($isPsrGet);
        static::assertTrue($isPsrHas);
    }

    /**
     * @test
     */
    public function testIsUntypedPsrMethodWithAnonClass(): void
    {
        $php = <<<'PHP'
<?php
namespace Test;

use Psr\Container\ContainerInterface as PsrContainer;

$x = new class implements Foo, PsrContainer, Bar {

    private $data = [];

    public function get($id)
    {
        return $this->data[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->data[$id]);
    }
};
PHP;
        $file = $this->factoryFile($php);

        $getFunc = $file->findNext(T_FUNCTION, 1);
        $hasFunc = $file->findNext(T_FUNCTION, $getFunc + 1);

        static::assertSame('get', $file->getDeclarationName($getFunc));
        static::assertSame('has', $file->getDeclarationName($hasFunc));

        $isPsrGet = Functions::isPsrMethod($file, $getFunc);
        $isPsrHas = Functions::isPsrMethod($file, $hasFunc);

        static::assertTrue($isPsrGet);
        static::assertTrue($isPsrHas);
    }
}
