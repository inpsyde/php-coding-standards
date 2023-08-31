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
use Inpsyde\Helpers\FunctionReturnStatement;

class FunctionReturnStatementTest extends TestCase
{
    /**
     * @test
     */
    public function testAllInfoForFunction(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    public function countInfo($x) {
        if ($x === 'void') {
            return;
        }
        
        if ($x === 'null') {
            return null;
        }
        
        $cb =  function ($x) {
            if ($x === 'void') {
                return;
            }
            
            return new class () {
                public function count($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
        };
        
        if (!$cb(1)) {
            $n = new class () {
                public function something($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
            
            return $n->something(1);
        }
        
        return true;
    }
}
PHP;

        $file = $this->factoryFile($php);

        $functionPos = $file->findNext(T_FUNCTION, 1);
        $info = FunctionReturnStatement::allInfo($file, $functionPos);

        static::assertSame('countInfo', $file->getDeclarationName($functionPos));
        static::assertSame(['nonEmpty' => 2, 'void' => 1, 'null' => 1, 'total' => 4], $info);
    }

    /**
     * @test
     */
    public function testAllInfoShortForClosure(): void
    {
        $php = <<<'PHP'
<?php
fn () => true;
fn () => null;
fn () => 'x';
PHP;

        $file = $this->factoryFile($php);

        $fn1Pos = $file->findNext(T_FN, 1);
        $fn2Pos = $file->findNext(T_FN, $fn1Pos + 1);
        $fn3Pos = $file->findNext(T_FN, $fn2Pos + 1);
        $info1 = FunctionReturnStatement::allInfo($file, $fn1Pos);
        $info2 = FunctionReturnStatement::allInfo($file, $fn2Pos);
        $info3 = FunctionReturnStatement::allInfo($file, $fn3Pos);

        static::assertSame(['nonEmpty' => 1, 'void' => 0, 'null' => 0, 'total' => 1], $info1);
        static::assertSame(['nonEmpty' => 0, 'void' => 0, 'null' => 1, 'total' => 1], $info2);
        static::assertSame(['nonEmpty' => 1, 'void' => 0, 'null' => 0, 'total' => 1], $info3);
    }
}
