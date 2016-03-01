<?php

namespace Tale\Di;

use Tale\Di\Dependency\Arg;
use Tale\Di\Dependency\Setter;
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
    private $_className;

    /**
     * @var bool
     */
    private $_persistent;

    /**
     * @var object
     */
    private $_instance;

    /**
     * @var Arg[]
     */
    private $_args;

    /**
     * @var Setter[]
     */
    private $_setters;

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

        $this->_className = $className;
        $this->_persistent = $persistent;
        $this->_instance = $instance;
        $this->_args = [];
        $this->_setters = [];
    }

    /**
     * @return $this
     */
    public function analyze()
    {

        $ref = new \ReflectionClass($this->_className);

        if (!$ref->isInstantiable())
            throw new \RuntimeException(
                "Failed to analyze dependency: {$this->_className} ".
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
                        "Failed to analyze dependency: {$this->_className} ".
                        "Constructor argument $name needs a type-hint with ".
                        "a class or needs to be removed"
                    );

                $this->_args[$name] = new Arg(
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

            $this->_setters[$name] = new Setter(
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
        return $this->_className;
    }

    /**
     * @return Arg[]
     */
    public function getArgs()
    {
        return $this->_args;
    }

    /**
     * @return Setter[]
     */
    public function getSetters()
    {
        return $this->_setters;
    }

    /**
     * @param                     $name
     * @param \Tale\Di\Dependency $value
     *
     * @return $this
     */
    public function set($name, Dependency $value)
    {

        if (!isset($this->_args[$name]))
            throw new \RuntimeException(
                "Failed to set arg $name: ".
                "{$this->_className} has no constructor argument called $name"
            );

        $this->_args[$name]->setValue($value);
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

        if (!isset($this->_setters[$name]))
            throw new \RuntimeException(
                "Failed to set call to setter $name: ".
                "{$this->_className} has no setter called $name "
            );

        $this->_setters[$name]->setValue($value);
        return $this;
    }

    /**
     * @return null|object
     * @throws \Exception
     * @throws \Tale\FactoryException
     */
    public function getInstance()
    {

        if ($this->_persistent && $this->_instance !== null)
            return $this->_instance;

        $args = [];
        foreach ($this->_args as $name => $arg) {

            $value = $arg->getValue();

            if ($value === null && !$arg->isOptional())
                throw new \Exception(
                    "Failed to create instance of {$this->_className}: ".
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

        foreach ($this->_setters as $name => $setter) {

            $value = $setter->getValue();

            if ($value !== null)
                call_user_func([$instance, $setter->getName()], $value->getInstance());
        }

        if ($this->_persistent)
            $this->_instance = $instance;

        return $instance;
    }

    /**
     * @param Arg[] $args
     * @param ContainerInterface $container
     *
     * @return $this
     */
    private function _wireArgs(array $args, ContainerInterface $container)
    {

        foreach ($args as $arg) {

            $className = $arg->getClassName();
            $dep = $container->findDependency($className);

            if ($dep)
                $arg->setValue($dep);
        }

        return $this;
    }

    /**
     * @param \Tale\Di\ContainerInterface $container
     */
    public function wire(ContainerInterface $container)
    {

        $this->_wireArgs($this->_args, $container);
        $this->_wireArgs($this->_setters, $container);
    }

    public function serialize()
    {
        return serialize([
            $this->_className,
            $this->_persistent,
            $this->_args,
            $this->_setters
        ]);
    }

    public function unserialize($serialized)
    {

        list(
            $this->_className,
            $this->_persistent,
            $this->_args,
            $this->_setters
        ) = unserialize($serialized);
    }
}