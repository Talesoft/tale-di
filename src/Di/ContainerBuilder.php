<?php declare(strict_types=1);

namespace Tale\Di;

use Generator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Serializable;
use Tale\Cache\Pool\RuntimePool;
use Tale\Di\Dependency\CallbackDependency;
use Tale\Di\Dependency\PersistentCallbackDependency;
use Tale\Di\Dependency\ParameterDependency;
use Tale\Di\Dependency\ValueDependency;
use Tale\Di\ParameterReader\DocCommentParameterReader;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;
use Traversable;
use function in_array;

/**
 * The ContainerBuilder that will create a PSR-11 container instance.
 *
 * The ContainerBuilder is the heart of Tale DI. It will auto-wire classes and static parameters
 * given the things you register on it and create a cached, lazy PSR-11 compatible container out of it
 * that you can use to manage your application's service instances.
 *
 * @package Tale\Di
 */
final class ContainerBuilder implements ContainerBuilderInterface
{
    /**
     * @var CacheItemPoolInterface The PSR-6 cache item pool the container uses to cache the auto-wiring data.
     */
    private $cachePool;

    /**
     * @var string The cache key auto-wiring data is cached under.
     */
    private $cacheKey;

    /**
     * @var ParameterReaderInterface A parameter reader to read parameter information from constructors.
     */
    private $parameterReader;

    /**
     * @see ContainerBuilder::setParameter()
     * @see ContainerBuilder::setParameters()
     *
     * @var array An array of parameters registered with this container builder.
     */
    private $parameters = [];

    /**
     * @see ContainerBuilder::add()
     *
     * @var string[] An array of pre-registered class names to add to the container.
     */
    private $registeredClassNames = [];

    /**
     * @see ContainerBuilder::addDependency()
     *
     * @var DependencyInterface[] An array of dependencies keyed by name that will be added to the container when it's built.
     */
    private $registeredDependencies = [];

    /**
     * @see ContainerBuilder::addLocator()
     *
     * @var ServiceLocatorInterface[] An array of services locators that will locate classes to add to our container.
     */
    private $serviceLocators = [];

    /**
     * Creates a new ContainerBuilder.
     *
     * @param CacheItemPoolInterface|null $cachePool The PSR-6 cache item pool to cache auto-wiring info with.
     * @param string $cacheKey The cache key to cache auto-wiring information under.
     * @param TypeInfoFactoryInterface|null $typeInfoFactory The type info factory to generate type info with.
     * @param ParameterReaderInterface|null $parameterReader The parameter reader to read constructor parameters with.
     */
    public function __construct(
        CacheItemPoolInterface $cachePool = null,
        string $cacheKey = self::CACHE_KEY,
        TypeInfoFactoryInterface $typeInfoFactory = null,
        ParameterReaderInterface $parameterReader = null
    ) {
        $this->cachePool = $cachePool ?? new RuntimePool();
        $this->cacheKey = $cacheKey;
        $this->parameterReader = $parameterReader ?? new DocCommentParameterReader(
            $typeInfoFactory ?? new PersistentTypeInfoFactory()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter(string $name, $value, string $className = self::CLASS_NAME_ALL): void
    {
        if (!isset($this->parameters[$className])) {
            $this->parameters[$className] = [];
        }
        $this->parameters[$className][$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(iterable $parameters, string $className = self::CLASS_NAME_ALL): void
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value, $className);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $className): void
    {
        $this->registeredClassNames[$className] = $className;
    }

    /**
     * {@inheritDoc}
     */
    public function addDependency(string $name, DependencyInterface $dependency): void
    {
        $this->registeredDependencies[$name] = $dependency;
    }

    /**
     * {@inheritDoc}
     */
    public function addInstance($instance): void
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException('Object has to be instance of some kind');
        }
        $this->addDependency(get_class($instance), new ValueDependency($instance));
    }

    /**
     * {@inheritDoc}
     */
    public function addLocator(ServiceLocatorInterface $locator): void
    {
        $this->serviceLocators[] = $locator;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): ContainerInterface
    {
        return new Container($this->buildDependencies());
    }

