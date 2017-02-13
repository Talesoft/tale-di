<?php

namespace Tale\Di;

use Tale\Di\Dependency\NotFoundException;
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
     * @var Dependency[]
     */
    private $resolvedDependencies = [];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {

        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependency($className)
    {

        if (isset($this->resolvedDependencies[$className]))
            return $this->resolvedDependencies[$className];

        $result = null;
        if (isset($this->dependencies[$className])) {

            //Exact matches are found directly
            $result = $this->dependencies[$className];
        } else {

            //Traverse the dep list to find our dep
            $depClassNames = array_keys($this->dependencies);

            foreach ($depClassNames as $depClassName)
                if (is_a($depClassName, $className, true))
                    $result = $this->dependencies[$depClassName];

        }

        if (!$result)
            return null;

        $this->resolvedDependencies[$className] = $result;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function has($className)
    {

        return $this->getDependency($className) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function get($className)
    {

        if (($dep = $this->getDependency($className)) === null)
            throw new NotFoundException(
                "Failed to locate dependency $className. Register it ".
                "with \$container->register($className::class)"
            );

        return $dep->getInstance();
    }

    /**
     * {@inheritdoc}
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
        //Prepend rather than append for iteration reasons (Lastly added dependencies should be found first)
        $this->dependencies = [$className => $dep] + $this->dependencies;

        $dep->analyze()
            ->wire($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function registerInstance($instance)
    {

        return $this->register(get_class($instance), true, $instance);
    }

    /**
     * {@inheritdoc}
     */
    public function registerSelf()
    {

        return $this->registerInstance($this);
    }
    
    public function serialize()
    {
        
        return serialize([
            $this->dependencies,
            $this->resolvedDependencies
        ]);
    }

    public function unserialize($data)
    {

        list($this->dependencies, $this->resolvedDependencies) = unserialize($data);
    }
}