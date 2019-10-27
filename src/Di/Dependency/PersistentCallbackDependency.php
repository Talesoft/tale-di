<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

/**
 * The PersistentCallbackDependency takes a callback that resolves to our dependencies value.
 *
 * The value is cached after retrieving it once and the callback is never called again
 * for the lifetime of the dependency.
 *
 * @package Tale\Di\Dependency
 */
final class PersistentCallbackDependency implements DependencyInterface
{
    /**
     * @var callable The callback that resolves to our dependency value.
     */
    private $callback;

    /**
     * @var bool Whether this value is resolved or not.
     */
    private $resolved = false;

    /**
     * @var mixed The resolved value, if it was resolved already.
     */
    private $resolvedValue;

    /**
     * Creates a new PersistentCallbackDependency
     *
     * @param callable $callback The callback that resolves to our value.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Calls the internally stored callback to retrieve the value of this dependency.
     *
     * If the callback has already resolved once, that same result will be retrieved and
     * the callback will not be called again.
     *
     * @param ContainerInterface $container The container the callback can use to get services.
     * @return mixed The resolved value.
     */
    public function get(ContainerInterface $container)
    {
        if ($this->resolved) {
            return $this->resolvedValue;
        }
        $callback = $this->callback;
        return $this->resolvedValue = $callback($container);
    }
}
