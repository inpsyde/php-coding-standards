<?php

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
