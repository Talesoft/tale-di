<?php

declare(strict_types=1);

namespace Tale\Test\Di\ServiceLocator;

use PHPUnit\Framework\TestCase;
use Tale\Di\ServiceLocator\DirectoryServiceLocator;
use Tale\Test\Di\TestClasses\Service\Importer\AttributeImporter;
use Tale\Test\Di\TestClasses\Service\Importer\CommodityImporter;
use Tale\Test\Di\TestClasses\Service\Importer\ProductImporter;
use Tale\Test\Di\TestClasses\Service\Importer\UserImporter;
use Tale\Test\Di\TestClasses\Service\ImporterInterface;
use Tale\Test\Di\TestClasses\Service\ImportManager;

/**
 * @coversDefaultClass \Tale\Di\ServiceLocator\DirectoryServiceLocator
 */
class DirectoryServiceLocatorTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::locate
     */
    public function testLocate(): void
    {
        $locator = new DirectoryServiceLocator(dirname(__DIR__) . '/TestClasses/Service');
        self::assertSame(
            [
                AttributeImporter::class,
                CommodityImporter::class,
                ProductImporter::class,
                UserImporter::class,
                ImporterInterface::class,
                ImportManager::class
            ],
            iterator_to_array($locator->locate())
        );
    }
}
