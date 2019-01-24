<?php declare(strict_types=1);

namespace Tale\Di;

interface ParameterReaderInterface
{
    /**
     * @param \ReflectionMethod $method
     * @return iterable<\Tale\Di\Parameter>
     */
    public function read(\ReflectionMethod $method): iterable;
}