<?php

declare(strict_types=1);

namespace Tale\Test\Di;

use PHPUnit\Framework\TestCase;
use Tale\Di\TypeInfo;

/**
 * @coversDefaultClass \Tale\Di\TypeInfo
 */
class TypeInfoTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     */
    public function testGetName(): void
    {
        self::assertSame('test name', (new TypeInfo('test name', TypeInfo::KIND_CLASS_NAME))->getName());
    }

    /**
     * @covers ::__construct
     * @covers ::getKind
     */
    public function testGetKind(): void
    {
        self::assertSame(TypeInfo::KIND_CLASS_NAME, (new TypeInfo('', TypeInfo::KIND_CLASS_NAME))->getKind());
    }

    /**
     * @covers ::__construct
     * @covers ::isBuiltIn
     */
    public function testIsBuiltIn(): void
    {
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_CLASS_NAME))->isBuiltIn());
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_GENERIC))->isBuiltIn());
        self::assertTrue((new TypeInfo('', TypeInfo::KIND_BUILT_IN))->isBuiltIn());
    }

    /**
     * @covers ::__construct
     * @covers ::isGeneric
     */
    public function testIsGeneric(): void
    {
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_CLASS_NAME))->isGeneric());
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_BUILT_IN))->isGeneric());
        self::assertTrue((new TypeInfo('', TypeInfo::KIND_GENERIC))->isGeneric());
    }

    /**
     * @covers ::__construct
     * @covers ::isClassName
     */
    public function testIsClassName(): void
    {
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_BUILT_IN))->isClassName());
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_GENERIC))->isClassName());
        self::assertTrue((new TypeInfo('', TypeInfo::KIND_CLASS_NAME))->isClassName());
    }

    /**
     * @covers ::__construct
     * @covers ::isNullable
     */
    public function testIsNullable(): void
    {
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_BUILT_IN))->isNullable());
        self::assertFalse((new TypeInfo('', TypeInfo::KIND_BUILT_IN, false))->isNullable());
        self::assertTrue((new TypeInfo('', TypeInfo::KIND_BUILT_IN, true))->isNullable());
    }

    /**
     * @covers ::__construct
     * @covers ::getGenericType
     */
    public function testGetGenericType(): void
    {
        $genericType = new TypeInfo('', TypeInfo::KIND_CLASS_NAME);
        self::assertNull((new TypeInfo('', TypeInfo::KIND_BUILT_IN))->getGenericType());
        self::assertNull((new TypeInfo('', TypeInfo::KIND_BUILT_IN, false, null))->getGenericType());
        self::assertEquals(
            $genericType,
            (new TypeInfo('', TypeInfo::KIND_BUILT_IN, false, $genericType))
                ->getGenericType()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getGenericParameterTypes
     */
    public function testGetGenericParameterTypes(): void
    {
        self::assertCount(0, (new TypeInfo('', TypeInfo::KIND_BUILT_IN))->getGenericParameterTypes());
        $types = [new TypeInfo('', TypeInfo::KIND_CLASS_NAME), new TypeInfo('', TypeInfo::KIND_BUILT_IN)];
        $info = new TypeInfo('', TypeInfo::KIND_GENERIC, false, new TypeInfo('', TypeInfo::KIND_BUILT_IN), $types);
        self::assertSame($types, $info->getGenericParameterTypes());
    }

    /**
     * @covers ::__construct
     * @covers ::serialize
     */
    public function testSerialize(): void
    {
        $types = [new TypeInfo('TestClass', TypeInfo::KIND_CLASS_NAME), new TypeInfo('int', TypeInfo::KIND_BUILT_IN)];
        $info = new TypeInfo(
            'array<TestClass, int>',
            TypeInfo::KIND_GENERIC,
            false,
            new TypeInfo('', TypeInfo::KIND_BUILT_IN),
            $types
        );
        $serialized = 'C:16:"Tale\Di\TypeInfo":337:{a:4:{i:0;s:21:"array<TestClass, int>";i:1;s:7:"generic";i:2;C:16:' .
            '"Tale\Di\TypeInfo":52:{a:4:{i:0;s:0:"";i:1;s:8:"built_in";i:2;N;i:3;a:0:{}}}i:3;a:2:{i:0;C:16:"Tale\Di\T' .
            'ypeInfo":64:{a:4:{i:0;s:9:"TestClass";i:1;s:10:"class_name";i:2;N;i:3;a:0:{}}}i:1;C:16:"Tale\Di\TypeInfo' .
            '":55:{a:4:{i:0;s:3:"int";i:1;s:8:"built_in";i:2;N;i:3;a:0:{}}}}}}';
        self::assertSame($serialized, serialize($info));
    }

    /**
     * @covers ::__construct
     * @covers ::unserialize
     */
    public function testUnserialize(): void
    {
        $types = [new TypeInfo('TestClass', TypeInfo::KIND_CLASS_NAME), new TypeInfo('int', TypeInfo::KIND_BUILT_IN)];
        $info = new TypeInfo(
            'array<TestClass, int>',
            TypeInfo::KIND_GENERIC,
            false,
            new TypeInfo('', TypeInfo::KIND_BUILT_IN),
            $types
        );
        $serialized = 'C:16:"Tale\Di\TypeInfo":337:{a:4:{i:0;s:21:"array<TestClass, int>";i:1;s:7:"generic";i:2;C:16:' .
            '"Tale\Di\TypeInfo":52:{a:4:{i:0;s:0:"";i:1;s:8:"built_in";i:2;N;i:3;a:0:{}}}i:3;a:2:{i:0;C:16:"Tale\Di\T' .
            'ypeInfo":64:{a:4:{i:0;s:9:"TestClass";i:1;s:10:"class_name";i:2;N;i:3;a:0:{}}}i:1;C:16:"Tale\Di\TypeInfo' .
            '":55:{a:4:{i:0;s:3:"int";i:1;s:8:"built_in";i:2;N;i:3;a:0:{}}}}}}';
        self::assertEquals($info, unserialize($serialized));
    }
}
