<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests;

use Inpsyde\PhpcsHelpers;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;
use PHP_CodeSniffer\Files\File;

class PhpcsHelpersTest extends TestCase
{
    /**
     * @test
     */
    public function testClassProperties()
    {
        $php = <<<'PHP'
<?php
class Test {
    private $var1;
    static private $var2;
    public static $var3;
    static $var4;
    var $var5;
    
    function foo($foo, int $bar) {
        $this->var1 = $bar;
        
        return new class() {
            static private $var7;
            public static $var8;
            static $var9;
            var $var10;
        };
    }
}
PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $classPos = $file->findNext(T_CLASS, 1);
        $classLine = $tokens[$classPos]['line'];
        $list = PhpcsHelpers::allPropertiesTokenPositions($file, $classPos);

        $actualNames = [];
        $propLine = $classLine;
        foreach ($list as $propPos) {
            $propLine++;
            $actualNames[$propLine] = $tokens[$propPos]['content'];
        }

        $expectedNames = [
            ($classLine + 1) => '$var1',
            ($classLine + 2) => '$var2',
            ($classLine + 3) => '$var3',
            ($classLine + 4) => '$var4',
            ($classLine + 5) => '$var5',
        ];

        static::assertTrue(is_int($classPos) && $classPos > 0);
        static::assertSame('Test', $file->getDeclarationName($classPos));
        static::assertSame($expectedNames, $actualNames);
    }

    /**
     * @test
     */
    public function testClassMethods()
    {
        $php = <<<'PHP'
<?php
trait One {
    public $var1;
    private function methodOne($foo, int $bar) {
        $this->var1 = $bar;
    }
}
class Two {
   use One;
    private function methodTwo($foo, int $bar) {
        return new class() {
            public function methodThree() {
                return function() {
                    return function() {
                    };
                };
            }
        };
    }
}
function test() {
    return function () {
        return function() {
           return new Two();
        };
    };
}
function testTest() {
    return (test()()())->var1;
}
PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $methodNames = [];
        foreach ($tokens as $pos => $token) {
            if (PhpcsHelpers::functionIsMethod($file, $pos)) {
                $methodNames[] = $file->getDeclarationName($pos);
            }
        }

        static::assertSame(['methodOne', 'methodTwo', 'methodThree'], $methodNames);
    }

    /**
     * @test
     */
    public function testFunctionCall()
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

PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $functionCallContents = [];
        foreach ($tokens as $pos => $token) {
            if (PhpcsHelpers::looksLikeFunctionCall($file, $pos)) {
                $functionCallContents[] = $tokens[$pos]['content'];
            }
        }

