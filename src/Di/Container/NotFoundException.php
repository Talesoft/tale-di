<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * An exception that occurs when container services have not been found when requesting them.
 *
 * @package Tale\Di\Container
 */
final class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
