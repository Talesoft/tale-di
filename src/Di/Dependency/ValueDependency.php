<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

final class ValueDependency implements DependencyInterface
{
    /** @var mixed */
    private $value;

    /**
     * CallbackDependency constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function get(ContainerInterface $container)
    {
        return $this->value;
    }
}