<?php

// @phpcsSniff Inpsyde.CodeQuality.DisableSerializeInterface

// @phpcsErrorOnNextLine
class One implements Serializable {

    public function serialize()
    {
        return null;
    }

    public function unserialize($data)
    {
    }
}

// @phpcsErrorOnNextLine
$x = new class implements Serializable {

    public function serialize()
    {
        return null;
    }

    public function unserialize($data)
    {
    }
};

class Three {

    public function serialize()
    {
        return null;
    }

    public function unserialize($data)
    {
    }
}

// @phpcsErrorOnNextLine
interface Two extends Serializable {

}

class Four {

    public function __serialize()
    {
        return null;
    }

    public function __unserialize($data)
    {
    }
}
