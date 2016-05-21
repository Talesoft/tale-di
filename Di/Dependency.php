<?php

namespace Tale\Di;

use Exception;
use Tale\Di\Dependency\Arg;
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
     * @var Arg[]
     */
    private $args;

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
                "pre-defined instance need to be persistent and can't have ".
                "any args or setters."
            );

        $this->className = $className;
        $this->persistent = $persistent;
        $this->instance = $instance;
        $this->args = [];
        $this->setters = [];
    }

    /**
     * @return $this
     */
    public function analyze()
    {

        $ref = new \ReflectionClass($this->className);

        if (!$ref->isInstantiable())
            throw new \RuntimeException(
                "Failed to analyze dependency: {$this->className} ".
                "is not instantiable"
            );

        $this->args = [];
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
                    throw new \RuntimeException(
                        "Failed to analyze dependency: {$this->className} ".
                        "Constructor argument $name needs a type-hint with ".
                        "a class or needs to be removed"
                    );

                $this->args[$name] = new Arg($name, $className, $param->isOptional());
            }
        }

        foreach ($ref->getMethods() as $method) {

            $name = $method->getName();
            if ($method->isStatic() || !$method->isPublic()
                || strlen($name) < 3 || substr($name, 0, 3) !== 'set'
            )
                continue;

            $params = $method->getParameters();
            $className = $params[0]->getClass();

            if (!$className)
                continue;

            $className = $className->getName();

            if (count($params) !== 1 || !$className || $params[0]->isOptional())
                continue;

            $this->setters[$name] = new Setter($name, $className);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {

        return $this->className;
    }

    /**
     * @return Arg[]
     */
    public function getArgs()
    {

        return $this->args;
    }

    /**
     * @return Setter[]
     */
    public function getSetters()
    {

        return $this->setters;
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

        if (!isset($this->args[$name]))
            throw new \RuntimeException(
                "Failed to set arg $name: ".
                "{$this->className} has no constructor argument called $name"
            );

        $this->args[$name]->setValue($value);

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
        foreach ($this->args as $name => $arg) {

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

        $instance = Factory::createInstance($this->getClassName(), $args);

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
     * @param Arg[]              $setters
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

                if ($arg instanceof Arg && !$arg->isOptional())
                    throw new DiException(
                        "Failed to wire {$this->className}'s $name-argument :".
                        "The DI-container does not contain a $className dependency"
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

        $this->wireSetters($this->args, $container);
        $this->wireSetters($this->setters, $container);

        return $this;
    }

    public function serialize()
    {

        return serialize([
            $this->className,
            $this->persistent,
            $this->args,
            $this->setters
        ]);
    }

    public function unserialize($serialized)
    {

        list(
            $this->className,
            $this->persistent,
            $this->args,
            $this->setters
        ) = unserialize($serialized);
    }
}