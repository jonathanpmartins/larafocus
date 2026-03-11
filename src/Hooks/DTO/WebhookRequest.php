<?php

declare(strict_types=1);

namespace Larafocus\Hooks\DTO;

use Larafocus\Hooks\DTO\Enums\WebhookEvent;

/** @phpstan-type WebhookRequestArray array{event: string, url: string, cnpj?: string|null, cpf?: string|null, authorization?: string|null, authorization_header?: string|null} */
readonly class WebhookRequest
{
    public function __construct(
        public WebhookEvent $event,
        public string $url,
        public ?string $cnpj = null,
        public ?string $cpf = null,
        public ?string $authorization = null,
        public ?string $authorization_header = null,
    ) {}

    /** @phpstan-param WebhookRequestArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            event: WebhookEvent::from($data['event']),
            url: $data['url'],
            cnpj: $data['cnpj'] ?? null,
            cpf: $data['cpf'] ?? null,
            authorization: $data['authorization'] ?? null,
            authorization_header: $data['authorization_header'] ?? null,
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_filter([
            'event' => $this->event->value,
            'url' => $this->url,
            'cnpj' => $this->cnpj,
            'cpf' => $this->cpf,
            'authorization' => $this->authorization,
            'authorization_header' => $this->authorization_header,
        ], fn (mixed $v): bool => $v !== null);
    }
}
