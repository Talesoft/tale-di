<?php

namespace Tale\Di\Dependency;

use Psr\Container\NotFoundExceptionInterface;
use Tale\DiException;

class NotFoundException extends DiException implements NotFoundExceptionInterface
{
}