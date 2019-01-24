<?php declare(strict_types=1);

namespace Tale\Di\ServiceLocator;

use Tale\Di\ServiceLocatorInterface;

final class DirectoryServiceLocator implements ServiceLocatorInterface
{
    /** @var string */
    private $directory;

    /**
     * FileServiceLocator constructor.
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function locate(): iterable
    {
        $files = scandir($this->directory, SCANDIR_SORT_NONE);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = "{$this->directory}/{$file}";
            if (is_dir($fullPath)) {
                yield from (new self($fullPath))->locate();
                continue;
            }
            yield from (new FileServiceLocator($file))->locate();
        }
    }
}