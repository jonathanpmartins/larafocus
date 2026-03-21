<?php

declare(strict_types=1);

namespace Larafocus\Shared;

use InvalidArgumentException;

final class TypeCast
{
    public static function toBool(string|int|bool $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function nullableBool(string|int|bool|null $value): ?bool
    {
        return $value !== null ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : null;
    }

    public static function nullableInt(string|int|null $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && ! is_numeric($value)) {
            throw new InvalidArgumentException(sprintf("Cannot cast non-numeric string '%s' to int", $value));
        }

        return (int) $value;
    }
}
