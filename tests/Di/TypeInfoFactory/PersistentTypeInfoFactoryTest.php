<?php

declare(strict_types=1);

namespace Tale\Test\Di\ParameterReader;

use PHPUnit\Framework\TestCase;
use Tale\Di\TypeInfo;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;
use Tale\Di\TypeInfoInterface;
use Tale\Test\Di\TestClasses\Service\ImportManager;

/**
 * @coversDefaultClass \Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory
 */
class PersistentTypeInfoFactoryTest extends TestCase
{
    /**
     * @covers ::get
     *
     * @dataProvider provideTypeStringsToTypeInfos
     * @param string $typeString
     * @param TypeInfoInterface $expectedTypeInfo
     */
    public function testGet(string $typeString, TypeInfoInterface $expectedTypeInfo): void
    {
        $factory = new PersistentTypeInfoFactory();
        self::assertEquals($expectedTypeInfo, $factory->get($typeString));
    }

    public function provideTypeStringsToTypeInfos(): array
    {
        return [
            ['null', new TypeInfo('null', TypeInfo::KIND_BUILT_IN)],
            ['array', new TypeInfo('array', TypeInfo::KIND_BUILT_IN)],
            ['int', new TypeInfo('int', TypeInfo::KIND_BUILT_IN)],
            ['float', new TypeInfo('float', TypeInfo::KIND_BUILT_IN)],
            ['string', new TypeInfo('string', TypeInfo::KIND_BUILT_IN)],
            ['?string', new TypeInfo('string', TypeInfo::KIND_BUILT_IN, true)],
            ['object', new TypeInfo('object', TypeInfo::KIND_BUILT_IN)],
            ['iterable', new TypeInfo('iterable', TypeInfo::KIND_BUILT_IN)],
            ['callable', new TypeInfo('callable', TypeInfo::KIND_BUILT_IN)],
            ['resource', new TypeInfo('resource', TypeInfo::KIND_BUILT_IN)],
            [TypeInfo::NAME_ANY, new TypeInfo(TypeInfo::NAME_ANY, TypeInfo::KIND_BUILT_IN)],
            [\stdClass::class, new TypeInfo(\stdClass::class, TypeInfo::KIND_CLASS_NAME)],
            [\DateTimeImmutable::class, new TypeInfo(\DateTimeImmutable::class, TypeInfo::KIND_CLASS_NAME)],
            [ImportManager::class, new TypeInfo(ImportManager::class, TypeInfo::KIND_CLASS_NAME)],
            [
                'array<string>',
                new TypeInfo(
                    'array<string>',
                    TypeInfo::KIND_GENERIC,
                    false,
                    new TypeInfo('array', TypeInfo::KIND_BUILT_IN),
                    [new TypeInfo('string', TypeInfo::KIND_BUILT_IN)]
                ),
            ],
            [
                '?iterable<int, \Tale\Test\Di\TestClasses\Service\ImportManager>',
                new TypeInfo(
                    'iterable<int,Tale\Test\Di\TestClasses\Service\ImportManager>',
                    TypeInfo::KIND_GENERIC,
                    true,
                    new TypeInfo('iterable', TypeInfo::KIND_BUILT_IN),
                    [
                        new TypeInfo('int', TypeInfo::KIND_BUILT_IN),
                        new TypeInfo(ImportManager::class, TypeInfo::KIND_CLASS_NAME),
                    ]
                )
            ],
        ];
    }
}
