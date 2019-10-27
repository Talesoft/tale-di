<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerExceptionInterface;

/**
 * Represents an exception that occurs when handling DI containers.
 *
 * @package Tale\Di\Container
 */
class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
