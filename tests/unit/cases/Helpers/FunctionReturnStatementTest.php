<?php

declare(strict_types=1);

namespace Inpsyde\CodingStandard\Tests\Helpers;

use Inpsyde\CodingStandard\Tests\TestCase;
use Inpsyde\CodingStandard\Helpers\FunctionReturnStatement;

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
