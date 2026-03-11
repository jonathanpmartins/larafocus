<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Concerns;

use Larafocus\Shared\InvalidDtoException;

trait ValidatesConstraints
{
    private static function validatePattern(string $field, string $value, string $pattern): void
    {
        if (preg_match($pattern, $value) !== 1) {
            throw new InvalidDtoException(sprintf('%s does not match pattern %s.', $field, $pattern));
        }
    }

    private static function validateMaxLength(string $field, string $value, int $max): void
    {
        if (mb_strlen($value) > $max) {
            throw new InvalidDtoException(sprintf('%s must not exceed %d characters.', $field, $max));
        }
    }

    private static function validateExactLength(string $field, string $value, int $length): void
    {
        if (mb_strlen($value) !== $length) {
            throw new InvalidDtoException(sprintf('%s must be exactly %d characters.', $field, $length));
        }
    }
}
