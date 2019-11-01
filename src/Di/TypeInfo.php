<?php

declare(strict_types=1);

namespace Tale\Di;

/**
 * TypeInfo is a basic DTO for type information.
 *
 * It can represent internal types, classes and generic types.
 *
 * @package Tale\Di
 */
final class TypeInfo implements TypeInfoInterface, \Serializable
{
    /**
     * @var string The full name of this type.
     */
    private $name;
    /**
     * @see TypeInfoInterface
     *
     * @var string One of the TypeInfoInterface constants to represent what kind of type this is.
     */
    private $kind;
    /**
     * @var bool Whether this type is nullable or not.
     */
    private $nullable;
    /**
     * @var TypeInfoInterface|null If this is a generic type, it contains the base type.
     */
    private $genericType;
    /**
     * @var array The array of the generic type parameters passed.
     */
    private $genericParameterTypes;

    /**
     * Creates a new TypeInfo instance.
     *
     * @param string $name The full name of the type.
     * @param string $kind The kind of the type (one of TypeInfoInterface's instances).
     * @param bool $nullable Whether this type is nullable or not.
     * @param TypeInfoInterface|null $genericType The generic base type of this type.
     * @param array<\Tale\Di\TypeInfoInterface> $genericParameterTypes The generic type parameters of this type.
     */
    public function __construct(
        string $name,
        string $kind,
        bool $nullable = false,
        ?TypeInfoInterface $genericType = null,
        array $genericParameterTypes = []
    ) {
        $this->name = $name;
        $this->kind = $kind;
        $this->nullable = $nullable;
        $this->genericType = $genericType;
        $this->genericParameterTypes = $genericParameterTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * {@inheritDoc}
     */
    public function isBuiltIn(): bool
    {
        return $this->kind === self::KIND_BUILT_IN;
    }

    /**
     * {@inheritDoc}
     */
    public function isGeneric(): bool
    {
        return $this->kind === self::KIND_GENERIC;
    }

    /**
     * {@inheritDoc}
     */
    public function isClassName(): bool
    {
        return $this->kind === self::KIND_CLASS_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * {@inheritDoc}
     */
    public function getGenericType(): ?TypeInfoInterface
    {
        return $this->genericType;
    }

    /**
     * {@inheritDoc}
     */
    public function getGenericParameterTypes(): array
    {
        return $this->genericParameterTypes;
    }

    /**
     * Serializes the type info to a string using PHPs serialization mechanism.
     *
     * @return string The serialized string.
     */
    public function serialize(): string
    {
        return serialize([$this->name, $this->kind, $this->genericType, $this->genericParameterTypes]);
    }

    /**
     * Unserializes the type info info from a PHP serialization string.
     *
     * @param string $serialized The serialized service data.
     */
    public function unserialize($serialized): void
    {
        [
            $this->name,
            $this->kind,
            $this->genericType,
            $this->genericParameterTypes
        ] = unserialize($serialized, ['allowed_classes' => [self::class]]);
    }
}
