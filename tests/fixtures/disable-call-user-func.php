<?php
// @phpcsSniff CodeQuality.DisableCallUserFunc

function test() {
    // @phpcsErrorOnNextLine
    return call_user_func('strtolower', 'foo');
}

echo 'call_user_func_array';

$foo = [
    'call_user_func',
    'call_user_func_array',
];

class Foo {

    pribate function test() {
        // @phpcsErrorOnNextLine
        return call_user_func_array('strtolower', ['foo']);
    }
}
