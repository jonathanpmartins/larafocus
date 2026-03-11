<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Shared\InvalidDtoException;

/** @phpstan-type NfseCancelRequestArray array{justificativa: string} */
readonly class NfseCancelRequest
{
    public function __construct(
        public string $justificativa,
    ) {
        $length = mb_strlen($this->justificativa);

        if ($length < 15 || $length > 255) {
            throw new InvalidDtoException('justificativa must be between 15 and 255 characters.');
        }
    }

    /** @phpstan-param NfseCancelRequestArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            justificativa: $data['justificativa'],
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'justificativa' => $this->justificativa,
        ];
    }
}
