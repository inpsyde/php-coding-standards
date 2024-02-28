<?php
// @phpcsSniff Inpsyde.CodeQuality.ForbiddenPublicProperty

$foo = 'foo';

class Foo
{
    // @phpcsErrorOnNextLine
    public $foo;

    private $bar;

    protected $baz;
}

trait Bar {

    // @phpcsErrorOnNextLine
    public $foo;

    private $bar;

    protected $baz;
}
