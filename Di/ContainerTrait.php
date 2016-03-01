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

        //Exact matches are found directly
        if (isset($this->_dependencies[$className]))
            return $this->_dependencies[$className];

        //After that we search for a sub-class
        $keys = array_reverse(array_keys($this->_dependencies));
        foreach ($keys as $key) {

            $dep = $this->_dependencies[$key];
            if (is_a($dep->getClassName(), $className, true))
                return $dep;
        }

        return null;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    public function hasDependency($className)
    {

        return $this->findDependency($className) !== null;
    }

    /**
     * @param $className
     *
     * @return null|object
     * @throws \Exception
     */
    public function getDependency($className)
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
     * @param        $className
     * @param bool   $persistent
     * @param object $instance
     *
     * @return $this
     */
    public function registerDependency($className, $persistent = true, $instance = null)
    {

        if (!($this instanceof ContainerInterface))
            throw new \RuntimeException(
                "Failed to register dependency: ".get_class($this)." uses ".
                ContainerTrait::class.", but doesnt implement ".ContainerInterface::class
            );

        if (isset($this->_dependencies[$className]))
            throw new \RuntimeException(
                "Failed to register dependency $className: A dependency ".
                "of this type is already registered. Use a sub-class to ".
                "avoid ambiguity"
            );

        /** @var ContainerInterface|ContainerTrait $this */
        $dep = new Dependency($className, $persistent, $instance);
        $this->_dependencies[$className] = $dep;

        //TODO: ->analyze is the workhorse, this should be cached somehow
        $dep->analyze()
            ->wire($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function registerDependencyInstance($instance)
    {

        return $this->registerDependency(get_class($instance), true, $instance);
    }
}