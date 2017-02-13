<?php

namespace Tale\Di;

use Tale\Di\Dependency\NotFoundException;
use Tale\DiException;

/**
 * Interface ContainerInterface
 *
 * @package Tale\Di
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface, \Serializable
{

    /** @return Dependency[] */
    public function getDependencies();

    /**
     * @param string $className
     *
     * @return Dependency|null
     */
    public function getDependency($className);

    /**
     * @param string $className
     *
     * @return bool
     */
    public function has($className);

    /**
     * @param string $className
     *
     * @return object
     *
     * @throws DiException
     * @throws NotFoundException
     */
    public function get($className);

    /**
     * @param string $className
     * @param bool $persistent
     * @param object|null $instance
     *
     * @return $this
     */
    public function register($className, $persistent = true, $instance = null);

    /**
     * @param $instance
     *
     * @return $this
     */
    public function registerInstance($instance);

    /**
     * @return $this
     */
    public function registerSelf();
}