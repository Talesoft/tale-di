<?php

declare(strict_types=1);

namespace Tale\Test\Di\ParameterReader;

use PHPUnit\Framework\TestCase;
use Tale\Di\Parameter;
use Tale\Di\ParameterReader\DocCommentParameterReader;
use Tale\Di\TypeInfo;
use Tale\Test\Di\TestClasses\PartiallyDocumentedMultiParameterTest;
use Tale\Test\Di\TestClasses\Service\ImporterInterface;
use Tale\Test\Di\TestClasses\Service\ImportManager;
use Tale\Test\Di\TestClasses\UndocumentedMultiParameterTest;

/**
 * @coversDefaultClass \Tale\Di\ParameterReader\DocCommentParameterReader
 */
class DocCommentParameterReaderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::read
     *
     * @covers ::parseDocCommentParams
     */
    public function testReadWithGenerics(): void
    {
        $reader = new DocCommentParameterReader();
        $params = iterator_to_array($reader->read(new \ReflectionMethod(ImportManager::class, '__construct')));
        $expectedParams = [
            'importerArray' => new Parameter(
                'importerArray',
                new TypeInfo(
                    'array<Tale\Test\Di\TestClasses\Service\ImporterInterface>',
                    TypeInfo::KIND_GENERIC,
                    false,
                    new TypeInfo('array', TypeInfo::KIND_BUILT_IN, false),
                    [new TypeInfo(ImporterInterface::class, TypeInfo::KIND_CLASS_NAME)]
                ),
                false,
                null
            ),
            'importerIterable' => new Parameter(
                'importerIterable',
                new TypeInfo(
                    'iterable<Tale\Test\Di\TestClasses\Service\ImporterInterface>',
                    TypeInfo::KIND_GENERIC,
                    false,
                    new TypeInfo('iterable', TypeInfo::KIND_BUILT_IN, false),
                    [new TypeInfo(ImporterInterface::class, TypeInfo::KIND_CLASS_NAME)]
                ),
                false,
                null
            ),
        ];
        self::assertEquals($expectedParams, $params);
    }

    /**
     * @covers ::__construct
     * @covers ::read
     *
     * @covers ::parseDocCommentParams
     */
    public function testReadOnUndocumentedMethod(): void
    {
        $reader = new DocCommentParameterReader();
        $method = new \ReflectionMethod(UndocumentedMultiParameterTest::class, '__construct');
        $params = iterator_to_array($reader->read($method));
        $expectedParams = [
            'stringValue' => new Parameter(
                'stringValue',
                new TypeInfo('string', TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
            'intValue' => new Parameter('intValue', new TypeInfo('int', TypeInfo::KIND_BUILT_IN, false), false, null),
            'floatValue' => new Parameter(
                'floatValue',
                new TypeInfo('float', TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
            'arrayValue' => new Parameter(
                'arrayValue',
                new TypeInfo('array', TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
        ];
        self::assertEquals($expectedParams, $params);
    }

    /**
     * @covers ::__construct
     * @covers ::read
     *
     * @covers ::parseDocCommentParams
     */
    public function testReadOnPartiallyDocumentedMethod(): void
    {
        $reader = new DocCommentParameterReader();
        $method = new \ReflectionMethod(PartiallyDocumentedMultiParameterTest::class, '__construct');
        $params = iterator_to_array($reader->read($method));
        $expectedParams = [
            'stringValue' => new Parameter(
                'stringValue',
                new TypeInfo('string', TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
            'intValue' => new Parameter('intValue', new TypeInfo('int', TypeInfo::KIND_BUILT_IN, false), false, null),
            'floatValue' => new Parameter(
                'floatValue',
                new TypeInfo('float', TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
            'arrayValue' => new Parameter(
                'arrayValue',
                new TypeInfo(TypeInfo::NAME_ANY, TypeInfo::KIND_BUILT_IN, false),
                false,
                null
            ),
        ];
        self::assertEquals($expectedParams, $params);
    }
}
