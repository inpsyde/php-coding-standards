<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\CodingStandard\Helpers\FunctionDocBlock;

class FunctionDocBlocTest extends TestCase
{
    /**
     * @test
     */
    public function testAllTags(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param ?string $foo
     * @param bool $bool
     * @return string
     */
    function something(string $foo = null, bool $bool): string {
        /**
         * @param string $foo
         * @return string
         * @custom Hello World
         * @wp-hook
         */
        $foo = static function () {
            return '';
        };
        
        return '';
    }
}
PHP;
        $file = $this->factoryFile($php);

        $oneFuncPos = $file->findNext(T_FUNCTION, 1);
        $twoFuncPos = $file->findNext(T_CLOSURE, $oneFuncPos + 1);

        $tagsOne = FunctionDocBlock::allTags($file, $oneFuncPos);
        $tagsTwo = FunctionDocBlock::allTags($file, $twoFuncPos);

        static::assertSame(
            [
                '@param' => ['?string $foo', 'bool $bool'],
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
     * @test
     */
    public function testTag(): void
    {
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

        $oneCustomFull = FunctionDocBlock::tag('customFull', $file, $oneFuncPos);
        $oneCustomEmpty = FunctionDocBlock::tag('customEmpty', $file, $oneFuncPos);

        $twoCustomFull = FunctionDocBlock::tag('customFull', $file, $twoFuncPos);
        $twoCustomEmpty = FunctionDocBlock::tag('customEmpty', $file, $twoFuncPos);

        $threeCustomFull = FunctionDocBlock::tag('customFull', $file, $threeFuncPos);
        $threeCustomEmpty = FunctionDocBlock::tag('customEmpty', $file, $threeFuncPos);

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
    public function testAllParamTypes(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    /**
     * @param string $foo
     * @param string|int|\SomeClass $bar
     * @return string
     */
    function something(string $foo, $bar): string {
        /**
         * @param ?string|int|\SomeClass $foo
         * @param string|null|int $bar
         * @return string
         * @custom Hello World
         * @wp-hook
         */
        $cb = static function ($foo, $bar) {
            return '';
        };
        
        return '';
    }
}
PHP;
        $file = $this->factoryFile($php);

        $oneFuncPos = $file->findNext(T_FUNCTION, 1);
        $twoFuncPos = $file->findNext(T_CLOSURE, $oneFuncPos + 1);

        $paramsOne = FunctionDocBlock::allParamTypes($file, $oneFuncPos);
        $paramsTwo = FunctionDocBlock::allParamTypes($file, $twoFuncPos);

        static::assertSame(
            [
                '$foo' => ['string'],
                '$bar' => ['\SomeClass', 'int', 'string'],
            ],
            $paramsOne
        );

        static::assertSame(
            [
                '$foo' => ['\SomeClass', 'int', 'string', 'null'],
                '$bar' => ['int', 'string', 'null'],
            ],
            $paramsTwo
        );
    }
}
