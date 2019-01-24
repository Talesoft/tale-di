<?php declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;

interface ContainerBuilderInterface
{
    public const CLASS_NAME_ALL = '*';

    public function setParameter(string $name, $value, string $className = self::CLASS_NAME_ALL): void;
    public function setParameters(iterable $parameters, string $className = self::CLASS_NAME_ALL): void;
    public function add(string $className): void;
    public function addInstance($instance): void;
    public function addLocator(ServiceLocatorInterface $locator): void;
    public function build(): ContainerInterface;
}