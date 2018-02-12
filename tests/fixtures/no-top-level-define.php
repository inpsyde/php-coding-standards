<?php

namespace Inpsyde\InpsydeCodingStandard\Tests\Fixtures;

// @phpcsSniff CodeQuality.NoTopLevelDefine

if (!defined('X')) {
    define('X', 1);
}

if (false) {
    define('Y', 1);
}

// @phpcsWarningOnNextLine
define('Z', 1);

const ZZZ = 1;