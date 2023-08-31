<?php

/*
 * This file is part of the "php-coding-standards" package.
 *
 * Copyright (c) 2023 Inpsyde GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\Helpers\Functions;
use Inpsyde\Helpers\WpHooks;

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
