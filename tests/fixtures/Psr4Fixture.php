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

    // @phpcsErrorCodeOnNextLine WrongFilename
    class ThisIsWrong
    {

    }
}

// @phpcsErrorCodeOnNextLine NotInPSR4
namespace Inpsyde\InpsydeCodingStandard\Foo\Bar {

    interface ThisIsWrong
    {

    }
}

// @phpcsErrorCodeOnNextLine InvalidPSR4
namespace Inpsyde\InpsydeCodingStandard\Tests\Bar {

    trait ThisIsWrong
    {

    }
}

namespace I\Am\Excluded {

    interface Psr4Fixture
    {

    }
}