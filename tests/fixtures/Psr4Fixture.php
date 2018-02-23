<?php # -*- coding: utf-8 -*-
// @phpcsSniff CodeQuality.Psr4

namespace {

    // @phpcsSniffPropertiesStart
    $psr4 = ["\\Inpsyde\\InpsydeCodingStandard\\Tests\\" => "tests/"];
    $exclude = ["\\I\\Am\\Excluded\\Psr4Fixture"];
    // @phpcsSniffPropertiesEnd
}

namespace Inpsyde\InpsydeCodingStandard\Tests\fixtures {

    class Psr4Fixture
    {

    }

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    class ThisIsWrong
    {

    }
}

namespace Inpsyde\InpsydeCodingStandard\Foo\Bar {

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    interface ThisIsWrong
    {

    }
}

namespace Inpsyde\InpsydeCodingStandard\Tests\Bar {

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    trait ThisIsWrong
    {

    }
}

namespace I\Am\Excluded {

    interface Psr4Fixture
    {

    }
}