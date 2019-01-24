<?php declare(strict_types=1);

namespace Tale\Di;

final class Parameter implements \Serializable
{
    /** @var string */
    private $name;

    /** @var TypeInfoInterface */
    private $typeInfo;

    /** @var bool */
    private $optional;

    /** @var mixed */
    private $defaultValue;

    /**
     * Parameter constructor.
     * @param string $name
     * @param TypeInfoInterface $typeInfo
     * @param bool $optional
     * @param mixed $defaultValue
     */
    public function __construct(string $name, TypeInfoInterface $typeInfo, bool $optional, $defaultValue)
    {
        $this->name = $name;
        $this->typeInfo = $typeInfo;
        $this->optional = $optional;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return TypeInfoInterface
     */
    public function getTypeInfo(): TypeInfoInterface
    {
        return $this->typeInfo;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function serialize(): string
    {
        return serialize([
            $this->name,
            $this->typeInfo,
            $this->optional,
            $this->defaultValue
        ]);
    }

    public function unserialize($serialized): void
    {
        [
            $this->name,
            $this->typeInfo,
            $this->optional,
            $this->defaultValue
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }
}