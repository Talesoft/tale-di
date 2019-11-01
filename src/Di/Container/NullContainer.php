<?php

declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerInterface;

/**
 * Represents a container that never has a service and always returns false when asking if it does.
 *
 * Useful as a default container implementation to avoid defensive null checks, e.g. when your
 * library supports PSR-11 containers conditionally.
 *
 * Use it like this:
 *
 * ```
 * $container = $optionalContainer ?? new NullContainer();
 *
 * if ($container->has(SomeService::class)) {
 *     // Will never occur, unless you pass something in $optionalContainer
 * }
 * ```
 *
 * @package Tale\Di\Container
 */
final class NullContainer implements ContainerInterface
{
    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        throw new NotFoundException("{$id} was not found in container");
    }

    /**
     * {@inheritDoc}
     */
    public function has($id): bool
    {
        return false;
    }
}
