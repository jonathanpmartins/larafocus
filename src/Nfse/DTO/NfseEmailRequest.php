<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Shared\InvalidDtoException;

/** @phpstan-type NfseEmailRequestArray array{emails: array<int, string>} */
readonly class NfseEmailRequest
{
    /** @param array<int, string> $emails */
    public function __construct(
        public array $emails,
    ) {
        $count = count($this->emails);

        if ($count === 0 || $count > 10) {
            throw new InvalidDtoException('emails must contain between 1 and 10 addresses.');
        }
    }

    /** @phpstan-param NfseEmailRequestArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            emails: $data['emails'],
        );
    }

    /** @return array<string, array<int, string>> */
    public function toArray(): array
    {
        return [
            'emails' => $this->emails,
        ];
    }
}
