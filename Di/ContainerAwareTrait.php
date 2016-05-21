<?php

namespace Tale\Di;

trait ContainerAwareTrait
{

    protected $container = null;

    public function getContainer()
    {

        return $this->container;
    }

    public function setContainer(ContainerInterface $container)
    {

        $this->container = $container;

        return $this;
    }
}