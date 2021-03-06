<?php

declare(strict_types=1);

namespace Tale\Di\TypeInfoFactory;

use Tale\Di\TypeInfo;
use Tale\Di\TypeInfoFactoryInterface;
use Tale\Di\TypeInfoInterface;

/**
 * The PersistentTypeInfoFactory will parse fully-qualified type names to type information.
 *
 * Once it parsed a type by name, it will always return the same information instance (runtime caching).
 *
 * @package Tale\Di\TypeInfoFactory
 */
final class PersistentTypeInfoFactory implements TypeInfoFactoryInterface
{
    /**
     * The type names that are seen as "built in".
     *
     * Notice that 'any' is seen as "built_in", makes it easier to resolve.
     */
    public const BUILT_IN_NAMES = [
        TypeInfoInterface::NAME_ANY,
        'null',
        'int',
        'float',
        'bool',
        'string',
        'array',
        'object',
        'resource',
        'callable',
        'iterable'
    ];
    /**
     * @var TypeInfoInterface[] The cached type infos.
     */
    private $typeInfos = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $name): TypeInfoInterface
    {
        $patterns = [
            // Turn array<string, int> to array<string,int>, string | null to string|null
            '/\s*/',
            // Turn Xyz[] to array<Xyz> and \Some\Class to Some\Class
            '/^([^\[]+)\[\]$/',
            // Turn array<\SomeClass> into array<SomeClass> and array<string, \SomeClass> into array<string, SomeClass>
            '/([<,]\s*)\\\\/',
        ];
        $replacements = [
            '',
            'array<$1>',
            '$1',
        ];
// Map NULL to null,
        $normalizedName = $name !== 'NULL'
            ? preg_replace($patterns, $replacements, ltrim(trim($name), '\\'))
            : 'null';
        if (isset($this->typeInfos[$normalizedName])) {
            return $this->typeInfos[$normalizedName];
        }

        $kind = TypeInfoInterface::KIND_CLASS_NAME;
        $nullable = false;
        $genericType = null;
        $genericParameterTypes = [];
        if (strpos($normalizedName, '?') === 0) {
            $normalizedName = substr($normalizedName, 1);
            $nullable = true;
        }
        if (\in_array($normalizedName, self::BUILT_IN_NAMES, true)) {
            $kind = TypeInfoInterface::KIND_BUILT_IN;
        }
        // TODO: This is a very simple algorithm, it won't read nested generics correctly!
        if (preg_match('/^(\??\w+)<([^>]+)>$/', $normalizedName, $matches)) {
            $kind = TypeInfoInterface::KIND_GENERIC;
            $genericType = $this->get($matches[1]);
            $genericParameterTypes = array_map(
                function (string $type) {

                    return $this->get($type);
                },
                explode(',', $matches[2])
            );
        }
        return $this->typeInfos[$name] = new TypeInfo(
            $normalizedName,
            $kind,
            $nullable,
            $genericType,
            $genericParameterTypes
        );
    }
}
