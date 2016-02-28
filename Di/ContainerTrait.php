<?php

namespace Tale\Di;

/**
 * Class ContainerTrait
 * @package Tale\Di
 */
trait ContainerTrait
{

    /**
     * The dependencies registered on this container.
     *
     * @var Dependency[]
     */
    private $_dependencies = [];

    /**
     * A cache for faster resolving of dependencies.
     *
     * @var Dependency[]
     */
    private $_dependencyFindCache = [];

    /**
     * Returns all dependencies registered on this container.
     *
     * @return Dependency[]
     */
    public function getDependencies()
    {
        return $this->_dependencies;
    }

    /**
     * Finds a dependency based on its class-name.
     *
     * @param $className
     *
     * @return Dependency|null
     */
    public function findDependency($className)
    {

        if (isset($this->_dependencyFindCache[$className]))
            return $this->_dependencyFindCache[$className];

        $result = null;
        $i = count($this->_dependencies);
        while ($i--) {

            $dep = $this->_dependencies[$i];
            if (is_a($dep->getClassName(), $className, true)) {

                $result = $dep;
                break;
            }
        }

        $this->_dependencyFindCache[$className] = $result;

        return $result;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function has($className)
    {

        return $this->findDependency($className) !== null;
    }

    /**
     * @param $className
     *
     * @return null|object
     * @throws \Exception
     */
    public function get($className)
    {

        $dep = $this->findDependency($className);

        if (!$dep)
            throw new \RuntimeException(
                "Failed to locate dependency $className. Register it ".
                "with Container->register"
            );

        return $dep->getInstance();
    }

    /**
     * @param      $className
     * @param bool $persistent
     *
     * @return $this
     */
    public function register($className, $persistent = true)
    {

        if (!($this instanceof ContainerInterface))
            throw new \RuntimeException(
                "Failed to register dependency: ".get_class($this)." uses ".
                ContainerTrait::class.", but doesnt implement ".ContainerInterface::class
            );

        if ($this->has($className))
            throw new \RuntimeException(
                "Failed to register dependency $className: This dependency ".
                "is already registered. Use a sub-class to avoid ambiguity"
            );

        /** @var ContainerInterface|ContainerTrait $this */
        $dep = new Dependency($className, $persistent);
        $this->_dependencies[] = $dep;

        //TODO: ->analyze is the workhorse, this should be cached somehow
        $dep->analyze()
            ->wire($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function registerContainer()
    {

        if (!($this instanceof ContainerInterface))
            throw new \RuntimeException(
                "Failed to register container: ".get_class($this)." uses ".
                ContainerTrait::class.", but doesnt implement ".ContainerInterface::class
            );

        $this->_dependencies[] = new Dependency(get_class($this), true, null, null, $this);

        return $this;
    }
}