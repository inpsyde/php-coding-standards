<?php
// @phpcsSniff CodeQuality.ArgumentTypeDeclaration

/** @wp-hook */
$cb = function ($foo) {
    return true;
};

array_walk($slugs, function (string &$slug) {
    $slug = sanitize_key($slug);
});

// @phpcsWarningOnNextLine
function a($foo)
{

}

/**
 * @param string $foo
 * @param mixed $bar
 * @param int $baz
 * @return void
 */
function mixed(string $foo, $bar, int $baz)
{

}

/**
 * @param string $foo
 * @param mixed|null $bar
 * @param int $baz
 * @return void
 */
function mixedNull(string $foo, $bar, int $baz)
{

}

/**
 * @param string $foo
 * @param mixed|null|array $bar
 * @param int $baz
 * @return void
 */
function mixedNullAndMore(string $foo, $bar, int $baz) // @phpcsWarningOnThisLine
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
        return false;
    }
);

$cb = add_action(
    'foo',
    function ($foo) {
        return true;
    }
);

/** @wp-hook */
function ($foo) {
    return true;
};

add_filter(
    'foo',
    static function ($foo) {
        return true;
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

fn() => true;

// @phpcsWarningOnNextLine
fn($foo) => $foo*2;

fn(int $foo) => $foo*2;

// @phpcsWarningOnNextLine
fn(int $foo, $bar) => true;

fn(int $foo, string $bar) => true;

// @phpcsWarningOnNextLine
fn(...$foo) => true;

fn(int ...$foo) => true;

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
    public function a($bar)
    {
        function (array $baz) use ($bar)
        {

        }
    }

    // @phpcsWarningOnNextLine
    private function ap($bar)
    {
        function (array $baz) use ($bar)
        {

        }
    }

    /**
     * @wp-hook foo
     */
    private function theHook($param)
    {
    }

    protected function b(array $bar)
    {
        function (array $baz) use ($bar)
        {
            // @phpcsWarningOnNextLine
            function ($meh) use ($baz)
            {

            }
        }
    }

    private function c(array $bar)
    {

        function (array $baz) use ($bar)
        {

            function (PHPUnit\Exception $meh = null) use ($baz)
            {

            }
        }
    }

    public function buildCallback(): callable {

        /**
         * @wp-hook
         */
        return function (...$args) {

        };
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

class Container implements \Psr\Container\ContainerInterface {

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
