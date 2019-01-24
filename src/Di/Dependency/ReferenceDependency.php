<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Tale\Di\DependencyInterface;

final class ReferenceDependency implements DependencyInterface, \Serializable
{
    private $id;
    private $loaded = false;
    private $value;

    /**
     * CallbackDependency constructor.
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function get(ContainerInterface $container)
    {
        if ($this->loaded === true) {
            return $this->value;
        }
        $this->value = $container->get($this->id);
        $this->loaded = true;
        return $this->value;
    }

    public function serialize(): string
    {
        return serialize($this->id);
    }

    public function unserialize($serialized): void
    {
        $this->id = unserialize($serialized, ['allowed_classes' => false]);
    }
}