<?php

declare(strict_types=1);

namespace Tale\Test\Di\Container;

use PHPUnit\Framework\TestCase;
use Tale\Di\Container;
use Tale\Test\Di\TestClasses\Service\Importer\UserImporter;

/**
 * @coversDefaultClass \Tale\Di\Container\NullContainer
 */
class NullContainerTest extends TestCase
{
    /**
     * @covers ::has
     */
    public function testHas(): void
    {
        $container = new Container\NullContainer();
        self::assertFalse($container->has(UserImporter::class));
    }

    /**
     * @covers ::get
     */
    public function testGetThrowsExceptionWhenServiceNotFound(): void
    {
        $this->expectException(Container\NotFoundException::class);
        $container = new Container\NullContainer();
        $container->get(UserImporter::class);
    }
}
