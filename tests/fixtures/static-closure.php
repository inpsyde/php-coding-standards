<?php
// @phpcsSniff CodeQuality.StaticClosure

/**
 * @return string
 * @var Foo $this
 */
function () {
    return 'Foo';
};

/**
 * @return string
 * @bound
 */
function () {
    return 'Foo';
};

// @phpcsWarningOnNextLine
function () {
    return 'Foo';
};

function () {
    return $this;
};

function () {
    $foo = $this;

    return $foo;
};

fn() => $this;

static fn() => 'Foo';

static function () {
    return 'Foo';
};

class Foo {

    public function a()
    {
        function () {
            $foo = $this;

            return $foo;
        };

        static function () {
            return 'Foo';
        };
    }

    public function b()
    {
        /**
         * @return string
         */
        $a = function () { // @phpcsWarningOnThisLine
            return 'Foo';
        };

        $a = fn () => 'x'; // @phpcsWarningOnThisLine

        return $a;
    }

    private function c()
    {
        /**
         * @return string
         * @var Foo $this
         */
        function () {
            return 'Foo';
        };

        /**
         * @return string
         * @bound
         */
        function () {
            return 'Foo';
        };

        /** @bound */
        fn() => 'Foo';

        fn() => $this;
    }
}

add_filter('x', fn() => 'y'); // @phpcsWarningOnThisLine
add_filter('x', static fn() => 'y');
