<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Arg extends Setter
{

    private $_optional;

    /**
     * Arg constructor.
     *
     * @param            $name
     * @param            $className
     * @param bool       $optional
     * @param Dependency $value
     */
    public function __construct($name, $className, $optional, Dependency $value = null)
    {

        parent::__construct($name, $className, $value);

        $this->_optional = $optional;
    }

    public function isOptional()
    {

        return $this->_optional;
    }

    public function serialize()
    {

        return serialize([
            'optional' => $this->_optional,
            'parent' => parent::serialize()
        ]);
    }

    public function unserialize($serialized)
    {

        $values = unserialize($serialized);
        $this->_optional = $values['optional'];
        parent::unserialize($values['parent']);
    }
}