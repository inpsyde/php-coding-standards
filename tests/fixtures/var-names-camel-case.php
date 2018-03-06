<?php
// @phpcsSniff CodeQuality.VariablesName

// @phpcsSniffPropertiesStart
// $checkType = "camelCase";
// @phpcsSniffPropertiesEnd

namespace VariablesNameTestCamelCase;

// @phpcsWarningOnNextLine
$foo_bar = 'foo_bar';

$foo = 'foo';

$fooBar = 'fooBar';

// @phpcsWarningOnNextLine
$foo_Bar = 'foo_Bar';

// @phpcsWarningOnNextLine
$FooBar = 'foo_Bar';

global $is_edge;
$is_edge = false;

class Foo {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine
    public static $FooBar = 'FooBar';

    // @phpcsWarningOnNextLine
    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine
    var $foo_Bar = 'foo_Bar';
}

trait Bar {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine
    public static $FooBar = 'FooBar';

    // @phpcsWarningOnNextLine
    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine
    var $foo_Bar = 'foo_Bar';
}