<?php declare(strict_types=1);

namespace Tale\Test\Di\TestClasses;

final class ParameterTest
{
    /**
     * @var string
     */
    private $stringValue;

    /**
     * SomeClass constructor.
     * @param string $stringValue
     */
    public function __construct(string $stringValue) {
        $this->stringValue = $stringValue;
    }

    /**
     * @return string
     */
    public function getStringValue(): string
    {
        return $this->stringValue;
    }
}
