<?php

namespace Tale\Di;

use Exception;
use Tale\Di\Dependency\Argument;
use Tale\Di\Dependency\NotFoundException;
use Tale\Di\Dependency\Setter;
use Tale\DiException;
use Tale\Factory;
use Tale\FactoryException;

/**
 * Class Dependency
 *
 * @package Tale\Di
 */
class Dependency implements \Serializable
{

    /**
     * @var string
     */
    private $className;

    /**
     * @var bool
     */
    private $persistent;

    /**
     * @var object
     */
    private $instance;

    /**
     * @var Argument[]
     */
    private $arguments;

    /**
     * @var Setter[]
     */
    private $setters;

    /**
     * Dependency constructor.
     *
     * @param string $className
     * @param bool   $persistent
     * @param object $instance
     */
    public function __construct($className, $persistent = true, $instance = null)
    {

        if (!class_exists($className))
            throw new \InvalidArgumentException(
                "Failed to create dependency: $className doesnt exist"
            );

        if ($instance && !$persistent)
            throw new \InvalidArgumentException(
                "Failed to set pre-defined instance: Dependencies with a ".
                "pre-defined instance need to be persistent (Set argument 2 to true) and can't have ".
                "any arguments or setters."
            );

        if ($instance && !is_object($instance))
            throw new \InvalidArgumentException(
                "Argument 3 passed to ".self::class."->__construct needs to be object or null, "
                .gettype($instance)." given"
            );

        $this->className = $className;
        $this->persistent = $persistent;
        $this->instance = $instance;
        $this->arguments = [];
        $this->setters = [];
    }

    /**
     * @return string
     */
    public function getClassName()
    {

        return $this->className;
    }

    /**
     * @return Argument[]
     */
    public function getArguments()
    {

        return $this->arguments;
    }

    /**
     * @return Setter[]
     */
    public function getSetters()
    {

        return $this->setters;
    }

    /**
     * @return $this
     *
     * @throws DiException
     */
    public function analyze()
    {

        $ref = new \ReflectionClass($this->className);

        if (!$ref->isInstantiable())
            throw new DiException(
                "Failed to analyze dependency: {$this->className} ".
                "is not instantiable"
            );

        $this->arguments = [];
        $this->setters = [];

        if ($ref->hasMethod('__construct')) {

            $ctor = $ref->getMethod('__construct');
            foreach ($ctor->getParameters() as $param) {

                $name = $param->getName();
                $className = $param->getClass();

                if (!$className)
                    continue;

                $className = $className->getName();

                if (!$className)
                    throw new DiException(
                        "Failed to analyze dependency: {$this->className} "
                        ."Constructor argument $name needs a type-hint with "
                        ."for a valid class name or needs to be removed"
                    );

                $this->arguments[$name] = new Argument($name, $className, $param->isOptional());
            }
        }

        foreach ($ref->getMethods() as $method) {

            $name = $method->getName();
            if ($method->isStatic() || !$method->isPublic()
                || strlen($name) < 3 || substr($name, 0, 3) !== 'set'
            )
                continue;

            $params = $method->getParameters();

            if (count($params) !== 1)
                continue;

            $className = $params[0]->getClass();

            if (!$className)
                continue;

            $className = $className->getName();

            if (!$className)
                continue;

            $this->setters[$name] = new Setter($name, $className);
        }

        return $this;
    }

    /**
     * Sets a constructor argument value by its name
     *
     * @param string $name
     * @param Dependency $value
     *
     * @return $this
     */
    public function set($name, Dependency $value)
    {

        if (!isset($this->arguments[$name]))
            throw new \RuntimeException(
                "Failed to set arg $name: ".
                "{$this->className} has no constructor argument called $name"
            );

        $this->arguments[$name]->setValue($value);

        return $this;
    }

    /**
     * Sets a setter value by the setter name
     *
     * @param string $name
     * @param Dependency  $value
     *
     * @return $this
     */
    public function call($name, Dependency $value)
    {

        if (!isset($this->setters[$name]))
            throw new \RuntimeException(
                "Failed to set call to setter $name: ".
                "{$this->className} has no setter called $name "
            );

        $this->setters[$name]->setValue($value);

        return $this;
    }

    /**
     * @return null|object
     * @throws Exception
     * @throws FactoryException
     */
    public function getInstance()
    {

        if ($this->persistent && $this->instance !== null)
            return $this->instance;

        $args = [];
        foreach ($this->arguments as $name => $arg) {

            $value = $arg->getValue();

            if ($value === null && !$arg->isOptional())
                throw new DiException(
                    "Failed to create instance of {$this->className}: ".
                    "Constructor argument $name is has not received any existing value yet. ".
                    "Register a ".$arg->getClassName()." dependency on the wired container ".
                    " or make the argument optional."
                );

            $args[] = $value !== null ? $value->getInstance() : null;
        }

        $className = $this->className;
        $instance = new $className(...$args);

        foreach ($this->setters as $name => $setter) {

            $value = $setter->getValue();

            if ($value !== null)
                $instance->$name($value->getInstance());
        }

        if ($this->persistent)
            $this->instance = $instance;

        return $instance;
    }

    /**
     * @param Argument[]         $setters
     * @param ContainerInterface $container
     *
     * @return $this
     * @throws DiException
     */
    private function wireSetters(array $setters, ContainerInterface $container)
    {

        //Wire forward (Dependencies of this instance on this instance)
        foreach ($setters as $name => $arg) {

            $className = $arg->getClassName();
            if (!($dep = $container->getDependency($className))) {

                if ($arg instanceof Argument && !$arg->isOptional())
                    throw new NotFoundException(
                        "Failed to wire {$this->className}'s $name-argument:"
                        ."The DI-container does not contain a $className dependency"
                    );

                continue;
            }

            $arg->setValue($dep);
        }

        /* TODO: Wire backward ((Persistent) dependencies receive this instance if required)???
        foreach ($container->getDependencies() as $dep) {

            foreach ($dep->getArgs() as $arg) {

                if (!$arg->getValue() && is_a($arg->getClassName(), $this->getClassName(), true))
                    $arg->setValue($this);
            }

            foreach ($dep->getSetters() as $setter) {

                if (!$setter->getValue() && is_a($setter->getClassName(), $setter->getClassName(), true))
                    $setter->setValue($this);
            }
        }
        */

        return $this;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function wire(ContainerInterface $container)
    {

        $this->wireSetters($this->arguments, $container);
        $this->wireSetters($this->setters, $container);

        return $this;
    }

    public function serialize()
    {

        return serialize([
            $this->className,
            $this->persistent,
            $this->arguments,
            $this->setters
        ]);
    }

    public function unserialize($serialized)
    {

        list(
            $this->className,
            $this->persistent,
            $this->arguments,
            $this->setters
        ) = unserialize($serialized);

        $this->instance = null;
    }
}