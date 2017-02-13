<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;
use Tale\DiException;

trait ArgumentTrait
{

    private $name;
    private $className;
    private $value;

    /**
     * @return mixed
     */
    public function getName()
    {

        return $this->name;
    }

    /**
     * @return null
     */
    public function getClassName()
    {

        return $this->className;
    }

    /**
     * @return Dependency
     */
    public function getValue()
    {

        return $this->value;
    }

    /**
     * @param Dependency $value
     *
     * @return Setter
     */
    public function setValue(Dependency $value)
    {

        if (!is_a($value->getClassName(), $this->className, true))
            throw new DiException(
                "Failed to set `{$this->name}`-value: ".
                "Passed value `".$value->getClassName()."` ".
                "is not a valid `".$this->className."` instance"
            );

        $this->value = $value;

        return $this;
    }
}