    /**
     * Walks through all registered class names and instances and creates an array of dependencies of them.
     *
     * @return DependencyInterface[] An array of DependencyInterface instances that make up our final dependencies.
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function buildDependencies(): array
    {
        $item = $this->cachePool->getItem($this->cacheKey);

        // Generate and cache auto-wiring information if required
        if (!$item->isHit()) {
            $item->set(iterator_to_array($this->buildServices()));
            $this->cachePool->save($item);
        }

        /** @var Service[] $services */
        $services = $item->get();

        $tags = [];
        $dependencies = [];
        $registeredDependencies = $this->registeredDependencies;
        // Walk through all services and create dependencies out of them
        foreach ($services as $className => $service) {
            $params = [];
            if (isset($registeredDependencies[$className])) {
                // Check if we already have a registered dependency for this service. If yes, we use that one.
                $dependencies[$className] = $registeredDependencies[$className];
                unset($registeredDependencies[$className]);
            } else {
                // Build parameters and map them to dependencies to let them use the container instance
                // when the class instance is created
                foreach ($service->getParameters() as $name => $param) {
                    $valueFactory = null;
                    $typeInfo = $param->getTypeInfo();

                    if ($typeInfo->isBuiltIn() && !$param->isOptional()) {
                        throw new RuntimeException(sprintf(
                            'Failed to wire constructor parameter $%s of class %s: No value found. '.
                            'You should probably call ContainerBuilder::setParameter(\'%s\', $value, \'%s\') before '.
                            'calling build to specify the parameter.',
                            $param->getName(),
                            $className,
                            $param->getName(),
                            $className
                        ));
                    }

                    if ($typeInfo->isClassName()
                        && $param->isOptional()
                        && !$typeInfo->isNullable()
                        && $param->getDefaultValue() === null) {
                        // Let PHP errors handle it if they are not there/defined
                        continue;
                    }

                    if ($typeInfo->isBuiltIn() || ($param->isOptional() && $param->getDefaultValue() !== null)) {
                        $value = $param->getDefaultValue();
                        if ($value instanceof DependencyInterface && !($value instanceof Serializable)) {
                            throw new RuntimeException(
                                "Parameter {$param->getName()} of {$className} is not a serializable dependency"
                            );
                        }
                        $params[] = $value instanceof DependencyInterface ? $value : new ValueDependency($value);
                        continue;
                    }

                    $params[] = new ParameterDependency(
                        $typeInfo->getName(),
                        $param->isOptional(),
                        $param->isOptional() ? $param->getDefaultValue() : null
                    );
                }

                // Create the PersistentCallbackDependency for our service
                // TODO: Some kind of scope mechanism could inject services
                //       either PersistentCallback or Callback dependencies
                $dependencies[$className] = new PersistentCallbackDependency(
                    static function (ContainerInterface $container) use ($className, $params) {
                        return new $className(...array_map(static function (DependencyInterface $dep) use ($container) {
                            return $dep->get($container);
                        }, $params));
                    }
                );
            }

            // Walk through interfaces/tags of the service and register our dependency under these, too
            foreach ($service->getTags() as $tag) {
                $dependencies[$tag] = $dependencies[$className];
                if (!isset($tags[$tag])) {
                    $tags[$tag] = [];
                }
                // Also add it to our tags array to track it for iterable/array injection
                $tags[$tag][] = $className;
            }
        }

        // Register tags as iterable/arrays to retrieve via DocBlock annotations
        foreach ($tags as $tag => $classNames) {
            $dependencies["iterable<{$tag}>"] = new CallbackDependency(
                static function (ContainerInterface $container) use ($classNames) {
                    foreach ($classNames as $className) {
                        yield $container->get($className);
                    }
                }
            );

            $dependencies["array<{$tag}>"] = new PersistentCallbackDependency(
                static function (ContainerInterface $container) use ($tag) {
                    return iterator_to_array($container->get("iterable<{$tag}>"));
                }
            );
        }

