<?php
// @phpcsSniff Inpsyde.CodeQuality.NoRootNamespaceFunctions

class Foo
{
    function test() {

    }
}

// @phpcsErrorOnNextLine
function test() {

}
