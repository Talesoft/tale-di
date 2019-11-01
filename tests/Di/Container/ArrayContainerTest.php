<?php

declare(strict_types=1);

namespace Tale\Test\Di\Container;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Test\Di\TestClasses\Service\Importer\UserImporter;

/**
 * @coversDefaultClass \Tale\Di\Container\ArrayContainer
 */
class ArrayContainerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::has
     * @covers ::get
     */
    public function testHasAndGet(): void
    {
        $container = new Container\ArrayContainer([]);
        self::assertFalse($container->has(UserImporter::class));
        $container = new Container\ArrayContainer([UserImporter::class => new UserImporter()]);
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
        $container = new Container\ArrayContainer([]);
        $container->get(UserImporter::class);
    }
}
