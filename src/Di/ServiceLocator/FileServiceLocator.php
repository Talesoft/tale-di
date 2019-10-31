<?php declare(strict_types=1);

namespace Tale\Di\ServiceLocator;

use Tale\Di\ServiceLocatorInterface;

/**
 * The FileServiceLocator locates a class in a file.
 *
 * It will use PHP tokenization to get the actual namespace and fully-qualified class name out of the file.
 *
 * @package Tale\Di\ServiceLocator
 */
final class FileServiceLocator implements ServiceLocatorInterface
{
    /**
     * @var string The file path we're locating classes in.
     */
    private $path;

    /**
     * Creates a new FileServiceLocator.
     *
     * @param string $path The file path to locate classes in.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function locate(): iterable
    {
        $className = $this->readClassName();
        if ($className !== null) {
            yield $className;
        }
    }

    /**
     * Tokenizes the file and reads token by token.
     *
     * It saves the current namespace it is in and finds the _main_ class defined in the file.
     *
     * @return string|null The class name we found or null, if we didn't find a class name
     */
    private function readClassName(): ?string
    {
        $stream = fopen($this->path, 'rb');
        $namespaceName = null;
        $className = null;
        $buffer = '';
        $bufferSize = 512;
        while (!feof($stream)) {
            $buffer .= fread($stream, $bufferSize);
            // Fix per https://github.com/octobercms/october/issues/2770
            $tokens = token_get_all('/**/' . $buffer . '/**/');
            $count = \count($tokens);
            for ($i = 0; $i < $count; $i++) {
                [$token] = $tokens[$i];
                if ($token === T_NAMESPACE && $namespaceName === null) {
                    $ns = '';
                    $valid = false;
                    for ($i++; $i < $count; $i++) {
                        [$nsToken, $nsValue] = $tokens[$i];
                        if ($nsToken === T_STRING || $nsToken === T_NS_SEPARATOR) {
                            $ns .= $nsValue;
                        }
                        if ($nsToken === null) {
                            $valid = true;
                            break;
                        }
                    }
                    if (!$valid) {
                        continue 2;
                    }
                    $namespaceName = $ns;
                    continue;
                }

                if ($token === T_CLASS || $token === T_INTERFACE) {
                    for ($i++; $i < $count; $i++) {
                        [$classToken, $classValue] = $tokens[$i];
                        if ($classToken === T_STRING) {
                            $className = $classValue;
                            break 2;
                        }
                        if ($i + 1 >= $count) {
                            continue 2;
                        }
                    }
                }
            }
        }

        if ($className !== null && $namespaceName !== null) {
            $className = "{$namespaceName}\\{$className}";
        }
        return $className;
    }
}
