<?php
// @phpcsSniff CodeQuality.ReturnTypeDeclaration

// @phpcsWarningCodeOnNextLine NoReturnType
function a()
{
    return 'x';
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
function c(): void
{
    return true;
}

function aa($foo)
{
    return;
}

function b($foo): bool
{
    return true;
}

function d($foo): void
{
    return;
}

// @phpcsErrorCodeOnNextLine MissingReturn
function e(): bool
{
    if (true) {
        die('x');
    }

    return;
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturn
function f(): bool
{
    if (true) {
        return true;
    }

    return;
}

function g(): bool
{
    if (true) {
        return true;
    }

    return false;
}

add_filter('x', function () {
    return '';
});

add_filter('x', function (): string {
    return '';
});

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
add_filter('x', function (): void {
    return '0';
});

// @phpcsErrorCodeOnNextLine MissingReturn
add_filter('x', function (): string {
    return;
});

// @phpcsWarningCodeOnNextLine NoReturnType
foo('x', function () {
    return '';
});

function filter_wrapper(): bool
{

    // @phpcsWarningCodeOnNextLine NoReturnType
    foo('x', function () {
        return '';
    });

    add_filter('x', function () {
        return '';
    });

    return true;
}

/**
 * @return string
 * @wp-hook Meh
 */
function hookCallback()
{
    return 'x';
}

/**
 * @return bool
 * @wp-hook Meh
 */
function badHookCallback(): bool // @phpcsErrorCodeOnThisLine MissingReturn
{
    return;
}

/**
 * @return string
 */
function noHookCallback() // @phpcsWarningCodeOnThisLine NoReturnType
{
    return 'x';
}

class WrapperHookWrapper
{

    function filterWrapper(string $x, int $y): bool
    {

        // @phpcsWarningCodeOnNextLine NoReturnType
        foo('x', function () {
            return '';
        });

        add_filter('x', function () use ($x, $y) {
            return "$x, $y";
        });

        return true;
    }

    public function register()
    {
        add_filter('foo_bar', function (array $a): array {
            return array_merge($a, ['x' => 'y']);
        });

        add_action(
            'foo_bar_baz',
            function ($x, $y) {
                $this->filterWrapper((string)$x, (int)$y);
            },
            10,
            2
        );
    }

    // @phpcsWarningCodeOnNextLine NoReturnType
    function problematicMethod()
    {
        return 'x';
    }

    /**
     * @return string
     * @wp-hook Meh
     */
    function hookMethod()
    {
        return 'x';
    }

    // @phpcsErrorCodeOnNextLine IncorrectVoidReturn
    function problematicMethodTwo(): bool
    {
        if (true) {
            return true;
        }

        return;
    }

    // @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
    function problematicMethodThree(): void
    {
        return 'x';
    }
}

interface LoremIpsum
{

    public function test1();

    /**
     * @wp-hook Meh
     */
    public function test2();

    /**
     * @return bool
     */
    public function test3(): bool;
}

class FooAccess implements ArrayAccess
{

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

    /**
     * @return \ArrayAccess|null
     */
    public function iMaybeReturnNull()
    {
        if (rand(1, 4) === 3) {
            return null;
        }

        if (rand(1, 4) > 2) {
            return null;
        }

        return new \ArrayObject();
    }

    /**
     * @return \ArrayAccess|null
     */
    public function iShouldReturnNullButReturnVoid() // @phpcsWarningCodeOnThisLine NoReturnType
    {
        if (rand(1, 4) === 3) {
            return null;
        }

        if (rand(1, 4) > 2) {
            return;
        }

        return new \ArrayObject();
    }

    /**
     * @return \ArrayAccess|null
     */
    public function iShouldReturnNull() // @phpcsWarningCodeOnThisLine NoReturnType
    {
        return new \ArrayObject();
    }

    /**
     * @return \ArrayAccess|null|\ArrayObject
     */
    public function iReturnALotOfStuff() // @phpcsWarningCodeOnThisLine NoReturnType
    {
        if (rand(1, 4) > 2) {
            return null;
        }

        return new \ArrayObject();
    }

    // @phpcsErrorCodeOnNextLine IncorrectVoidReturn
    public function iReturnWrongNull() : \ArrayAccess
    {
        if (rand(1, 4) > 2) {
            return null;
        }

        return new \ArrayObject();
    }
}