<?php

// @phpcsSniff Inpsyde.CodeQuality.NoAccessors

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

class WithAccessors implements \IteratorAggregate {

    function thing() {

    }

    function setting() {

    }

    // @phpcsWarningCodeOnNextLine NoGetter
    function getTheThing() {

    }

    function withThing() {

    }

    private function setTheThing($foo, $bar) {

    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator();
    }
}
