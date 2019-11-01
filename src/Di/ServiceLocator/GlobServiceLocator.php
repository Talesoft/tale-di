<?php

declare(strict_types=1);

namespace Tale\Di\ServiceLocator;

use Tale\Di\ServiceLocatorInterface;

/**
 * GlobServiceLocator takes a glob pattern and returns all classes in can find in all files that match it.
 *
 * You can optionally also pass an exclude pattern that contains files that are ignored.
 *
 * @package Tale\Di\ServiceLocator
 */
final class GlobServiceLocator implements ServiceLocatorInterface
{
    /**
     * @var string The glob pattern of files to locate class names in.
     */
    private $includePattern;
    /**
     * @var string|null The glob pattern of excluded files that we shall ignore.
     */
    private $excludePattern;

    /**
     * Creates a new GlobServiceLocator.
     *
     * @param string $pattern A glob pattern that we're locating class names in.
     * @param string|null $excludePattern A pattern of files to exclude or null to exclude nothing.
     */
    public function __construct(string $pattern, ?string $excludePattern = null)
    {
        $this->includePattern = $pattern;
        $this->excludePattern = $excludePattern;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(): iterable
    {
        $files = glob($this->includePattern, GLOB_BRACE);
        $excludedFiles = $this->excludePattern !== null ? glob($this->excludePattern, GLOB_BRACE) : [];
        foreach ($files as $file) {
            if (\in_array($file, $excludedFiles, true)) {
                continue;
            }
            // We iterate instead of using yield from because yield from resets keys
            // and will break iterator_to_array when not using use_keys on it
            $fileLocator = new FileServiceLocator($file);
            foreach ($fileLocator->locate() as $className) {
                yield $className;
            }
        }
    }
}
