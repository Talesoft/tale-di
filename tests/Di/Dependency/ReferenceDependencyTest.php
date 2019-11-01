<?php

declare(strict_types=1);

namespace Tale\Test\Di\Dependency;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Di\Dependency\ReferenceDependency;
use Tale\Di\Dependency\ValueDependency;

/**
 * @coversDefaultClass \Tale\Di\Dependency\ReferenceDependency
 */
class ReferenceDependencyTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGet(): void
    {
        $dependency = new ReferenceDependency('test');
        self::assertSame(
            'some value',
            $dependency->get(
                new Container(
                    [
                        'test' => new ValueDependency('some value'),
                    ]
                )
            )
        );
    }
}
