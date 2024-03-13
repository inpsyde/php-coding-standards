<?php

// @phpcsSniff Inpsyde.CodeQuality.DisableMagicSerialize

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

    // @phpcsErrorOnNextLine
    public function __wakeup(): array
    {
        return [];
    }

    public function wakeup(): array
    {
        return [];
    }

    // @phpcsErrorOnNextLine
    public function __unserialize(): array
    {
        return [];
    }

    public function unserialize(): array
    {
        return [];
    }
}
