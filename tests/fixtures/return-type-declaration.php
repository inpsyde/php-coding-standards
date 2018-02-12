<?php
// @phpcsSniff CodeQuality.ReturnTypeDeclaration

// @phpcsWarningCodeOnNextLine NoReturnType
function a()
{
    return 'x';
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
function c(): void
{
    return true;
}

function aa($foo)
{
    return;
}

function b($foo): bool
{
    return true;
}

function d($foo): void
{
    return;
}

// @phpcsErrorCodeOnNextLine MissingReturn
function e(): bool
{
    if (true) {
        die('x');
    }

    return;
}

// @phpcsErrorCodeOnNextLine IncorrectVoidReturn
function f(): bool
{
    if (true) {
        return true;
    }

    return;
}

function g(): bool
{
    if (true) {
        return true;
    }

    return false;
}

add_filter('x', function() {
    return '';
});

add_filter('x', function(): string {
    return '';
});

// @phpcsErrorCodeOnNextLine IncorrectVoidReturnType
add_filter('x', function(): void {
    return '0';
});

// @phpcsErrorCodeOnNextLine MissingReturn
add_filter('x', function(): string {
    return;
});

// @phpcsWarningCodeOnNextLine NoReturnType
foo('x', function() {
    return '';
});

function filter_wrapper(): bool {

    // @phpcsWarningCodeOnNextLine NoReturnType
    foo('x', function() {
        return '';
    });

    add_filter('x', function() {
        return '';
    });

    return true;
}

/**
 * @return string
 * @wp-hook Meh
 */
function hookCallback() {
    return 'x';
}

/**
 * @return bool
 * @wp-hook Meh
 */
function badHookCallback(): bool {
    // @phpcsErrorCodeOnPreviousLine MissingReturn
    return;
}

/**
 * @return string
 */
function noHookCallback() {
    // @phpcsWarningCodeOnPreviousLine NoReturnType
    return 'x';
}

class WrapperHookWrapper {

    function filterWrapper(): bool {

        // @phpcsWarningCodeOnNextLine NoReturnType
        foo('x', function() {
            return '';
        });

        add_filter('x', function() {
            return '';
        });

        return true;
    }

    // @phpcsWarningCodeOnNextLine NoReturnType
    function problematicMethod() {
        return 'x';
    }

    /**
     * @return string
     * @wp-hook Meh
     */
    function hookMethod() {
        return 'x';
    }

    function problematicMethodTwo(): bool {
        // @phpcsErrorCodeOnPreviousLine IncorrectVoidReturn
        if (true) {
            return true;
        }

        return;
    }

    function problematicMethodThree(): void {
        // @phpcsErrorCodeOnPreviousLine IncorrectVoidReturnType
        return 'x';
    }
}

interface LoremIpsum {

    public function test1();

    /**
     * @wp-hook Meh
     */
    public function test2();

    /**
     * @return bool
     */
    public function test3(): bool ;
}