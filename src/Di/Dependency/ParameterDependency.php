<?php declare(strict_types=1);

namespace Tale\Di\Dependency;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tale\Di\DependencyInterface;

final class ParameterDependency implements DependencyInterface
{

    private $id;
    private $loaded = false;
    private $value;

    /**
     * @var bool
     */
    private $optional;
    /**
     * @var null
     */
    private $defaultValue;

    /**
     * CallbackDependency constructor.
     * @param string $id
     * @param bool $optional
     * @param null $defaultValue
     */
    public function __construct(string $id, bool $optional = false, $defaultValue = null)
    {
        $this->id = $id;
        $this->optional = $optional;
        $this->defaultValue = $defaultValue;
    }

    public function get(ContainerInterface $container)
    {
        if ($this->loaded === true) {
            return $this->value;
        }
        try {
            return $this->value = $container->get($this->id);
        } catch (NotFoundExceptionInterface $ex) {
            if (!$this->optional) {
                throw $ex;
            }
            return $this->value = $this->defaultValue;
        } finally {
            $this->loaded = true;
        }
    }
}