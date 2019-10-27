<?php declare(strict_types=1);

namespace Tale;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Tale\Di\Container;
use Tale\Di\Container\ArrayContainer;
use Tale\Di\Container\NullContainer;
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

/**
 * Creates a new dependency injection container.
 *
 * Pass an array of name-keyed dependency instances you can then retrieve the values from.
 *
 * @see DependencyInterface
 *
 * @param array $dependencies An array of dependency instances, keyed by the dependency name.
 *
 * @return ContainerInterface The created container.
 */
function di_container(array $dependencies): ContainerInterface
{
    return new Container($dependencies);
}

/**
 * Creates an array-based dependency injection container.
 *
 * The container will just resolve the values contained in the array.
 *
 * @see ArrayContainer
 *
 * @param array $values The values to resolve with the container.
 *
 * @return ContainerInterface The container.
 */
function di_container_array(array $values): ContainerInterface
{
    return new ArrayContainer($values);
}

/**
 * Creates a new Null-Container that can be used as a default implementation.
 *
 * @see NullContainer
 *
 * @return ContainerInterface A container that has no services.
 */
function di_container_null(): ContainerInterface
{
    return new NullContainer();
}

/**
 * Creates a new container builder on which you can register and locate services.
 *
 * It will create a fully wired PSR-11 dependency container.
 *
 * @see ContainerBuilder
 *
 * @param CacheItemPoolInterface|null $cachePool The PSR-6 cache item pool to cache auto-wiring info with.
 * @param string $cacheKey The cache key to cache auto-wiring information under.
 * @param TypeInfoFactoryInterface|null $typeInfoFactory The type info factory to generate type info with.
 * @param ParameterReaderInterface|null $parameterReader The parameter reader to read constructor parameters with.
 *
 * @return ContainerBuilderInterface The container builder.
 */
function di_container_builder(
    CacheItemPoolInterface $cachePool = null,
    string $cacheKey = ContainerBuilder::CACHE_KEY,
    TypeInfoFactoryInterface $typeInfoFactory = null,
    ParameterReaderInterface $parameterReader = null
): ContainerBuilderInterface {
    return new ContainerBuilder($cachePool, $cacheKey, $typeInfoFactory, $parameterReader);
}

/**
 * Creates a new File service locator.
 *
 * @see FileServiceLocator
 *
 * @param string $path The file path to locate a class name in.
 *
 * @return ServiceLocatorInterface The service locator.
 */
function di_service_locator_file(string $path): ServiceLocatorInterface
{
    return new FileServiceLocator($path);
}

/**
 * Creates a new DirectoryServiceLocator.
 *
 * @see DirectoryServiceLocator
 *
 * @param string $directory The directory to locate class names in.
 *
 * @return ServiceLocatorInterface The service locator.
 */
function di_service_locator_directory(string $directory): ServiceLocatorInterface
{
    return new DirectoryServiceLocator($directory);
}

/**
 * Creates a new GlobServiceLocator.
 *
 * @see GlobServiceLocator
 *
 * @param string $pattern The glob pattern to locate class names in.
 * @param string|null $excludePattern The exclude glob pattern of files to ignore.
 *
 * @return ServiceLocatorInterface The service locator.
 */
function di_service_locator_glob(string $pattern, ?string $excludePattern = null): ServiceLocatorInterface
{
    return new GlobServiceLocator($pattern, $excludePattern);
}

/**
 * Creates a new ValueDependency.
 *
 * @see ValueDependency
 *
 * @param $value The value the dependency contains.
 *
 * @return DependencyInterface The dependency.
 */
function di_dependency_value($value): DependencyInterface
{
    return new ValueDependency($value);
}

/**
 * Creates a new ReferenceDependency.
 *
 * @see ReferenceDependency
 *
 * @param string $id The service ID to reference.
 *
 * @return DependencyInterface The dependency.
 */
function di_dependency_reference(string $id): DependencyInterface
{
    return new ReferenceDependency($id);
}

/**
 * Creates a new CallbackDependency.
 *
 * @see CallbackDependency
 *
 * @param callable $callback The callback that resolves to the dependency value.
 * @return DependencyInterface The dependency.
 */
function di_dependency_callback(callable $callback): DependencyInterface
{
    return new CallbackDependency($callback);
}

/**
 * Creates a new PersistentCallbackDependency.
 *
 * @see PersistentCallbackDependency
 *
 * @param callable $callback The callback that resolves to the dependency value.
 * @return DependencyInterface The dependency.
 */
function di_dependency_persistent_callback(callable $callback): DependencyInterface
{
    return new PersistentCallbackDependency($callback);
}

/**
 * Creates a new persistent type info factory.
 *
 * @see PersistentTypeInfoFactory
 *
 * @return TypeInfoFactoryInterface The type info factory.
 */
function di_type_info_factory_persistent(): TypeInfoFactoryInterface
{
    return new PersistentTypeInfoFactory();
}

/**
 * Creates a new Doc Comment parameter reader.
 *
 * @see DocCommentParameterReader
 *
 * @param TypeInfoFactoryInterface|null $typeInfoFactory The type info factory to use.
 *
 * @return ParameterReaderInterface The parameter reader.
 */
function di_parameter_reader_doc_comment(TypeInfoFactoryInterface $typeInfoFactory = null): ParameterReaderInterface
{
    return new DocCommentParameterReader($typeInfoFactory);
}
