<?php declare(strict_types=1);

namespace Tale\Di\TypeInfoFactory;

use Tale\Di\TypeInfo;
use Tale\Di\TypeInfoInterface;
use Tale\Di\TypeInfoFactoryInterface;

final class PersistentTypeInfoFactory implements TypeInfoFactoryInterface
{
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

    /** @var TypeInfoInterface[] */
    private $typeInfos = [];

    public function getTypeInfo(string $name): TypeInfoInterface
    {
        //Map NULL to null, Xyz[] to array<Xyz> and \Some\Class to Some\Class
        $normalizedName = $name !== 'NULL'
            ? preg_replace('/^([^\[]+)\[\]$/', 'array<$1>', ltrim(trim($name), '\\'))
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
        if (preg_match('/^(\??\w+)<([^>]+)>$/', $normalizedName, $matches)) {
            $kind = TypeInfoInterface::KIND_GENERIC;
            $genericType = $this->getTypeInfo($matches[1]);
            $genericParameterTypes = array_map(function (string $type) {
                return $this->getTypeInfo($type);
            }, explode(',', $matches[2]));
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