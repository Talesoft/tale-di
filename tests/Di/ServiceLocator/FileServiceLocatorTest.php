<?php

declare(strict_types=1);

namespace Tale\Test\Di\ServiceLocator;

use PHPUnit\Framework\TestCase;
use Tale\Di\ServiceLocator\FileServiceLocator;
use Tale\Test\Di\TestClasses\Service\ImportManager;

/**
 * @coversDefaultClass \Tale\Di\ServiceLocator\FileServiceLocator
 */
class FileServiceLocatorTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::locate
     *
     * @covers ::readClassName
     */
    public function testLocate(): void
    {
        $locator = new FileServiceLocator(dirname(__DIR__) . '/TestClasses/Service/ImportManager.php');
        self::assertSame([ImportManager::class], iterator_to_array($locator->locate()));
    }
}
