<?php
// @phpcsSniff Inpsyde.CodeQuality.NoRootNamespaceFunctions

namespace Foo {

    function test() {

    }
}

namespace Foo\Bar {

    function test() {

    }
}

namespace {

    class Foo
    {
        function test() {

        }
    }

    // @phpcsErrorOnNextLine
    function test() {

    }
}
