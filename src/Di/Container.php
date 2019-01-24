<?php declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;
use Tale\Di\Container\NotFoundException;

final class Container implements ContainerInterface
{
    /** @var DependencyInterface[] */
    private $dependencies;

    /**
     * DependencyContainer constructor.
     * @param DependencyInterface[] $dependencies
     */
    public function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("{$id} was not found in container");
        }
        return $this->dependencies[$id]->get($this);
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->dependencies);
    }
}