        static::assertSame(['x', 'sprintf', 'x', 'y', '$y'], $functionCallContents);
    }

    /**
     * @test
     */
    public function testTokenName()
    {
        $php = <<<'PHP'
<?php
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
((new k())->l());
PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $names = [];
        foreach ($tokens as $pos => $token) {
            $name = PhpcsHelpers::tokenName($file, $pos);
            $name and $names[] = $name;
        }

        static::assertSame(range('a', 'l'), $names);
    }

    /**
     * @test
     */
    public function testHookClosure()
    {
        $php = <<<'PHP'
<?php

add_action('x', '__return_false');

function theHookPrefix() {
    return 'x_';
}

add_action /* x */ (theHookPrefix() . 'xx', 
    static
    function /* add_action('x', function () {}) */
    () {
        return 'find me!';
    }
);

function add_action($x, $y) {
    return function () {
        return function() {
            add_action('x', 'theHookPrefix');
        };
    };
}
PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $bodies = [];
        foreach ($tokens as $pos => $token) {
            $isHookClosure = PhpcsHelpers::isHookClosure($file, $pos);
            $isHookClosure and $bodies[] = trim(PhpcsHelpers::functionBody($file, $pos));
        }

        static::assertSame(["return 'find me!';"], $bodies);
    }

    /**
     * @test
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     */
    public function testReturnCount()
    {
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        /** @noinspection PhpInconsistentReturnPointsInspection */
        $php = <<<'PHP'
<?php
class Test {
    public function countInfo($x) {
        if ($x === 'void') {
            return;
        }
        
        if ($x === 'null') {
            return null;
        }
        
        $cb =  function ($x) {
            if ($x === 'void') {
                return;
            }
            
            return new class () {
                public function count($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
        };
        
        if (!$cb(1)) {
            $n = new class () {
                public function something($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
            
            return $n->something(1);
        }
        
        return true;
    }
}
PHP;

        $file = $this->factoryFile($php);

        $functionPos = $file->findNext(T_FUNCTION, 1);
        $info = PhpcsHelpers::returnsCountInfo($file, $functionPos);

        static::assertSame('countInfo', $file->getDeclarationName($functionPos));
        static::assertSame(['nonEmpty' => 2, 'void' => 1, 'null' => 1, 'total' => 4], $info);
    }

    /**
     * @test
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength
     */
    public function testDocBlocTag()
    {
        // phpcs:enable Inpsyde.CodeQuality.FunctionLength

        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param string $foo
     * @return string
     * @customEmpty
     * @customFull  Hello There Foo
     *              next line
     */
    function one(string $foo): string {
        return $foo;
    }
    
    function two(string $foo): string {
        return $foo;
    }
    
    /**
     * @param string $foo
     * @return string
     * @customEmpty
     * @customFull Third
     * @customEmpty 
     * @customFull Third Again
     */
    function three(string $foo): string {
        return $foo;
    }
}
PHP;
        $file = $this->factoryFile($php);

        $oneFuncPos = $file->findNext(T_FUNCTION, 1);
        $twoFuncPos = $file->findNext(T_FUNCTION, $oneFuncPos + 1);
        $threeFuncPos = $file->findNext(T_FUNCTION, $twoFuncPos + 1);

        static::assertSame('one', $file->getDeclarationName($oneFuncPos));
        static::assertSame('two', $file->getDeclarationName($twoFuncPos));
        static::assertSame('three', $file->getDeclarationName($threeFuncPos));

        $oneCustomFull = PhpcsHelpers::functionDocBlockTag('customFull', $file, $oneFuncPos);
        $oneCustomEmpty = PhpcsHelpers::functionDocBlockTag('customEmpty', $file, $oneFuncPos);

        $twoCustomFull = PhpcsHelpers::functionDocBlockTag('customFull', $file, $twoFuncPos);
        $twoCustomEmpty = PhpcsHelpers::functionDocBlockTag('customEmpty', $file, $twoFuncPos);

        $threeCustomFull = PhpcsHelpers::functionDocBlockTag('customFull', $file, $threeFuncPos);
        $threeCustomEmpty = PhpcsHelpers::functionDocBlockTag('customEmpty', $file, $threeFuncPos);

        static::assertSame(["Hello There Foo\nnext line"], $oneCustomFull);
        static::assertSame([''], $oneCustomEmpty);

        static::assertSame([], $twoCustomFull);
        static::assertSame([], $twoCustomEmpty);

        static::assertSame(['Third', 'Third Again'], $threeCustomFull);
        static::assertSame(['', ''], $threeCustomEmpty);
    }

    /**
     * @test
     */
    public function testDocBlocTags()
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param string $foo
     * @param bool $bool
     * @return string
     */
    function something(string $foo, bool $bool): string {
        /**
         * @param string $foo
         * @return string
         * @custom Hello World
         * @wp-hook
         */
        $foo = static function () {
        
        };
    }
}
PHP;
        $file = $this->factoryFile($php);

        $oneFuncPos = $file->findNext(T_FUNCTION, 1);
        $twoFuncPos = $file->findNext(T_CLOSURE, $oneFuncPos + 1);

        $tagsOne = PhpcsHelpers::functionDocBlockTags($file, $oneFuncPos);
        $tagsTwo = PhpcsHelpers::functionDocBlockTags($file, $twoFuncPos);

        static::assertSame(
            [
                '@param' => ['string $foo', 'bool $bool'],
                '@return' => ['string'],
            ],
            $tagsOne
        );

        static::assertSame(
            [
                '@param' => ['string $foo'],
                '@return' => ['string'],
                '@custom' => ['Hello World'],
                '@wp-hook' => [''],
            ],
            $tagsTwo
        );
    }

    /**
     * @param string $content
     * @return File
     */
    private function factoryFile(string $content): File
    {
        $config = new Config();
        $config->standards = [];
        $config->extensions = ['php' => 'PHP'];
        $config->dieOnUnknownArg = false;
        $config->setCommandLineValues([]);
        /** @var Ruleset $ruleset */
        $ruleset = (new \ReflectionClass(Ruleset::class))->newInstanceWithoutConstructor();

        $file = new DummyFile($content, $ruleset, $config);
        $file->parse();

        return $file;
    }
}
