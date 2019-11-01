<?php

declare(strict_types=1);

namespace Tale\Test\Di;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tale\Di\ContainerBuilder;
use Tale\Di\ServiceLocator\DirectoryServiceLocator;
use Tale\Test\Di\TestClasses\MultiParameterTest;
use Tale\Test\Di\TestClasses\ParameterTest;
use Tale\Test\Di\TestClasses\Service\Importer\UserImporter;
use Tale\Test\Di\TestClasses\Service\ImporterInterface;
use Tale\Test\Di\TestClasses\Service\ImportManager;

/**
 * @coversDefaultClass \Tale\Di\ContainerBuilder
 */
class ContainerBuilderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::setParameter
     */
    public function testSetParameter(): void
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('stringValue', 'some value');
        $builder->add(ParameterTest::class);
        $container = $builder->build();
        $instance = $container->get(ParameterTest::class);
        self::assertInstanceOf(ParameterTest::class, $instance);
        self::assertSame('some value', $instance->getStringValue());
    }

    /**
     * @covers ::__construct
     * @covers ::setParameter
     */
    public function testSetParameterWithDifferentClass(): void
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('stringValue', 'some value');
        $builder->setParameter('stringValue', 'some other value', DateTimeImmutable::class);
        $builder->add(ParameterTest::class);
        $container = $builder->build();
        $instance = $container->get(ParameterTest::class);
        self::assertSame('some value', $instance->getStringValue());
    }

    /**
     * @covers ::__construct
     * @covers ::setParameter
     */
    public function testSetParameterWithSpecificClass(): void
    {
        $builder = new ContainerBuilder();
        $builder->setParameter('stringValue', 'some value');
        $builder->setParameter('stringValue', 'some other value', ParameterTest::class);
        $builder->add(ParameterTest::class);
        $container = $builder->build();
        $instance = $container->get(ParameterTest::class);
        self::assertSame('some other value', $instance->getStringValue());
    }

    /**
     * @covers ::__construct
     * @covers ::setParameters
     */
    public function testSetParameters(): void
    {
        $builder = new ContainerBuilder();
        $builder->setParameters(
            [
                'stringValue' => 'some value',
                'intValue' => 14,
                'floatValue' => 12.2,
                'arrayValue' => [1, 2, 3],
            ]
        );
        $builder->add(MultiParameterTest::class);
        $container = $builder->build();
        $instance = $container->get(MultiParameterTest::class);
        self::assertSame('some value', $instance->getStringValue());
        self::assertSame(14, $instance->getIntValue());
        self::assertSame(12.2, $instance->getFloatValue());
        self::assertSame([1, 2, 3], $instance->getArrayValue());
    }

    /**
     * @covers ::__construct
     * @covers ::build
     *
     * @covers ::buildService
     * @covers ::buildServices
     * @covers ::locateClasses
     * @covers ::getClassNameTypes
     */
    public function testBuild(): void
    {
        $builder = new ContainerBuilder();
        $locator = new DirectoryServiceLocator(__DIR__ . '/TestClasses/Service');
        $builder->addLocator($locator);
        $container = $builder->build();
        $userImporter = $container->get(UserImporter::class);
        self::assertInstanceOf(UserImporter::class, $userImporter);
        $anyImporter = $container->get(ImporterInterface::class);
// This can be any importer, it's usually the last one added
        self::assertInstanceOf(ImporterInterface::class, $userImporter);
        $importers = $container->get('array<Tale\\Test\\Di\\TestClasses\\Service\\ImporterInterface>');
        self::assertCount(4, $importers);
        foreach ($importers as $importer) {
            self::assertInstanceOf(ImporterInterface::class, $importer);
        }
        $manager = $container->get(ImportManager::class);
        self::assertInstanceOf(ImportManager::class, $manager);
        $importers = $manager->getImporterArray();
        self::assertCount(4, $importers);
        foreach ($importers as $importer) {
            self::assertInstanceOf(ImporterInterface::class, $importer);
        }
        $importers = iterator_to_array($manager->getImporterIterable());
        self::assertCount(4, $importers);
        foreach ($importers as $importer) {
            self::assertInstanceOf(ImporterInterface::class, $importer);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::build
     */
    public function testBuildWhenParameterCouldNotBeWired(): void
    {
        $this->expectException(RuntimeException::class);
        $builder = new ContainerBuilder();
        $builder->add(ParameterTest::class);
        $builder->build();
    }
}
