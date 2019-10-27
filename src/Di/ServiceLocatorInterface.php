<?php declare(strict_types=1);

namespace Tale\Di;

/**
 * Describes an implementation that can retrieve class names to auto-wire from somewhere.
 *
 * @package Tale\Di
 */
interface ServiceLocatorInterface
{
    /**
     * Locates the class names wherever this locator may find them.
     *
     * @return iterable An iterable of class names.
     */
    public function locate(): iterable;
}
