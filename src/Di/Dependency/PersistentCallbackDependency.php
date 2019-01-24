<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

final class PersistentCallbackDependency implements DependencyInterface
{
    /** @var callable */
    private $callback;

    /** @var bool */
    private $resolved = false;

    /** @var mixed */
    private $resolvedValue;

    /**
     * CallbackDependency constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function get(ContainerInterface $container)
    {
        if ($this->resolved) {
            return $this->resolvedValue;
        }
        $callback = $this->callback;
        return $this->resolvedValue = $callback($container);
    }
}