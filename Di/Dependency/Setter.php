<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Setter implements \Serializable
{

    private $name;
    private $className;
    private $value;

    public function __construct($name, $className, Dependency $value = null)
    {

        $this->name = $name;
        $this->className = $className;
        $this->value = null;

        if ($value)
            $this->setValue($value);
    }

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
            throw new \RuntimeException(
                "Failed to set `{$this->name}`-value: ".
                "Passed value `".$value->getClassName()."` ".
                "is not a valid `".$this->className."` instance"
            );

        $this->value = $value;

        return $this;
    }

    public function serialize()
    {

        return serialize([
            'name' => $this->name,
            'className' => $this->className,
            'value' => $this->value
        ]);
    }

    public function unserialize($serialized)
    {

        $data = unserialize($serialized);
        $this->name = $data['name'];
        $this->className = $data['className'];
        $this->value = $data['value'];
    }
}