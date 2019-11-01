<?php

declare(strict_types=1);

namespace Tale\Test\Di\ServiceLocator;

use PHPUnit\Framework\TestCase;
use Tale\Di\ServiceLocator\GlobServiceLocator;
use Tale\Test\Di\TestClasses\Service\Importer\AttributeImporter;
use Tale\Test\Di\TestClasses\Service\Importer\ProductImporter;

/**
 * @coversDefaultClass \Tale\Di\ServiceLocator\GlobServiceLocator
 */
class GlobServiceLocatorTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::locate
     */
    public function testLocate(): void
    {
        $include = dirname(__DIR__) .
            '/TestClasses/Service/Importer/{AttributeImporter,CommodityImporter,ProductImporter}.php';
        $locator = new GlobServiceLocator(
            $include,
            dirname(__DIR__) . '/TestClasses/Service/Importer/{CommodityImporter}.php'
        );
        self::assertSame(
            [
                AttributeImporter::class,
                ProductImporter::class,
            ],
            iterator_to_array($locator->locate())
        );
    }
}
