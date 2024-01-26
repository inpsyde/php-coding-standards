<?php
// @phpcsSniff Inpsyde.CodeQuality.VariablesName

// @phpcsSniffPropertiesStart
// $checkType = 'snake_case';
// $ignoredNames = ['IAMALLOWED', 'anId'];
// $ignoreLocalVars = "true";
// @phpcsSniffPropertiesEnd

namespace VariablesNameTestCamelCase;

$foo_bar = 'foo_bar';
$foo = 'foo';
$fooBar = 'fooBar';
$foo_Bar = 'foo_Bar';
$FooBar = 'foo_Bar';

global $is_NS4;
$is_NS4 = false;
$_GET = [];
$IAMALLOWED = true;

class Foo {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine
    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    // @phpcsWarningOnNextLine
    protected $fooBar = 'fooBar';

    private $anId = 1;

    // @phpcsWarningOnNextLine
    var $foo_Bar = 'foo_Bar';
}

trait Bar {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine
    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    // @phpcsWarningOnNextLine
    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine
    var $foo_Bar = 'foo_Bar';
}
