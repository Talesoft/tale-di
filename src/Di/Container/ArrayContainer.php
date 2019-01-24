<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerInterface;

final class ArrayContainer implements ContainerInterface
{
    /** @var array */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("{$id} was not found in container");
        }
        return $this->values[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->values);
    }
}