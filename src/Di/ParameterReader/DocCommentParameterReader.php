<?php declare(strict_types=1);

namespace Tale\Di\ParameterReader;

use Tale\Di\Parameter;
use Tale\Di\ParameterReaderInterface;
use Tale\Di\TypeInfo;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;
use Tale\Di\TypeInfoFactoryInterface;
use Tale\Di\TypeInfoInterface;

final class DocCommentParameterReader implements ParameterReaderInterface
{
    /** @var TypeInfoFactoryInterface */
    private $typeInfoFactory;

    /**
     * DocBlockParameterReader constructor.
     * @param TypeInfoFactoryInterface $typeInfoFactory
     */
    public function __construct(TypeInfoFactoryInterface $typeInfoFactory = null)
    {
        $this->typeInfoFactory = $typeInfoFactory ?? new PersistentTypeInfoFactory();
    }

    public function read(\ReflectionMethod $method): iterable
    {
        $docBlockParams = iterator_to_array($this->parseDocCommentParams($method));
        $params = [];
        $reflParams = $method->getParameters();
        foreach ($reflParams as $param) {
            $name = $param->getName();
            $reflType = $param->getType();
            $type = $reflType ? $this->typeInfoFactory->getTypeInfo($reflType->getName()) : null;
            $docBlockType = $docBlockParams[$name] ?? null;
            $type = $docBlockType ?? $type;
            if (!$type) {
                //$className = $method->getDeclaringClass()->getName();
                //throw new \RuntimeException("Failed to determine a type for parameter {$name} of {$className}");
                $type = $this->typeInfoFactory->getTypeInfo('any');
            }
            $defaultValue = null;
            if ($param->isOptional()) {
                try {
                    $defaultValue = $param->getDefaultValue();
                } catch (\ReflectionException $ex) {
                }
            }

            yield $name => new Parameter($name, $type, $param->isOptional(), $defaultValue);
        }
        return $params;
    }

    private function parseDocCommentParams(\ReflectionMethod $method): \Generator
    {
        $docBlock = $method->getDocComment();
        if (!is_string($docBlock)) {
            return;
        }
        if (!preg_match_all('/@param\s+(\S+)\$(\S+)/', $docBlock, $matches)) {
            return;
        }

        foreach ($matches as $match) {
            [, $types, $name] = $match;
            /** @var TypeInfo[] $types */
            $types = array_map(function ($type) {
                return $this->typeInfoFactory->getTypeInfo($type);
            }, array_values(array_filter(array_map('trim', explode('|', $types)))));

            $typeCount = count($types);
            if ($typeCount === 0) {
                continue;
            }

            if ($typeCount === 1) {
                yield $name => $types[0];
                continue;
            }

            //Determine which type makes the most sense to keep
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
                && $builtInType->getName() === 'null') {
                /** @var TypeInfoInterface $type */
                $type = $genericType ?? $classNameType;
                yield $name => $this->typeInfoFactory->getTypeInfo("?{$type->getName()}");
                continue;
            }

            yield $name => $genericType ?? ($classNameType ?? $builtInType);
        }
    }
}