<?php

namespace Tale\Di;

interface ContainerInterface
{

    public function findDependency($className);
    public function hasDependency($className);
    public function getDependency($className);
    public function registerDependency($className, $persistent = true);
    public function registerDependencyInstance($instance);
}