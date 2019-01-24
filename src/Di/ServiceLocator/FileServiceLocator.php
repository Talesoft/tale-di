<?php declare(strict_types=1);

namespace Tale\Di\ServiceLocator;

use Tale\Di\ServiceLocatorInterface;

final class FileServiceLocator implements ServiceLocatorInterface
{
    /** @var string */
    private $path;

    /**
     * FileServiceLocator constructor.
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function locate(): iterable
    {
        $className = $this->readClassName();
        if ($className !== null) {
            yield $className;
        }
    }

    private function readClassName(): ?string
    {
        $stream = fopen($this->path, 'rb');
        $namespaceName = null;
        $className = null;
        $buffer = '';
        while (!feof($stream)) {
            $buffer .= fread($stream, 512);
            $tokens = token_get_all($buffer);
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