<?php

declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

/**
 * A ReferenceDependency references a service in a container.
 *
 * It will always just forward everything to the actual service, but can be kept under an own name.
 * This is useful to alias services.
 *
 * @package Tale\Di\Dependency
 */
final class ReferenceDependency implements DependencyInterface, \Serializable
{
    /**
     * @var string The name of the service to reference.
     */
    private $id;
    /**
     * @var bool Whether this value is resolved or not.
     */
    private $resolved = false;
    /**
     * @var mixed The resolved value, if it was resolved already.
     */
    private $resolvedValue;

    /**
     * Creates a new ReferenceDependency.
     *
     * @param string $id The name of the referenced service.
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Retrieves the referenced service from the container.
     *
     * It will cache the retrieved value once it was resolved.
     *
     * @param ContainerInterface $container The container to retrieve the reference from.
     * @return mixed The retrieved reference.
     */
    public function get(ContainerInterface $container)
    {
        if ($this->resolved === true) {
            return $this->resolvedValue;
        }
        $this->resolvedValue = $container->get($this->id);
        $this->resolved = true;
        return $this->resolvedValue;
    }

    /**
     * Serializes this dependency to a PHP serialization string.
     *
     * @return string The serialized string.
     */
    public function serialize(): string
    {
        return serialize($this->id);
    }

    /**
     * Unserializes this dependency from a serialized string.
     *
     * @param string $serialized The serialized string.
     */
    public function unserialize($serialized): void
    {
        $this->id = unserialize($serialized, ['allowed_classes' => false]);
    }
}
