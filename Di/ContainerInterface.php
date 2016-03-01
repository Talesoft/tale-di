<?php

namespace Tale\Di;

interface ContainerInterface
{

    public function hasDependency($className);
    public function getDependency($className);
    public function registerDependency($className, $persistent = true, $instance = null);
}