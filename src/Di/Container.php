<?php

declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;
use Tale\Di\Container\NotFoundException;

/**
 * The normal Tale DI Container is a container that will use DependencyInterface instances to manage instances.
 *
 * @see DependencyInterface
 *
 * @package Tale\Di
 */
final class Container implements ContainerInterface
{
    /**
     * @var DependencyInterface[] An array of all registered dependencies, keyed by name.
     */
    private $dependencies;

    /**
     * Creates a new Container.
     *
     * @param DependencyInterface[] $dependencies The dependencies you can load from this container.
     */
    public function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->dependencies)) {
            throw new NotFoundException("{$id} was not found in container");
        }
        return $this->dependencies[$id]->get($this);
    }

    /**
     * {@inheritDoc}
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->dependencies);
    }
}
