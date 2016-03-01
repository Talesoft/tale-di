<?php

namespace Tale\Di;

use Tale\DiException;

/**
 * Class ContainerTrait
 *
 * @package Tale\Di
 */
trait ContainerTrait
{

    /**
     * The dependencies registered on this container.
     *
     * @var Dependency[]
     */
    private $dependencies = [];

    /**
     * Returns all dependencies registered on this container.
     *
     * @return Dependency[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Finds a dependency based on its class-name.
     *
     * @param $className
     *
     * @return Dependency|null
     */
    public function getDependency($className)
    {

        //Exact matches are found directly
        if (isset($this->dependencies[$className]))
            return $this->dependencies[$className];

        //After that we search for a sub-class
        $depClassNames = array_reverse(array_keys($this->dependencies));
        foreach ($depClassNames as $depClassName) {

            if (is_a($depClassName, $className, true))
                return $this->dependencies[$depClassName];
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

        return $this->getDependency($className) !== null;
    }

    /**
     * @param $className
     *
     * @return null|object
     * @throws \Exception
     */
    public function getDependencyInstance($className)
    {

        $dep = $this->getDependency($className);

        if (!$dep)
            throw new DiException(
                "Failed to locate dependency $className. Register it ".
                "with \$container->registerDependency($className::class)"
            );

        return $dep->getInstance();
    }

    /**
     * @param        $className
     * @param bool   $persistent
     * @param object $instance
     *
     * @return $this
     * @throws DiException
     */
    public function registerDependency($className, $persistent = true, $instance = null)
    {

        if (!($this instanceof ContainerInterface))
            throw new DiException(
                "Failed to register dependency: ".get_class($this)." uses ".
                ContainerTrait::class.", but doesnt implement ".ContainerInterface::class
            );

        if (isset($this->dependencies[$className]))
            throw new DiException(
                "Failed to register dependency $className: A dependency ".
                "of this type is already registered. Use a sub-class to ".
                "avoid ambiguity"
            );

        /** @var ContainerInterface|ContainerTrait $this */
        $dep = new Dependency($className, $persistent, $instance);
        $this->dependencies[$className] = $dep;

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