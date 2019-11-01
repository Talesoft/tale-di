<?php

declare(strict_types=1);

namespace Tale\Di\ParameterReader;

use Tale\Di\Parameter;
use Tale\Di\ParameterReaderInterface;
use Tale\Di\TypeInfo;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;
use Tale\Di\TypeInfoFactoryInterface;
use Tale\Di\TypeInfoInterface;

/**
 * The DocCommentParameterReader can read parameter types from PHPDoc Blocks of methods.
 *
 * It will use the TypeInfoFactory to create type information from the read types.
 *
 * @see TypeInfoFactoryInterface
 *
 * @package Tale\Di\ParameterReader
 */
final class DocCommentParameterReader implements ParameterReaderInterface
{
    /**
     * @var TypeInfoFactoryInterface The TypeInfoFactory to generate type info with.
     */
    private $typeInfoFactory;

    /**
     * Creates a new DocBlockParameterReader.
     *
     * @param TypeInfoFactoryInterface $typeInfoFactory The TypeInfoFactory to generate type info with.
     */
    public function __construct(TypeInfoFactoryInterface $typeInfoFactory = null)
    {
        $this->typeInfoFactory = $typeInfoFactory ?? new PersistentTypeInfoFactory();
    }

    /**
     * Reads parameter types from a method.
     *
     * Example:
     * ```
     * class SomeClass
     * {
     *     /**
     *      * @param iterable<SomeOtherStuff> $someValue
     *      *\/
     *     public function someMethod(string $someString, SomeClass $someInstance = null, $someIterable)
     *     {
     *         // function body...
     *     }
     * }
     *
     * $reader = new DocCommentParameterReader();
     * $method = new ReflectionMethod(SomeClass::class, 'someMethod');
     * $parameters = $reader->read($method);
     *
     * var_dump($parameters);
     * /*
     * [
     *     'someString' => TypeInfo (Will tell you it's an inbuilt "string"),
     *     'someInstance' => TypeInfo (Will tell you it's a class name "SomeClass"),
     *     'someIterable' => TypeInfo (Will tell you it's a generic "iterable" of a class name "SomeOtherStuff"),
     * ]
     * *\/
     * ```
     * @param \ReflectionMethod $method
     * @return iterable<\Tale\Di\Parameter>
     */
    public function read(\ReflectionMethod $method): iterable
    {
        $docBlockParams = iterator_to_array($this->parseDocCommentParams($method));
        $params = [];
        $reflParams = $method->getParameters();
        // We merge the doc comment definitions with the normal typing
        // information that we can read via simple, normal reflection
        foreach ($reflParams as $param) {
            $name = $param->getName();
            $reflType = $param->getType();
            $type = $reflType ? $this->typeInfoFactory->get($reflType->getName()) : null;
            $docBlockType = $docBlockParams[$name] ?? null;
            $finalType = $docBlockType ?? $type;
            if (!$finalType) {
                $finalType = $this->typeInfoFactory->get(TypeInfoInterface::NAME_ANY);
            }
            $defaultValue = null;
            if ($param->isOptional()) {
                try {
                    $defaultValue = $param->getDefaultValue();
                } catch (\ReflectionException $ex) {
                }
            }

            yield $name => new Parameter($name, $finalType, $param->isOptional(), $defaultValue);
        }
        return $params;
    }

    /**
     * Reads the doc-comment of a method and parses it into an iterable of TypeInformation keyed by parameter name.
     *
     * @param \ReflectionMethod $method The reflection method to parse the docblock of.
     * @return \Generator An iterable of TypeInfo keyed by the parameter name.
     */
    private function parseDocCommentParams(\ReflectionMethod $method): \Generator
    {
        $docBlock = $method->getDocComment();
        if (!is_string($docBlock)) {
            return;
        }
        if (!preg_match_all('/@param\s+(\S+)\s+\$(\w+)/', $docBlock, $matches)) {
            return;
        }

        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++
        ) {
            $types = $matches[1][$i];
            $name = $matches[2][$i];
            /** @var TypeInfo[] $types */
            $types = array_map(
                function ($type) {

                    return $this->typeInfoFactory->get($type);
                },
                array_values(array_filter(array_map('trim', explode('|', $types))))
            );
            $typeCount = count($types);
            if ($typeCount === 0) {
                continue;
            }

            if ($typeCount === 1) {
                yield $name => $types[0];
                continue;
            }

            // Determine which type makes the most sense to keep
            $builtInType = null;
            $genericType = null;
            $classNameType = null;
            foreach ($types as $type) {
                if ($type->isBuiltIn()) {
                    $builtInType = $type;
                    continue;
                }
                if ($type->isGeneric()) {
                    $genericType = $type;
                    continue;
                }
                $classNameType = $type;
            }

            if (($genericType !== null || $classNameType !== null)
                && $builtInType !== null
                && $builtInType->getName() === 'null'
            ) {
                /** @var TypeInfoInterface $type */
                $type = $genericType ?? $classNameType;
                yield $name => $this->typeInfoFactory->get("?{$type->getName()}");
                continue;
            }

            yield $name => $genericType ?? ($classNameType ?? $builtInType);
        }
    }
}
