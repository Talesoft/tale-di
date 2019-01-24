<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerInterface;
use Tale\NotFoundException;

final class ArrayContainer implements ContainerInterface
{
    /** @var array */
    private $instances;

    public function __construct(array $instances)
    {
        $this->instances = $instances;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("{$id} was not found in container");
        }
        return $this->instances[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->instances);
    }
}