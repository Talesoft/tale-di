<?php

namespace Tale\Di;

use Tale\Di\Dependency\Arg;
use Tale\Di\Dependency\Setter;
use Tale\DiException;
use Tale\Factory;

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

                $this->args[$name] = new Arg(
                    $name,
                    $className,
                    $param->isOptional()
                );
            }
        }

        foreach ($ref->getMethods() as $method) {

            $name = $method->getName();
            if ($method->isStatic() || !$method->isPublic()
             || strlen($name) < 3 || substr($name, 0, 3) !== 'set')
                continue;

            $params = $method->getParameters();
            $className = $params[0]->getClass();

            if (!$className)
                continue;

            $className = $className->getName();

            if (count($params) !== 1 || !$className || $params[0]->isOptional())
                continue;

            $this->setters[$name] = new Setter(
                $name,
                $className
            );
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
     * @param                     $name
     * @param \Tale\Di\Dependency $value
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
     * @param                     $name
     * @param \Tale\Di\Dependency $value
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
     * @throws \Exception
     * @throws \Tale\FactoryException
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
                    "Constructor argument $name is not specified. ".
                    "Register a ".$arg->getClassName()." dependency ".
                    " or make the argument optional."
                );

            $args[] = $value !== null ? $value->getInstance() : null;
        }

        $instance = Factory::createInstance(
            $this->getClassName(),
            $args
        );

        foreach ($this->setters as $name => $setter) {

            $value = $setter->getValue();

            if ($value !== null)
                call_user_func([$instance, $setter->getName()], $value->getInstance());
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
    private function _wireSetters(array $setters, ContainerInterface $container)
    {

        foreach ($setters as $name => $arg) {

            $className = $arg->getClassName();

            if (!$container->hasDependency($className)) {

                if ($arg instanceof Arg && !$arg->isOptional())
                    throw new DiException(
                        "Failed to wire {$this->className}'s $name-argument :".
                        "The DI-container does not contain a $className dependency"
                    );

                continue;
            }

            $arg->setValue($container->getDependency($className));
        }

        return $this;
    }

    /**
     * @param \Tale\Di\ContainerInterface $container
     */
    public function wire(ContainerInterface $container)
    {

        $this->_wireSetters($this->args, $container);
        $this->_wireSetters($this->setters, $container);
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