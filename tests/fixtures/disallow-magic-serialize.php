<?php

// @phpcsSniff CodeQuality.DisableMagicSerialize

class Foo {

    // @phpcsErrorOnNextLine
    public function __serialize(): array
    {
        return [];
    }

    public function serialize(): array
    {
        return [];
    }

    // @phpcsErrorOnNextLine
    public function __sleep(): array
    {
        return [];
    }

    public function sleep(): array
    {
        return [];
    }
}
