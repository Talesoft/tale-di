<?php

namespace Tale\Di\Container;

use Tale\Di\ContainerTrait;

trait DedicatedTrait
{
    use ContainerTrait;

    public function has($className)
    {

        return $this->hasDependency($className);
    }

    public function get($className)
    {

        return $this->getDependency($className);
    }

    public function register($className, $persistent = false)
    {

        return $this->registerDependency($className, $persistent);
    }

    public function registerInstance($instance)
    {

        return $this->registerDependencyInstance($instance);
    }

    public function registerSelf()
    {

        return $this->registerInstance($this);
    }
}