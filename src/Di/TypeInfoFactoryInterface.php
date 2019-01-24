<?php declare(strict_types=1);

namespace Tale\Di;

interface TypeInfoFactoryInterface
{
    public function getTypeInfo(string $name): TypeInfoInterface;
}