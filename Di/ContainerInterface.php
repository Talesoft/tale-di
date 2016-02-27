<?php

namespace Tale\Di;

interface ContainerInterface
{

    public function findDependency($className);
    public function get($className);
    public function register($className, $persistent = true);
}