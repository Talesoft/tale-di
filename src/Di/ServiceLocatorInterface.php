<?php declare(strict_types=1);

namespace Tale\Di;

interface ServiceLocatorInterface
{
    public function locate(): iterable;
}