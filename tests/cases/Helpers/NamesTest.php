<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\CodingStandard\Helpers\Names;

class NamesTest extends TestCase
{
    /**
     * @test
     */
    public function testNameableTokenName(): void
    {
        $php = <<<'PHP'
<?php
namespace {

    interface a {
        public static function b(): string;
    }
    function c(string $d): string {
        return '';
    }
    abstract class e implements a {
        const f = 'f';
        function g(): string {
            return c($h = 'h');
        }
    }
    trait i {
        public function j() {
        }
    }
    class k {
        use i;
        function l() {
        }
    }
    enum m {
        case n;
        case o;
    }
    enum p {
        case q = 'q';
        case r = 'r';
    }
    ((new k())->l());
    $s = 's';
}

namespace t {

}
PHP;
        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $names = [];
        foreach ($tokens as $pos => $token) {
            $name = Names::nameableTokenName($file, $pos);
            $name and $names[] = $name;
        }

        static::assertSame(range('a', 't'), $names);
    }
}
