<?php
// @phpcsSniff Inpsyde.CodeQuality.PropertyPerClassLimit

class Reasonable {

    private $_1;
    protected $_2;
    public $_3;
    private $_4;
    protected $_5;
    public $_6;
    private $_7;
    protected $_8;
}

// @phpcsWarningOnNextLine
class TooMuchProps {

    private $_1;
    protected $_2;
    public $_3;
    private $_4;
    protected $_5;
    public $_6;
    private $_7;
    protected $_8;
    public $_9;
    private $_10;
    protected $_11;
    public $_12;
    private $_13;
}

trait ReasonableTrait {

    private $_1;
    protected $_2;
    public $_3;
    private $_4;
    protected $_5;
    public $_6;
    private $_7;
    protected $_8;
}

// @phpcsWarningOnNextLine
trait TooMuchPropsTrait {

    private $_1;
    protected $_2;
    public $_3;
    private $_4;
    protected $_5;
    public $_6;
    private $_7;
    protected $_8;
    public $_9;
    private $_10;
    protected $_11;
    public $_12;
    private $_13;
}
