<?php

declare(strict_types=1);

namespace Tale\Test\Di\Container;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Di\Dependency\ValueDependency;
use Tale\Test\Di\TestClasses\Service\Importer\UserImporter;

/**
 * @coversDefaultClass \Tale\Di\Container
 */
class ContainerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::has
     * @covers ::get
     */
    public function testHasAndGet(): void
    {
        $container = new Container([]);
        self::assertFalse($container->has(UserImporter::class));
        $container = new Container([UserImporter::class => new ValueDependency(new UserImporter())]);
        self::assertTrue($container->has(UserImporter::class));
        self::assertInstanceOf(UserImporter::class, $container->get(UserImporter::class));
    }

    /**
     * @covers ::__construct
     * @covers ::get
     */
    public function testGetThrowsExceptionWhenServiceNotFound(): void
    {
        $this->expectException(Container\NotFoundException::class);
        $container = new Container([]);
        $container->get(UserImporter::class);
    }
}
