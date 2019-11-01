<?php

declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

/**
 * A lazy dependency that gets resolved when get() is called.
 *
 * The result will not be cached. If you need caching, use PersistentCallbackDependency.
 *
 * @package Tale\Di\Dependency
 */
final class CallbackDependency implements DependencyInterface
{
    /**
     * @var callable The callback that resolves to our dependencies value.
     */
    private $callback;

    /**
     * Creates a new CallbackDependency.
     *
     * @param callable $callback The callback that resolves our dependency value.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Calls the internally stored callback to retrieve the value of this dependency.
     *
     * @param ContainerInterface $container The container the callback can use to get services.
     * @return mixed The resolved value.
     */
    public function get(ContainerInterface $container)
    {
        $callback = $this->callback;
        return $callback($container);
    }
}
