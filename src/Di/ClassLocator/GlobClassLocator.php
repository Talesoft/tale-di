<?php declare(strict_types=1);

namespace Tale\Di\ClassLocator;

use Tale\Di\ClassLocatorInterface;

final class GlobClassLocator implements ClassLocatorInterface
{
    /** @var string */
    private $includePattern;

    /** @var string|null */
    private $excludePattern;

    /**
     * FileServiceLocator constructor.
     * @param string $pattern
     * @param string|null $excludePattern
     */
    public function __construct(string $pattern, ?string $excludePattern = null)
    {
        $this->includePattern = $pattern;
    }

    public function locate(): iterable
    {
        $files = glob($this->includePattern, GLOB_BRACE);
        $excludedFiles = $this->excludePattern !== null ? glob($this->excludePattern, GLOB_BRACE) : [];
        foreach ($files as $file) {
            if (\in_array($file, $excludedFiles, true)) {
                continue;
            }
            yield from (new FileClassLocator($file))->locate();
        }
    }
}