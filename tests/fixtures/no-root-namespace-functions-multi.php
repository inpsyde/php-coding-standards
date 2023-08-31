<?php
// @phpcsSniff CodeQuality.NoRootNamespaceFunctions

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
