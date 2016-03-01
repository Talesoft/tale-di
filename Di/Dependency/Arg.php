<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Arg extends Setter
{

    private $optional;

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

        $this->optional = $optional;
    }

    public function isOptional()
    {

        return $this->optional;
    }

    public function serialize()
    {

        return serialize([
            'optional' => $this->optional,
            'parent' => parent::serialize()
        ]);
    }

    public function unserialize($serialized)
    {

        $values = unserialize($serialized);
        $this->optional = $values['optional'];
        parent::unserialize($values['parent']);
    }
}