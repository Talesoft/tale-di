<?php

declare(strict_types=1);

namespace Tale\Test\Di\TestClasses\Service;

class ImportManager
{
    /**
     * @var array
     */
    private $importerArray;
    /**
     * @var iterable
     */
    private $importerIterable;

    /**
     * ImportManager constructor.
     * @param array<\Tale\Test\Di\TestClasses\Service\ImporterInterface> $importerArray
     * @param iterable<\Tale\Test\Di\TestClasses\Service\ImporterInterface> $importerIterable
     */
    public function __construct(array $importerArray, iterable $importerIterable)
    {
        $this->importerArray = $importerArray;
        $this->importerIterable = $importerIterable;
    }

    /**
     * @return array
     */
    public function getImporterArray(): array
    {
        return $this->importerArray;
    }

    /**
     * @return iterable
     */
    public function getImporterIterable(): iterable
    {
        return $this->importerIterable;
    }
}
