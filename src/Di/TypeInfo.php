<?php declare(strict_types=1);

namespace Tale\Di;

final class TypeInfo implements TypeInfoInterface, \Serializable
{
    /** @var string */
    private $name;

    /** @var string */
    private $kind;
    /**
     * @var bool
     */
    private $nullable;

    private $genericType;

    private $genericParameterTypes;

    /**
     * Type constructor.
     * @param string $name
     * @param string $kind
     * @param bool $nullable
     * @param TypeInfoInterface|null $genericType
     * @param array<\Tale\Di\TypeInfoInterface> $genericParameterTypes
     */
    public function __construct(
        string $name,
        string $kind,
        bool $nullable = false,
        ?TypeInfoInterface $genericType = null,
        array $genericParameterTypes = []
    )
    {
        $this->name = $name;
        $this->kind = $kind;
        $this->nullable = $nullable;
        $this->genericType = $genericType;
        $this->genericParameterTypes = $genericParameterTypes;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return bool
     */
    public function isBuiltIn(): bool
    {
        return $this->kind === self::KIND_BUILT_IN;
    }

    /**
     * @return bool
     */
    public function isGeneric(): bool
    {
        return $this->kind === self::KIND_GENERIC;
    }

    /**
     * @return bool
     */
    public function isClassName(): bool
    {
        return $this->kind === self::KIND_CLASS_NAME;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return TypeInfo|null
     */
    public function getGenericType(): ?TypeInfoInterface
    {
        return $this->genericType;
    }

    /**
     * @return array<\Tale\Di\TypeInfoInterface>
     */
    public function getGenericParameterTypes(): array
    {
        return $this->genericParameterTypes;
    }

    public function serialize(): string
    {
        return serialize([$this->name, $this->kind, $this->genericType, $this->genericParameterTypes]);
    }

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