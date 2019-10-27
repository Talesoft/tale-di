<?php declare(strict_types=1);

namespace Tale\Di\ServiceLocator;

use Tale\Di\ServiceLocatorInterface;

/**
 * The DirectoryServiceLocator will recursively load a whole directory of class names available.
 *
 * @package Tale\Di\ServiceLocator
 */
final class DirectoryServiceLocator implements ServiceLocatorInterface
{
    /**
     * @var string The directory to find class names in.
     */
    private $directory;

    /**
     * Creates a new DirectoryServiceLocator.
     *
     * @param string $directory The directory to locate classes in.
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(): iterable
    {
        $files = scandir($this->directory, SCANDIR_SORT_NONE);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = "{$this->directory}/{$file}";
            if (is_dir($fullPath)) {
                // We iterate instead of using yield from because yield from resets keys
                // and will break iterator_to_array when not using use_keys on it
                $dirClassNames = (new self($fullPath))->locate();
                foreach ($dirClassNames as $dirClassName) {
                    yield $dirClassName;
                }
                continue;
            }
            // We iterate instead of using yield from because yield from resets keys
            // and will break iterator_to_array when not using use_keys on it
            $fileClassNames = (new FileServiceLocator($fullPath))->locate();
            foreach ($fileClassNames as $fileClassName) {
                yield $fileClassName;
            }
        }
    }
}
