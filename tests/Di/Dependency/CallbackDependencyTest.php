<?php

declare(strict_types=1);

namespace Tale\Test\Di\Dependency;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Di\Dependency\CallbackDependency;

/**
 * @coversDefaultClass \Tale\Di\Dependency\CallbackDependency
 */
class CallbackDependencyTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGet(): void
    {
        $callCount = 0;
        $dependency = new CallbackDependency(
            static function () use (&$callCount) {

                $callCount++;
                return 'some value';
            }
        );
        self::assertSame('some value', $dependency->get(new Container([])));
        self::assertSame(1, $callCount);
        self::assertSame('some value', $dependency->get(new Container([])));
        self::assertSame(2, $callCount);
    }
}
