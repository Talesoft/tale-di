<?php declare(strict_types=1);

namespace Tale\Di;

final class Service implements \Serializable
{
    /** @var string */
    private $className;

    /** @var Parameter[] */
    private $parameters;

    /**
     * @var string[]
     */
    private $tags;

    public function __construct(string $className, array $tags = [], array $parameters = [])
    {
        $this->className = $className;
        $this->tags = $tags;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function serialize(): string
    {
        return serialize([$this->className, $this->tags, $this->parameters]);
    }

    public function unserialize($serialized): void
    {
        [$this->className, $this->tags, $this->parameters] = unserialize($serialized, ['allowed_classes' => [
            \Serializable::class
        ]]);
    }
}