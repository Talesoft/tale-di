<?php declare(strict_types=1);

namespace Tale;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Tale\Di\Container;
use Tale\Di\ContainerBuilder;
use Tale\Di\ContainerBuilderInterface;
use Tale\Di\Dependency\CallbackDependency;
use Tale\Di\Dependency\PersistentCallbackDependency;
use Tale\Di\Dependency\ReferenceDependency;
use Tale\Di\Dependency\ValueDependency;
use Tale\Di\DependencyInterface;
use Tale\Di\ParameterReader\DocCommentParameterReader;
use Tale\Di\ParameterReaderInterface;
use Tale\Di\ServiceLocator\DirectoryServiceLocator;
use Tale\Di\ServiceLocator\FileServiceLocator;
use Tale\Di\ServiceLocator\GlobServiceLocator;
use Tale\Di\ServiceLocatorInterface;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;
use Tale\Di\TypeInfoFactoryInterface;

function di_container(array $dependencies): ContainerInterface
{
    return new Container($dependencies);
}

function di_container_array(array $values): ContainerInterface
{
    return new Container\ArrayContainer($values);
}

function di_container_null(): ContainerInterface
{
    return new Container\NullContainer();
}

function di_container_builder(
    CacheItemPoolInterface $cachePool = null,
    TypeInfoFactoryInterface $typeInfoFactory = null,
    ParameterReaderInterface $parameterReader = null
): ContainerBuilderInterface {
    return new ContainerBuilder($cachePool, $typeInfoFactory, $parameterReader);
}

function di_service_locator_file(string $path): ServiceLocatorInterface
{
    return new FileServiceLocator($path);
}

function di_service_locator_directory(string $directory): ServiceLocatorInterface
{
    return new DirectoryServiceLocator($directory);
}

function di_service_locator_glob(string $pattern, ?string $excludePattern = null): ServiceLocatorInterface
{
    return new GlobServiceLocator($pattern, $excludePattern);
}

function di_dependency_value($value): DependencyInterface
{
    return new ValueDependency($value);
}

function di_dependency_reference(string $id): DependencyInterface
{
    return new ReferenceDependency($id);
}

function di_dependency_callback(callable $callback): DependencyInterface
{
    return new CallbackDependency($callback);
}

function di_dependency_persistent_callback(callable $callback): DependencyInterface
{
    return new PersistentCallbackDependency($callback);
}

function di_type_info_factory_persistent(): TypeInfoFactoryInterface
{
    return new PersistentTypeInfoFactory();
}

function di_parameter_reader_doc_comment(TypeInfoFactoryInterface $typeInfoFactory = null): ParameterReaderInterface
{
    return new DocCommentParameterReader($typeInfoFactory);
}