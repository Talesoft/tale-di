<?php

namespace Tale\Di;

interface ContainerAwareInterface
{

    public function getContainer();
    public function setContainer(ContainerInterface $container);
}