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
use Inpsyde\CodingStandard\Helpers\Objects;

class ObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function testAllInterfacesFullyQualifiedNames(): void
    {
        $php = <<<'PHP'
<?php
namespace Foo;

use X\Partial as Aliased;
use Y\Full;

function () use ($test) {
    return $test;
}

class Test1 implements Bar, \X, \Y\Y, Aliased\Sub, Full
{
}

class Test2 implements Bar, \X, \Y\Y, Aliased\Sub, Full extends \Y, Z
{
}
PHP;
        $file = $this->factoryFile($php);
        $class1 = $file->findNext(T_CLASS, 0);
        $class2 = $file->findNext(T_CLASS, $class1 + 1);

        $names1 = Objects::allInterfacesFullyQualifiedNames($file, $class1);
        $names2 = Objects::allInterfacesFullyQualifiedNames($file, $class2);

        static::assertSame(
            ['\\Foo\\Bar', '\\X', '\Y\Y', '\\X\\Partial\\Sub', '\\Y\\Full'],
            $names1
        );

        static::assertSame(
            ['\\Foo\\Bar', '\\X', '\Y\Y', '\\X\\Partial\\Sub', '\\Y\\Full'],
            $names2
        );
    }

    /**
     * @test
     */
    public function testCountProperties(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    private readonly Test $var1;
    static private string $var2;
    public static $var3;
    static int $var4;
    var $var5;
    
    function foo($foo, int $bar) {
        $this->var1 = $bar;
        
        return new class() {
            static private $x1;
            public static $x2;
            static $x3;
            var $x4;
        };
    }
    
    static private readonly Test $var6;
    
    function foo($foo, int $bar) {
        var $x4;
    }
    
    var $var7;
}
PHP;

        $file = $this->factoryFile($php);

        $classPos = $file->findNext(T_CLASS, 1);
        static::assertSame(7, Objects::countProperties($file, $classPos));
    }
}
