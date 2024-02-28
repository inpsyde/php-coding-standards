<?php
// @phpcsSniff Inpsyde.CodeQuality.ReturnTypeDeclaration

use Brain\Assets\Enqueue\Enqueue;
use Psr\Container\ContainerInterface as PsrContainer;

class FooIterator implements IteratorAggregate
{
    private array $collection = [];

    /**
     * @return Iterator<int, Foo>
     */
    public function getIterator(): \Iterator
    {
        return new ArrayIterator($this->collection);
    }
}

function returnMixed(): mixed
{
    return null;
}

add_filter('x', function () {
    return '';
});

add_filter('x', function (): string {
    return '';
});

add_filter('x', static function () {
    return '';
});

function hooks(): ?array
{
    return null;
}

// @phpcsWarningCodeOnNextLine NoReturnType
function a()
{
    return 'x';
}

// @phpcsErrorCodeOnNextLine IncorrectNullReturn
function iReturnWrongNull(): \ArrayAccess
{
    if (rand(1, 4) > 2) {
        return null;
    }

    return new \ArrayObject();
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
function c(): void
{
    return true;
}

// @phpcsWarningCodeOnNextLine NoReturnType
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

// @phpcsErrorCodeOnNextLine IncorrectVoidReturn
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

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
function h(): void
{
    return null === true;
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
function hh(): void {
    return null;
}

function hhh(): void {
    return;
}

function hhComment(): void {
    return /* I return void */ ;
}

function gen(string $content): \Generator
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        yield is_string($line) ? trim($line) : '';
    }
}

// @phpcsErrorCodeOnNextLine GeneratorReturnTypeWithoutYield
function genNoYield1(string $content): \Generator
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        is_string($line) ? trim($line) : '';
    }

    return true;
}

// @phpcsErrorCodeOnNextLine GeneratorReturnTypeWithoutYield
function genNoYield2(string $content): (Generator&Countable)|bool
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        is_string($line) ? trim($line) : '';
    }

    return true;
}

// @phpcsErrorCodeOnNextLine NoGeneratorReturnType
function yieldNoGen1(string $content): Foo
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        yield is_string($line) ? trim($line) : '';
    }
}

// @phpcsErrorCodeOnNextLine NoGeneratorReturnType
function yieldNoGen2(string $content)
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        yield is_string($line) ? trim($line) : '';
    }
}

// @phpcsErrorCodeOnNextLine NoGeneratorReturnType
function yieldWrongReturn(string $content): int
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        yield is_string($line) ? trim($line) : '';
    }

    return 1;
}

function yieldIteratorReturn(string $content): \Iterator
{
    $line = strtok($content, "\n");
    while ($line !== false) {
        $line = strtok("\n");
        yield is_string($line) ? trim($line) : '';
    }

    return 1;
}


function genFrom(): \Generator
{

    $gen = function (int $x): int {
        if ($x < 0) {
            return 0;
        }

        if ($x > 100) {
            return 100;
        }

        return $x;
    };

    $data = array_map($gen, range(-100, 100));
    yield from $data;
}

// @phpcsErrorCodeOnNextLine InvalidGeneratorManyReturns
function genMultiReturn(): \Generator
{
    if (defined('MEH_MEH')) {
        return 1;
    }

    yield from [1, 2];

    if (defined('MEH')) {
        return 1;
    }

    return 2;
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturn
function returnMixed2(): mixed
{
    return;
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
add_filter('x', function (): void {
    return '0';
});

// @phpcsErrorCodeOnNextLine IncorrectVoidReturn
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

fn() => true;

fn(): bool => true;

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
function badHookCallback(): bool // @phpcsErrorCodeOnThisLine IncorrectVoidReturn
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

/**
 * @return mixed
 */
function mixed() {
    return $GLOBALS['thing'] ?? null;
}

class WrapperHookWrapper
{

    public function filterWrapper(string $x, int $y): bool
    {

        // @phpcsWarningCodeOnNextLine NoReturnType
        foo('x', function () {
            return '';
        });

        add_filter('x', function () use ($x, $y) {
            return "$x, $y";
        });

        add_filter('x', fn($x, $y) => "$x, $y");

        return true;
    }

    // @phpcsWarningCodeOnNextLine NoReturnType
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

        add_action('x', fn($x, $y) => "$x, $y");
    }

    // @phpcsWarningCodeOnNextLine NoReturnType
    protected function problematicMethod()
    {
        return 'x';
    }

    /**
     * @return string
     * @wp-hook Meh
     */
    private function hookMethod()
    {
        return 'x';
    }

    // @phpcsErrorCodeOnNextLine IncorrectVoidReturn
    protected function problematicMethodTwo(): bool
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
    private string $x;

    public function __construct(string $x)
    {
        if ($x === '') {
            return;
        }
        $this->x = $x;
    }

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
    public function iMaybeReturnNull() // @phpcsWarningOnThisLine
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
    public function iShouldReturnNullButReturnVoid() // @phpcsWarningOnThisLine
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
    public function iShouldReturnNull() // @phpcsWarningOnThisLine
    {
        return new \ArrayObject();
    }

    /**
     * @return \ArrayAccess|null|\ArrayObject
     */
    public function iReturnALotOfStuff()
    {
        if (rand(1, 4) > 2) {
            return null;
        }

        return new \ArrayObject();
    }

    // @phpcsErrorCodeOnNextLine IncorrectNullReturn
    public function iReturnWrongNull(): \ArrayAccess
    {
        if (rand(1, 4) > 2) {
            return null;
        }

        return new \ArrayObject();
    }

    function returnIterable(): iterable
    {
        yield 1;
        yield 2;
    }

    function returnIterator(): Iterator
    {
        yield 1;
        yield 2;
    }

    function returnTraversable(): Traversable
    {
        yield 1;
        yield 2;
    }
}

class Container implements PsrContainer {

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

class JsonSerializeTest implements \JsonSerializable, \Serializable {

    public function jsonSerialize(): string
    {
        return '';
    }

    public function serialize(): string
    {
        return '';
    }

    public function unserialize($serialized): void
    {
    }
}
