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
     * @param      $className
     *
     * @param bool $reverse
     *
     * @return null|Dependency
     */
    public function getDependency($className, $reverse = true)
    {

        //Exact matches are found directly
        if (isset($this->dependencies[$className]))
            return $this->dependencies[$className];
        
        $depClassNames = array_keys($this->dependencies);

        if ($reverse)
            $depClassNames = array_reverse($depClassNames);

        foreach ($depClassNames as $depClassName)
            if (is_a($depClassName, $className, true))
                return $this->dependencies[$depClassName];

        return null;
    }

    /**
     * @param      $className
     *
     * @param bool $reverse
     *
     * @return null|object
     * @throws DiException
     */
    public function get($className, $reverse = true)
    {

        if (!($dep = $this->getDependency($className, $reverse)))
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
    public function register($className, $persistent = true, $instance = null)
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

        //TODO: those two are the workhorses, but everything should be prepared for caching. Just need an elegant way...
        $dep->analyze()
            ->wire($this);

        return $this;
    }

    /**
     * @param $instance
     *
     * @return $this
     * @throws DiException
     */
    public function registerInstance($instance)
    {

        return $this->register(get_class($instance), true, $instance);
    }
    
    public function registerSelf()
    {

        return $this->registerInstance($this);
    }
}