<?php

namespace Tale\Di;

trait ContainerTrait
{

    /**
     * @var Dependency[]
     */
    private $_dependencies = [];
    private $_dependencyFindCache = [];

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

    public function has($className)
    {

        return $this->findDependency($className) !== null;
    }

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

    public function register($className, $persistent = true)
    {

        if (!($this instanceof ContainerInterface))
            throw new \RuntimeException(
                "Failed to register dependency: ".get_class($this)." uses ".
                ContainerTrait::class.", but doesnt implement ".ContainerInterface::class
            );

        /** @var ContainerInterface|ContainerTrait $this */
        $dep = new Dependency($className, $persistent);
        $this->_dependencies[] = $dep;

        //TODO: ->analyze is the workhorse, this should be cached somehow
        $dep->analyze()
            ->wire($this);

        return $this;
    }

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