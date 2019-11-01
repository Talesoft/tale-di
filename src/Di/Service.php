<?php

declare(strict_types=1);

namespace Tale\Di;

/**
 * A service represents fully wired information about a class name.
 *
 * @package Tale\Di
 */
final class Service implements \Serializable
{
    /**
     * @var string The class name of the service.
     */
    private $className;
    /**
     * @var Parameter[] The constructor parameters of the service.
     */
    private $parameters;
    /**
     * @var string[] The tags (speak: interfaces) of a service.
     */
    private $tags;

    /**
     * Creates a new service instance.
     *
     * @param string $className The class name of the service.
     * @param array $tags The tags/interfaces of this service.
     * @param array $parameters The constructor parameters of this service.
     */
    public function __construct(string $className, array $tags = [], array $parameters = [])
    {
        $this->className = $className;
        $this->tags = $tags;
        $this->parameters = $parameters;
    }

    /**
     * Returns the class name of this service.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Returns the constructor parameters of this service.
     *
     * @return Parameter[] The constructor parameter array.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns the tags/interfaces of this service.
     *
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Serializes the service data to a string using PHPs serialization mechanism.
     *
     * @return string The serialized string.
     */
    public function serialize(): string
    {
        return serialize([$this->className, $this->tags, $this->parameters]);
    }

    /**
     * Unserializes the service data info from a PHP serialization string.
     *
     * @param string $serialized The serialized service data.
     */
    public function unserialize($serialized): void
    {
        [$this->className, $this->tags, $this->parameters] = unserialize(
            $serialized,
            [
                'allowed_classes' => [
                    \Serializable::class
                ]
            ]
        );
    }
}
