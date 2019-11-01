<?php

declare(strict_types=1);

namespace Tale\Di;

/**
 * A parameter describes basic parameter information of a function.
 *
 * This is mostly a DTO to store the information of classes constructor parameters.
 *
 * @package Tale\Di
 */
final class Parameter implements \Serializable
{
    /**
     * @var string The name of the parameter without the dollar sign ($).
     */
    private $name;
    /**
     * @see TypeInfoFactoryInterface
     *
     * @var TypeInfoInterface The type information of the parameter.
     */
    private $typeInfo;
    /**
     * @var bool Whether this parameter is optional or not.
     */
    private $optional;
    /**
     * @var mixed The default value of the parameter.
     */
    private $defaultValue;

    /**
     * Creates a new Parameter instance.
     *
     * @param string $name The name of the parameter without the dollar sign. ($)
     * @param TypeInfoInterface $typeInfo The type information of the parameter.
     * @param bool $optional Whether this parameter is optional or not.
     * @param mixed $defaultValue The default value of the parameter
     */
    public function __construct(string $name, TypeInfoInterface $typeInfo, bool $optional, $defaultValue)
    {
        $this->name = $name;
        $this->typeInfo = $typeInfo;
        $this->optional = $optional;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the name of the parameter without the dollar sign ($).
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the parameter.
     *
     * @param string $name The new name of the parameter without the dollar sign ($).
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the type information of this parameter.
     *
     * @return TypeInfoInterface
     */
    public function getTypeInfo(): TypeInfoInterface
    {
        return $this->typeInfo;
    }

    /**
     * Returns whether this parameter is optional or not.
     *
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * Returns the default value of the parameter.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Serializes the parameter with the PHP serialization mechanism.
     *
     * @return string The serialized string.
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->name,
                $this->typeInfo,
                $this->optional,
                $this->defaultValue
            ]
        );
    }

    /**
     * Unserializes the parameter info from a PHP serialization string.
     *
     * @param string $serialized The serialized parameter data.
     */
    public function unserialize($serialized): void
    {
        [
            $this->name,
            $this->typeInfo,
            $this->optional,
            $this->defaultValue
        ] = unserialize(
            $serialized,
            [
                'allowed_classes' => [
                    \Serializable::class
                ]
            ]
        );
    }
}
