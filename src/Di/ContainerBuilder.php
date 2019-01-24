<?php declare(strict_types=1);

namespace Tale\Di;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Tale\Cache\Pool\RuntimePool;
use Tale\Di\Dependency\CallbackDependency;
use Tale\Di\Dependency\LazyCallbackDependency;
use Tale\Di\Dependency\ParameterDependency;
use Tale\Di\Dependency\ValueDependency;
use Tale\Di\ParameterReader\DocCommentParameterReader;
use Tale\Di\TypeInfoFactory\PersistentTypeInfoFactory;

final class ContainerBuilder implements ContainerBuilderInterface
{
    /** @var CacheItemPoolInterface */
    private $cachePool;
    /**
     * @var TypeInfoFactoryInterface
     */
    private $typeInfoFactory;

    /** @var ParameterReaderInterface */
    private $parameterReader;

    /** @var array */
    private $parameters = [];

    /** @var string[] */
    private $registeredClassNames = [];

    /** @var object[] */
    private $registeredInstances = [];

    /** @var ClassLocatorInterface[] */
    private $classLocators;

    /**
     * ContainerBuilder constructor.
     * @param CacheItemPoolInterface $cachePool
     * @param TypeInfoFactoryInterface|null $typeInfoFactory
     * @param ParameterReaderInterface|null $parameterReader
     */
    public function __construct(
        CacheItemPoolInterface $cachePool = null,
        TypeInfoFactoryInterface $typeInfoFactory = null,
        ParameterReaderInterface $parameterReader = null
    )
    {
        $this->cachePool = $cachePool ?? new RuntimePool();
        $this->typeInfoFactory = $typeInfoFactory ?? new PersistentTypeInfoFactory();
        $this->parameterReader = $parameterReader ?? new DocCommentParameterReader($this->typeInfoFactory);
    }

    public function setParameter(string $name, $value, string $className = self::CLASS_NAME_ALL): void
    {
        if (!isset($this->parameters[$className])) {
            $this->parameters[$className] = [];
        }
        $this->parameters[$className][$name] = $value;
    }

    public function setParameters(iterable $parameters, string $className = self::CLASS_NAME_ALL): void
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value, $className);
        }
    }

    public function add(string $className): void
    {
        $this->registeredClassNames[$className] = $className;
    }

    public function addInstance($instance): void
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException('Object has to be instance of some kind');
        }
        $this->registeredInstances[get_class($instance)] = $instance;
    }

    public function addLocator(ClassLocatorInterface $locator): void
    {
        $this->classLocators[] = $locator;
    }

    /**
     * @return ContainerInterface
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function build(): ContainerInterface
    {
        return new Container($this->buildDependencies());
    }

    /**
     * @return DependencyInterface[]
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \ReflectionException
     */
    private function buildDependencies(): array
    {
        $item = $this->cachePool->getItem('tale.di.container_builder.build');
        if (!$item->isHit()) {
            $item->set(iterator_to_array($this->buildServices()));
            $this->cachePool->save($item);
        }

        /** @var Service[] $services */
        $services = $item->get();

        $tags = [];
        $dependencies = [];
        foreach ($services as $className => $service) {
            $params = [];
            if (isset($this->registeredInstances[$className])) {
                $dependencies[$className] = new ValueDependency($this->registeredInstances[$className]);
            } else {
                foreach ($service->getParameters() as $name => $param) {
                    $valueFactory = null;
                    $typeInfo = $param->getTypeInfo();

                    if ($typeInfo->isBuiltIn() && !$param->isOptional()) {
                        throw new \RuntimeException(
                            "Failed to write param {$param->getName()} of {$className}: No value found"
                        );
                    }

                    if ($typeInfo->isClassName()
                        && $param->isOptional()
                        && !$typeInfo->isNullable()
                        && $param->getDefaultValue() === null) {
                        continue;
                    }

                    if ($typeInfo->isBuiltIn() || ($param->isOptional() && $param->getDefaultValue() !== null)) {
                        $value = $param->getDefaultValue();
                        if ($value instanceof DependencyInterface && !($value instanceof \Serializable)) {
                            throw new \RuntimeException(
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

                $dependencies[$className] = new LazyCallbackDependency(
                    function (ContainerInterface $container) use ($className, $params) {
                        return new $className(...array_map(function (DependencyInterface $dep) use ($container) {
                            return $dep->get($container);
                        }, $params));
                    }
                );
            }

            foreach ($service->getTags() as $tag) {
                $dependencies[$tag] = $dependencies[$className];
                if (!isset($tags[$tag])) {
                    $tags[$tag] = [];
                }
                $tags[$tag][] = $className;
            }
        }

        //Register tags as iterable/arrays to retrieve via DocBlock annotations
        foreach ($tags as $tag => $classNames) {
            $dependencies["iterable<{$tag}>"] = new CallbackDependency(
                function (ContainerInterface $container) use ($classNames) {
                    foreach ($classNames as $className) {
                        yield $container->get($className);
                    }
                }
            );

            $dependencies["array<{$tag}>"] = new LazyCallbackDependency(
                function (ContainerInterface $container) use ($tag) {
                    return iterator_to_array($container->get("iterable<{$tag}>"));
                }
            );
        }

        return $dependencies;
    }

    /**
     * @return \Generator
     * @throws \ReflectionException
     */
    private function buildServices(): \Generator
    {
        $classNames = array_values(array_unique(array_reverse(array_merge(
            $this->registeredClassNames,
            iterator_to_array($this->locateClasses()),
            array_keys($this->registeredInstances)
        ))));

        $finishedDefinitions = [];
        foreach ($classNames as $className) {
            if (in_array($className, $finishedDefinitions, true)) {
                continue;
            }

            $service = $this->buildService($className);
            if (!$service) {
                continue;
            }
            $finishedDefinitions[] = $className;
            yield $className => $service;

            foreach ($service->getParameters() as $param) {
                $paramType = $param->getTypeInfo();
                $paramTypeName = $paramType->getName();
                if ($paramType->isClassName() && !in_array($paramTypeName, $finishedDefinitions, true)) {
                    $childService = $this->buildService($paramTypeName);
                    if (!$childService) {
                        continue;
                    }
                    yield $paramTypeName => $childService;
                }
            }
        }
    }

    /**
     * @param string $className
     * @return Service
     * @throws \ReflectionException
     */
    private function buildService(string $className): ?Service
    {
        $reflClass = new \ReflectionClass($className);
        if (!$reflClass->isInstantiable()) {
            return null;
        }

        /** @var Parameter[] $params */
        $params = [];
        if ($reflClass->hasMethod('__construct')) {
            $reflConstructor = $reflClass->getMethod('__construct');
            $parameters = $this->parameterReader->read($reflConstructor);
            $params = $parameters instanceof \Traversable ? iterator_to_array($parameters) : (array)$parameters;
        }

        $tags = $reflClass->getInterfaceNames();
        $names = array_merge($tags, [$className, '*']);
        foreach ($this->parameters as $targetClassName => $fixedParams) {
            if (!\in_array($targetClassName, $names, true)) {
                continue;
            }

            var_dump('FIXED PARAMS', $fixedParams);
            foreach ($fixedParams as $name => $value) {
                if (!isset($params[$name])) {
                    continue;
                }
                //Override parameter with defined value
                $params[$name] = new Parameter($params[$name]->getName(), $params[$name]->getTypeInfo(), true, $value);
            }
        }
        return new Service($className, $tags, $params);
    }

    private function locateClasses(): \Traversable
    {
        foreach ($this->classLocators as $classLocator) {
            yield from $classLocator->locate();
        }
    }
}