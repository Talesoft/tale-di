<?php declare(strict_types=1);

namespace Tale\Di;

use ReflectionMethod;

/**
 * Represents an implementation that can read parameters from a ReflectionMethod.
 *
 * @package Tale\Di
 */
interface ParameterReaderInterface
{
    /**
     * Reads parameter information from the given ReflectionMethod.
     *
     * @param ReflectionMethod $method
     * @return iterable<\Tale\Di\Parameter>
     */
    public function read(ReflectionMethod $method): iterable;
}
