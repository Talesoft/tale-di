<?php declare(strict_types=1);

namespace Tale\Di;

interface ClassLocatorInterface
{
    public function locate(): iterable;
}