<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerInterface;

/**
 * A very basic implementation of the PSR-11 spec.
 *
 * The ArrayContainer will take a simple array of services keyed by a name (or number, if you like)
 * and the get() and has() methods will just retrieve the services from that static array.
 *
 * Useful for unit-tests.
 *
 * @package Tale\Di\Container
 */
final class ArrayContainer implements ContainerInterface
{
    /**
     * @var array Our array of services keyed by name.
     */
    private $values;

    /**
     * Creates a new ArrayContainer.
     *
     * @param array $values The services or values keyed by name
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->values)) {
            throw new NotFoundException("{$id} was not found in container");
        }
        return $this->values[$id];
    }

    /**
     * {@inheritDoc}
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->values);
    }
}
