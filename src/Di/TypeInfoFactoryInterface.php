<?php

declare(strict_types=1);

namespace Tale\Di;

/**
 * Represents an implementation that returns type information from type names given.
 *
 * @package Tale\Di
 */
interface TypeInfoFactoryInterface
{
    /**
     * Returns type information from the given type name.
     *
     * Supported types:
     * - All inbuilt PHP types ("int", "string", "float", "object", "array" etc.)
     * - Class names ("Some\Class\Name" etc.)
     * - Generic types ("Some\Generic<int, Generic\SubType>" etc.)
     * - Array notation ("Some\Dto[]", "string[]" etc.)
     *
     * Example for built-in types:
     * ```
     * $typeInfo = $factory->get('string');
     * $typeInfo->getKind(); // "built_in"
     * $typeInfo->isBuiltIn(); // true
     * $typeInfo->getName(); // string
     * ```
     *
     * Example for classes:
     * ```
     * $typeInfo = $factory->get(SomeClass::class);
     * $typeInfo->getKind(); // "class_name"
     * $typeInfo->isClassName(); // true
     * $typeInfo->getName(); // "SomeClass"
     * ```
     *
     * Example for generics:
     * ```
     * $typeInfo = $factory->get("array<string, SomeClass>")
     * $typeInfo->getKind(); // "generic"
     * $typeInfo->isGeneric(); // true
     * $typeInfo->getGenericType()->getKind(); // "built_in"
     * $typeInfo->getGenericType()->getName(); // "array"
     *
     * $params = $typeInfo->getGenericParameterTypes();
     * count($params); // 2
     * $params[0]->getKind(); // "built_in"
     * $params[0]->getName(); // "string"
     *
     * $params[1]->getKind(); // "class_name"
     * $params[1]->getName(); // "SomeClass"
     * ```
     *
     * @param string $name The full name of the type to retrieve information from.
     * @return TypeInfoInterface The type information generated.
     */
    public function get(string $name): TypeInfoInterface;
}
