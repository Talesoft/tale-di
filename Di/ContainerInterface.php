<?php

namespace Tale\Di;

interface ContainerInterface
{

    //TODO: Loosen getDependencies() and getDependency() to not rely directly on Dependency
    /** @return Dependency[] */
    public function getDependencies();
    public function getDependency($className, $reverse = true);
    public function get($className, $reverse = true);
    public function register($className, $persistent = true, $instance = null);
    public function registerInstance($instance);
}