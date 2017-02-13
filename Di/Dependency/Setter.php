<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Setter implements \Serializable
{
    use ArgumentTrait;

    public function __construct($name, $className, Dependency $value = null)
    {

        $this->name = $name;
        $this->className = $className;
        $this->value = null;

        if ($value)
            $this->setValue($value);
    }

    public function serialize()
    {

        return serialize([
            $this->name,
            $this->className,
            $this->value
        ]);
    }

    public function unserialize($serialized)
    {

        list($this->name, $this->className, $this->value) = unserialize($serialized);
    }
}