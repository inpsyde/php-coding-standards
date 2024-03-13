<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\CodingStandard\Helpers\Functions;
use Inpsyde\CodingStandard\Helpers\WpHooks;

class WpHooksTest extends TestCase
{
    /**
     * @test
     */
    public function testHookClosure(): void
    {
        $php = <<<'PHP'
<?php

add_action /* x */ (theHookPrefix() . 'xx', static
    fn () /* add_action('x', function () {}) */ => 'find me short!';
);

add_action('x', '__return_false');

function theHookPrefix() {
    return 'x_';
}

add_action /* x */ (theHookPrefix() . 'xx', 
    static
    function /* add_action('x', function () {}) */
    () {
        return 'find me!';
    }
);

function add_action($x, $y) {
    return function () {
        return function() {
            add_action('x', 'theHookPrefix');
        };
    };
}
PHP;

        $file = $this->factoryFile($php);
        $tokens = $file->getTokens();

        $bodies = [];
        foreach ($tokens as $pos => $token) {
            $isHookClosure = WpHooks::isHookClosure($file, $pos);
            $isHookClosure and $bodies[] = trim(Functions::bodyContent($file, $pos));
        }

        static::assertSame(["'find me short!'", "return 'find me!';"], $bodies);
    }
}
