<?php

// @phpcsSniff CodeQuality.NoAccessors

function getting() {

}

function setting() {

}

interface WithAccessorsInterface {

    // @phpcsWarningCodeOnNextLine NoGetter
    function getTheThing();

    // @phpcsWarningCodeOnNextLine NoSetter
    function setTheThing($foo, $bar);

    function setting();
}

class WithAccessors {

    function thing() {

    }

    function setting() {

    }

    // @phpcsWarningCodeOnNextLine NoGetter
    function getTheThing() {

    }

    function withThing() {

    }

    // @phpcsWarningCodeOnNextLine NoSetter
    function setTheThing($foo, $bar) {

    }
}
