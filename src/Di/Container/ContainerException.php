<?php declare(strict_types=1);

namespace Tale\Di\Container;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}