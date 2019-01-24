<?php declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;

interface DependencyInterface
{
    public function get(ContainerInterface $container);
}