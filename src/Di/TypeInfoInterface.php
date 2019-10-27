<?php declare(strict_types=1);

namespace Tale\Di;

interface TypeInfoInterface
{
    /**
     * Represents the "any" type which means "All Types".
     *
     * We don't use "mixed" as it's possible mixed becomes an own type in PHP in the future.
     *
     * 'any' counts as an inbuilt type.
     */
    public const NAME_ANY = 'any';

    /**
     * Represents Builtin types of PHP (array, int, float, string, object, null, callable, iterable etc.)
     */
    public const KIND_BUILT_IN = 'built_in';

    /**
     * Represents generic types with the usual format known: "BaseClass<string, SomeTypeParam>" etc.)
     */
    public const KIND_GENERIC = 'generic';

    /**
     * Represents types that are names of a class (fully-qualified class names).
     *
     * No resolution via use-statements of classes is attempted in any case.
     */
    public const KIND_CLASS_NAME = 'class_name';

    /**
     * Returns the full name of this parameter.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns one of the TypeInfoInterface constants that represents the kind of this type.
     *
     * Possible values are 'built_in', 'generic' and 'class_name'.
     *
     * @return string
     */
    public function getKind(): string;

    /**
     * Returns whether this type is builtin or not.
     *
     * @return bool
     */
    public function isBuiltIn(): bool;

    /**
     * Returns whether this type is generic or not.
     *
     * @return bool
     */
    public function isGeneric(): bool;

    /**
     * Returns whether this type is a class name or not.
     *
     * @return bool
     */
    public function isClassName(): bool;

    /**
     * Returns whether this type is nullable or not.
     *
     * @return bool
     */
    public function isNullable(): bool;

    /**
     * Returns the base type of the generic type if this is a generic.
     *
     * @return TypeInfo|null
     */
    public function getGenericType(): ?self;

    /**
     * Returns an array of generic type parameter type information.
     *
     * @return array<\Tale\Di\TypeInfoInterface>
     */
    public function getGenericParameterTypes(): array;
}
