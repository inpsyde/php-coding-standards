<?php
// @phpcsSniff CodeQuality.NoRootNamespaceFunctions

class Foo
{
    function test() {

    }
}

// @phpcsErrorOnNextLine
function test() {

}
