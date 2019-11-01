<?php

declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

/**
 * A value dependency always resolves to its given value.
 *
 * It's the most simple of all dependencies.
 *
 * @package Tale\Di\Dependency
 */
final class ValueDependency implements DependencyInterface
{
    /**
     * @var mixed The dependency value.
     */
    private $value;

    /**
     * Creates a new ValueDependency.
     *
     * @param mixed $value The dependencies value.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Will return the internally stored value.
     *
     * The given container is ignored.
     *
     * @param ContainerInterface $container The DI container.
     * @return mixed The internally stored value.
     */
    public function get(ContainerInterface $container)
    {
        return $this->value;
    }
}
