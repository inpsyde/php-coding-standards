<?php
// @phpcsSniff Inpsyde.CodeQuality.ElementNameMinimalLength

namespace {

    // @phpcsErrorOnNextLine
    function a()
    {

    }

    // @phpcsErrorOnNextLine
    $a = 'a';

    $db = 'db';

    class Db {

    }

    // @phpcsErrorOnNextLine
    class Ff {

    }

    // @phpcsErrorOnNextLine
    interface Ii {

    }

    interface Up {

        // @phpcsErrorOnNextLine
        public function z();

        public function id();
    }

    // @phpcsErrorOnNextLine
    trait T {

        // @phpcsErrorOnNextLine
        public function z() {

        }

        public function go() {

        }
    }

    for ($i = 1; $i < 10; $i ++) {
        echo $i;
    }

    // @phpcsErrorOnNextLine
    for ($u = 1; $u < 10; $u ++) {
        // @phpcsErrorOnNextLine
        echo $u;
    }

    // @phpcsErrorOnNextLine
    const A = 'a';

    const ID = 'ID';

    class It {

        public function hello()
        {

        }

        // @phpcsErrorOnNextLine
        public function ciao($foo, $x = 'x')
        {

        }

        // @phpcsErrorOnNextLine
        private function xx()
        {

        }
    }
}


namespace ok {}

// @phpcsErrorOnNextLine
namespace hh {}
