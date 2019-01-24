<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}