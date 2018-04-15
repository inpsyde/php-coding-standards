<?php
// @phpcsSniff CodeQuality.ArgumentTypeDeclaration

// @phpcsWarningOnNextLine
function a($foo)
{

}

function b(string $foo = 'foo')
{

}

function c(ArrayObject $foo)
{

}

function d(PHPUnit\Exception $foo = null)
{

}

// @phpcsWarningOnNextLine
function e(...$foo)
{

}

function f(string ...$foo)
{

}

function g(PHPUnit\Exception ...$foo)
{

}

// @phpcsWarningOnNextLine
function h(string $foo, $bar = true)
{

}

function i(string $foo, ArrayObject ...$bar)
{

}

add_action(
    'foo',
    function ($foo) {

    }
);

array_map(
    function ($foo) { // @phpcsWarningOnThisLine

    },
    []
);

function ()
{

}

// @phpcsWarningOnNextLine
function ($foo)
{

}

// @phpcsWarningOnNextLine
function (PHPUnit\Exception $foo, $bar)
{

}

function (PHPUnit\Exception $foo, bool $bar)
{

}

function foo(array $foo)
{
    // @phpcsWarningOnNextLine
    function ($bar) use ($foo)
    {

    }
}

function (array $foo)
{
    // @phpcsWarningOnNextLine
    function ($bar) use ($foo)
    {

    }
}

function bar(array $bar)
{
    function (array $baz) use ($bar)
    {

    }
}

function (array $bar)
{
    function (array $baz) use ($bar)
    {

    }
}

// @phpcsWarningOnNextLine
function baz($bar)
{
    function (array $baz) use ($bar)
    {

    }
}

// @phpcsWarningOnNextLine
function ($bar)
{
    function (array $baz) use ($bar)
    {

    }
}

function (array $bar)
{

    function (array $baz) use ($bar)
    {
        // @phpcsWarningOnNextLine
        function ($meh) use ($baz)
        {

        }
    }
}

function (array $bar)
{

    function (array $baz) use ($bar)
    {
        function (PHPUnit\Exception $meh = null) use ($baz)
        {

        }
    }
}

add_action('foo', function (array $foo) {

});

class ArgumentTypeDeclarationSniffTestFixture
{
    // @phpcsWarningOnNextLine
    function a($bar)
    {
        function (array $baz) use ($bar)
        {

        }
    }

    function b(array $bar)
    {
        function (array $baz) use ($bar)
        {
            // @phpcsWarningOnNextLine
            function ($meh) use ($baz)
            {

            }
        }
    }

    function c(array $bar)
    {

        function (array $baz) use ($bar)
        {

            function (PHPUnit\Exception $meh = null) use ($baz)
            {

            }
        }
    }
}

interface ArgumentTypeDeclarationSniffTestFixtureInterface
{

    function a(array $foo);

    // @phpcsWarningOnNextLine
    function b(array $foo, $bar);

    function d(array $foo, ArrayObject $bar);
}

class FooAccess implements ArrayAccess {

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}

class SerializeTest implements \Serializable {

    public function serialize()
    {
        return '';
    }

    public function unserialize($serialized)
    {
    }
}
