<?php # -*- coding: utf-8 -*-
// @phpcsSniff Inpsyde.CodeQuality.Psr4

namespace {

    // @phpcsSniffPropertiesStart
    $psr4 = ["\\Inpsyde\\CodingStandard\\Tests\\" => "tests/"];
    $exclude = ["\\I\\Am\\Excluded\\Psr4Fixture"];
    // @phpcsSniffPropertiesEnd
}

namespace Inpsyde\CodingStandard\Tests\fixtures {

    class Psr4Fixture
    {

    }

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    class ThisIsWrong
    {

    }
}

namespace Inpsyde\CodingStandard\Tests\fixtures\Foo {

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    class Psr4Fixture
    {

    }
}

namespace Inpsyde\CodingStandard\Foo\Bar {

    // @phpcsErrorCodeOnNextLine InvalidPSR4
    interface ThisIsWrong
    {

    }
}

namespace Inpsyde\CodingStandard\Tests\Bar {

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
