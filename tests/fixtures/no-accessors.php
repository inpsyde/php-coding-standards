<?php

// @phpcsSniff CodeQuality.NoAccessors

interface WithAccessorsInterface {

    // @phpcsWarningCodeOnNextLine NoGetter
    function getTheThing();

    // @phpcsWarningCodeOnNextLine NoSetter
    function setTheThing($foo, $bar);
}

class WithAccessors {

    function thing() {

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