        return array_replace($dependencies, $registeredDependencies);
    }

    /**
     * Generates auto-wiring data for services and will return Service instances with their information.
     *
     * @see ContainerBuilder::locateClasses()
     * @see ContainerBuilder::buildService()
     * @see Service
     *
     * @return Generator A generator of Service instances keyed by their class name.
     * @throws ReflectionException
     */
    private function buildServices(): Generator
    {
        // Loads all existing class names we registered, either from class names, instances or
        // without service locators
        $classNames = array_values(array_unique(array_reverse(array_merge(
            $this->registeredClassNames,
            iterator_to_array($this->locateClasses()),
            array_filter(array_keys($this->registeredDependencies), static function (string $className) {
                return class_exists($className);
            })
        ))));

        $finishedDefinitions = [];
        // Walk through all services to create or Service instance that contains all auto-wiring information
        foreach ($classNames as $className) {
            // Check for services that might already have been defined by our parameter location logic below
            if (in_array($className, $finishedDefinitions, true)) {
                continue;
            }

            // buildService will build our Service instance
            $service = $this->buildService($className);
            if (!$service) {
                continue;
            }

            // Register the class name as defined
            $finishedDefinitions[] = $className;

            // Yield our newly created service definition
            yield $className => $service;

            // Walk through all constructor parameters and check for classes/services we may not know yet
            // All parameter types you didn't add will be added now
            // If we requested services that come later in our class name array, the definition will be
            // prioritized and already added to finishedDefinitions before it will be iterated again later
            foreach ($service->getParameters() as $param) {
                $paramType = $param->getTypeInfo();
                foreach ($this->getClassNameTypes($paramType) as $classParamType) {
                    $paramTypeName = $classParamType->getName();
                    // Build a service definition from our parameter type
                    $childService = $this->buildService($paramTypeName);
                    if (!$childService) {
                        continue;
                    }
                    // Yield it, too
                    yield $paramTypeName => $childService;
                }
            }
        }
    }

    /**
     * Builds a single service definition from our given class name.
     *
     * This is the "auto-wiring" part of Tale DI. It will reflect the class,
     * validate it, read its constructor parameters and wire them fully.
     *
     * Parameterless constructors or constructor-less classes are handled as expected.
     *
     * @see ContainerBuilder::buildServices()
     * @see ParameterReaderInterface
     * @see Parameter
     * @see Service
     *
     * @param string $className The class name to create a service information object from.
     * @return Service The service information object created.
     * @throws ReflectionException
     */
    private function buildService(string $className): ?Service
    {
        $reflClass = new ReflectionClass($className);
        if (!$reflClass->isInstantiable()) {
            return null;
        }

        /** @var Parameter[] $params */
        $params = [];
        // Check if we have a constructor
        if ($reflClass->hasMethod('__construct')) {
            // Get the constructor's ReflectionMethod
            $reflConstructor = $reflClass->getMethod('__construct');
            // Read the parameters from this constructor (array of [$name => $param (Parameter instance)])
            $parameters = $this->parameterReader->read($reflConstructor);
            $params = $parameters instanceof Traversable ? iterator_to_array($parameters) : (array)$parameters;
        }

        // Get the interfaces of the class. These are our "tags"
        $tags = $reflClass->getInterfaceNames();
        // $names contains all classes our service will be available as, possibly
        $names = array_merge($tags, [$className, '*']);
        // Check for fixed, registered parameters in $this->parameters.
        // They will override the parameters we read above.
        foreach ($this->parameters as $targetClassName => $fixedParams) {
            if (!in_array($targetClassName, $names, true)) {
                continue;
            }

            foreach ($fixedParams as $name => $value) {
                if (!isset($params[$name])) {
                    continue;
                }
                // Override parameter with defined value
                $params[$name] = new Parameter($params[$name]->getName(), $params[$name]->getTypeInfo(), true, $value);
            }
        }
        // Return our final, finished service instance with all data
        return new Service($className, $tags, $params);
    }

    /**
     * Collects all locale() calls on the service locators we registered.
     *
     * @see ContainerBuilder::buildServices()
     *
     * @return Traversable An iterable of all located class names found.
     */
    private function locateClasses(): Traversable
    {
        foreach ($this->serviceLocators as $classLocator) {
            yield from $classLocator->locate();
        }
    }

    /**
     * Returns all class names that are used in a type.
     *
     * @param TypeInfoInterface $typeInfo The type info to retrieve class names from.
     * @return Generator A generator of class names.
     */
    private function getClassNameTypes(TypeInfoInterface $typeInfo): Generator
    {
        if ($typeInfo->isClassName()) {
            yield $typeInfo;
            return;
        }
        if ($typeInfo->isGeneric()) {
            foreach ($typeInfo->getGenericParameterTypes() as $genericParamType) {
                foreach ($this->getClassNameTypes($genericParamType) as $paramTypeInfo) {
                    yield $paramTypeInfo;
                }
            }
            return;
        }
    }
}
