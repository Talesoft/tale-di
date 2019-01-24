<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerInterface;

final class NullContainer implements ContainerInterface
{
    public function get($id)
    {
        throw new NotFoundException("{$id} was not found in container");
    }

    public function has($id): bool
    {
        return false;
    }
}