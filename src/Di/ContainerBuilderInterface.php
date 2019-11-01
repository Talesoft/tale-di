<?php

declare(strict_types=1);

namespace Tale\Di;

use Psr\Container\ContainerInterface;
use Tale\Di\Dependency\CallbackDependency;
use Tale\Di\Dependency\ParameterDependency;
use Tale\Di\Dependency\PersistentCallbackDependency;
use Tale\Di\Dependency\ReferenceDependency;
use Tale\Di\Dependency\ValueDependency;
use Tale\Di\ServiceLocator\DirectoryServiceLocator;
use Tale\Di\ServiceLocator\FileServiceLocator;
use Tale\Di\ServiceLocator\GlobServiceLocator;

/**
 * The interface for a ContainerBuilder.
 *
 * @see ContainerBuilder
 *
 * @package Tale\Di
 */
interface ContainerBuilderInterface
{
    /**
     * The default cache key that ContainerBuilderInterface implementations can use.
     *
     * @see ContainerBuilderInterface::build()
     */
    public const CACHE_KEY = 'tale.di.container_builder';
    /**
     * The phrase that denotes the application of parameters on all classes when using setParameter/s.
     *
     * @see ContainerBuilderInterface::setParameter
     * @see ContainerBuilderInterface::setParameters
     */
    public const CLASS_NAME_ALL = '*';

    /**
     * Adds a new, fixed constructor parameter to the container.
     *
     * Instances created by the container will find these parameters and apply them on constructor parameters
     * with the same name.
     *
     * @param string $name The name of the parameter. Needs to be the same name as the constructor parameter.
     * @param $value The value that should be injected for that constructor parameter.
     * @param string $className The classes or interfaces to apply this parameter on. (Default: "*" (All classes))
     * @see ContainerBuilderInterface::setParameters()
     *
     */
    public function setParameter(string $name, $value, string $className = self::CLASS_NAME_ALL): void;

    /**
     * Adds a an array of fixed constructor parameters to the container.
     *
     * Instances created by the container will find these parameters and apply them on constructor parameters
     * with the same name.
     *
     * @param iterable $parameters An array/iterable of the parameters to add. Keys are the name, values are values.
     * @param string $className The classes or interfaces to apply these parameters on. (Default: "*" (All classes))
     * @see ContainerBuilderInterface::setParameter()
     *
     */
    public function setParameters(iterable $parameters, string $className = self::CLASS_NAME_ALL): void;

    /**
     * Registers a new class name for this container builder.
     *
     * Adding a class name will add it to the auto-wiring mechanism and you can retrieve
     * fully created instances for this class after building the container.
     *
     * @param string $className The class name to add to this container builder.
     */
    public function add(string $className): void;

    /**
     * Registers an additional, own dependency for the container.
     *
     * Dependencies are lazy factories, basically.
     *
     * @param string $name The name to register the dependency under (can be any name).
     * @param DependencyInterface $dependency The dependency to register.
     * @see PersistentCallbackDependency
     * @see ReferenceDependency
     * @see ValueDependency
     *
     * @see CallbackDependency
     * @see ParameterDependency
     */
    public function addDependency(string $name, DependencyInterface $dependency): void;

    /**
     * Registers a new class instance for this container builder.
     *
     * After building, you can retrieve this class instance via its class or interface names.
     *
     * @param $instance Any object instance.
     */
    public function addInstance($instance): void;

    /**
     * Registers a new class locator for this container builder.
     *
     * The class locators will locate classes in files and directories.
     *
     * @param ServiceLocatorInterface $locator A service locator instance.
     * @see DirectoryServiceLocator
     * @see FileServiceLocator
     * @see GlobServiceLocator
     *
     * @see ServiceLocatorInterface
     */
    public function addLocator(ServiceLocatorInterface $locator): void;

    /**
     * Creates the actual container instance to retrieve services from.
     *
     * It will cache the results using the given PSR-6 cache item pool.
     *
     * @return ContainerInterface
     * @see ContainerInterface
     */
    public function build(): ContainerInterface;
}
