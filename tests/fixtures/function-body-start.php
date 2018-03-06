<?php
// @phpcsSniff CodeQuality.FunctionBodyStart

namespace FunctionBodyStartTest;

function foo()
{
    return 'foo';
}

// @phpcsWarningCodeOnNextLine WrongForSingleLineDeclaration
function bar()
{

    return 'bar';
}

// @phpcsWarningCodeOnNextLine WrongForMultiLineDeclaration
function fooFoo(
    string $foo,
    string $bar,
    string $baz
) {
    return 'foo';
}

// @phpcsWarningCodeOnNextLine WrongForSingleLineSignature
function fooFooBar(string $foo, string $bar, string $baz) {
    return 'foo';
}

function fooFooBar2(string $foo, string $bar, string $baz) {
    // it's ok
    return 'foo';
}

// @phpcsWarningCodeOnNextLine WrongForMultiLineDeclaration
function barBar(
    string $foo,
    string $bar,
    string $baz
): string {
    return 'foo';
}

function barBarBaz(
    string $foo,
    string $bar,
    string $baz
): string {

    return 'foo';
}

interface Foo
{

    public function fooBar();
}

class BarBarBar
{

    public function foo()
    {
        return 'foo';
    }

    // @phpcsWarningCodeOnNextLine WrongForSingleLineDeclaration
    private function bar()
    {

        return 'bar';
    }

    // @phpcsWarningCodeOnNextLine WrongForMultiLineDeclaration
    protected function fooFoo(
        string $foo,
        string $bar,
        string $baz
    ) {
        return 'foo';
    }

    // @phpcsWarningCodeOnNextLine WrongForMultiLineDeclaration
    public function barBar(
        string $foo,
        string $bar,
        string $baz
    ): string {
        return 'foo';
    }

    private function barBarBaz(
        string $foo,
        string $bar,
        string $baz
    ): string {

        return 'foo';
    }

    private function barBarBazComment(
        string $foo,
        string $bar,
        string $baz
    ): string {
        // it's ok
        return 'foo';
    }
}
