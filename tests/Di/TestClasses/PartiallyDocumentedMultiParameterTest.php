<?php

declare(strict_types=1);

namespace Tale\Test\Di\TestClasses;

final class PartiallyDocumentedMultiParameterTest
{
    /**
     * @var string
     */
    private $stringValue;
    /**
     * @var int
     */
    private $intValue;
    /**
     * @var float
     */
    private $floatValue;
    /**
     * @var array
     */
    private $arrayValue;

    /**
     * Don't add more comments here!
     *
     * @param int $intValue
     * @param float $floatValue
     */
    public function __construct(string $stringValue, int $intValue, $floatValue, $arrayValue)
    {
        $this->stringValue = $stringValue;
        $this->intValue = $intValue;
        $this->floatValue = $floatValue;
        $this->arrayValue = $arrayValue;
    }

    /**
     * @return string
     */
    public function getStringValue(): string
    {
        return $this->stringValue;
    }

    /**
     * @return int
     */
    public function getIntValue(): int
    {
        return $this->intValue;
    }

    /**
     * @return float
     */
    public function getFloatValue(): float
    {
        return $this->floatValue;
    }

    /**
     * @return array
     */
    public function getArrayValue(): array
    {
        return $this->arrayValue;
    }
}
