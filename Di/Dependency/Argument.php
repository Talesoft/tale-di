<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Argument implements \Serializable
{
    use ArgumentTrait;

    private $optional;

    /**
     * Arg constructor.
     *
     * @param            $name
     * @param            $className
     * @param bool       $optional
     * @param Dependency $value
     */
    public function __construct($name, $className, $optional = false, Dependency $value = null)
    {

        $this->name = $name;
        $this->className = $className;
        $this->value = null;

        if ($value)
            $this->setValue($value);

        $this->optional = $optional;
    }

    public function isOptional()
    {

        return $this->optional;
    }

    public function serialize()
    {

        return serialize([
            $this->name,
            $this->className,
            $this->value,
            $this->optional
        ]);
    }

    public function unserialize($serialized)
    {

        list($this->name, $this->className, $this->value, $this->optional) = unserialize($serialized);
    }
}