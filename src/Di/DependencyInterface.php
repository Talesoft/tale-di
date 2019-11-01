<?php

declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;

/**
 * A dependency describes a lazy value, basically. That's all it is.
 *
 * Calling get() on the dependency shall resolve that lazy value.
 *
 * Consecutive calls on get() can lazily retrieve and cache the results, if required.
 *
 * @package Tale\Di
 */
interface DependencyInterface
{
    /**
     * Gets the value stored inside our dependency or lazily retrieves it (and then stores it).
     *
     * The get() call takes a ContainerInterface instance to load other, required services on demand.
     *
     * @param ContainerInterface $container The container interface for lazily loading services.
     * @return mixed The internally stored dependency value.
     */
    public function get(ContainerInterface $container);
}
