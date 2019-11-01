<?php

declare(strict_types=1);

namespace Tale\Test\Di\Dependency;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Di\Dependency\ParameterDependency;
use Tale\Di\Dependency\ValueDependency;

/**
 * @coversDefaultClass \Tale\Di\Dependency\ParameterDependency
 */
class ParameterDependencyTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGet(): void
    {
        $dependency = new ParameterDependency('test');
        self::assertSame(
            'some value',
            $dependency->get(
                new Container(
                    [
                        'test' => new ValueDependency('some value')
                    ]
                )
            )
        );
    }

    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGetThrowsExceptionWhenRequiredAndNonExistent(): void
    {
        $this->expectException(Container\NotFoundException::class);
        $dependency = new ParameterDependency('test');
        $dependency->get(new Container([]));
    }

    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGetReturnsDefaultValueWhenNotRequired(): void
    {
        $dependency = new ParameterDependency('test', true, 'some default value');
        self::assertSame('some default value', $dependency->get(new Container([])));
    }
}
