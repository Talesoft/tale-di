<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

final class CallbackDependency implements DependencyInterface
{
    /** @var callable */
    private $callback;

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
        $callback = $this->callback;
        return $callback($container);
    }
}