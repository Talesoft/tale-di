<?php declare(strict_types=1);

namespace Tale\Di;

interface TypeInfoInterface
{
    public const NAME_ANY = 'any';
    public const KIND_BUILT_IN = 'built_in';
    public const KIND_GENERIC = 'generic';
    public const KIND_CLASS_NAME = 'class_name';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return bool
     */
    public function isBuiltIn(): bool;

    /**
     * @return bool
     */
    public function isGeneric(): bool;

    /**
     * @return bool
     */
    public function isClassName(): bool;

    public function isNullable(): bool;

    /**
     * @return TypeInfoInterface|null
     */
    public function getGenericType(): ?self;

    /**
     * @return array<\Tale\Di\TypeInfoInterface>
     */
    public function getGenericParameterTypes(): array;
}