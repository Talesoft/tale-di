<?php

declare(strict_types=1);

namespace Tale\Test\Di\TestClasses;

final class App
{
    /** @var \DateTimeInterface */
    private $someInterface;
    /** @var string */
    private $someString;
    /** @var iterable */
    private $someInterfaces;

    /**
     * SomeClass constructor.
     * @param \DateTimeInterface $someInterface
     * @param string $someString
     * @param iterable<SomeClass> $someInterfaces
     */
    public function __construct(
        \DateTimeInterface $someInterface,
        string $someString = 'test',
        iterable $someInterfaces = null
    ) {
        $this->someInterface = $someInterface;
        $this->someString = $someString;
        $this->someInterfaces = $someInterfaces;
    }
}
