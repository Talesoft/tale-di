<?php

namespace Tale\Di\Dependency;

use Tale\Di\Dependency;

class Setter implements \Serializable
{

    private $_name;
    private $_className;
    private $_value;

    public function __construct($name, $className, Dependency $value = null)
    {

        $this->_name = $name;
        $this->_className = $className;
        $this->_value = null;

        if ($value)
            $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return null
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * @return Dependency
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param Dependency $value
     *
     * @return Setter
     */
    public function setValue(Dependency $value)
    {

        if (!is_a($value->getClassName(), $this->_className, true))
            throw new \RuntimeException(
                "Failed to set `{$this->_name}`-value: ".
                "Passed value `".$value->getClassName()."` ".
                "is not a valid `".$this->_className."` instance"
            );

        $this->_value = $value;

        return $this;
    }

    public function serialize()
    {

        return serialize([
            'name' => $this->_name,
            'className' => $this->_className,
            'value' => $this->_value
        ]);
    }

    public function unserialize($serialized)
    {

        $data = unserialize($serialized);
        $this->_name = $data['name'];
        $this->_className = $data['className'];
        $this->_value = $data['value'];
    }
}