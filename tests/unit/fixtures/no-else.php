<?php

// @phpcsSniff Inpsyde.CodeQuality.NoElse

if ('x') {

} elseif ('y') {

} else {
    // @phpcsWarningOnPreviousLine
    die();
}

function test()
{
    if ('x') {

    } elseif ('y') {

    } else {
        // @phpcsWarningOnPreviousLine
        die();
    }
}

class FooBarBaz
{
    public function test()
    {
        if ('x') {

        } elseif ('y') {

        } else {
            // @phpcsWarningOnPreviousLine
            die();
        }
    }
}
