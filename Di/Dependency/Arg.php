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
}