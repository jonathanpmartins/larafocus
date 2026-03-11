<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\Response;

readonly class FocusResponse
{
    /**
     * @param  array<string, mixed>  $body
     * @param  list<array{codigo?: string, mensagem?: string, campo?: string}>  $errors
     */
    public function __construct(
        public int $statusCode,
        public bool $success,
        public array $body,
        public array $errors,
        public Response $response,
    ) {}

    public static function fromResponse(Response $response): self
    {
        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        return new self(
            statusCode: $response->status(),
            success: $response->successful(),
            body: $body,
            errors: self::extractErrors($body),
            response: $response,
        );
    }

    /**
     * @param  array<string, mixed>  $body
     * @return list<array{codigo?: string, mensagem?: string, campo?: string}>
     */
    private static function extractErrors(array $body): array
    {
        if (isset($body['erros']) && is_array($body['erros'])) {
            /** @var list<array{codigo?: string, mensagem?: string, campo?: string}> */
            return array_values(array_filter($body['erros'], is_array(...)));
        }

        if (isset($body['codigo'], $body['mensagem']) && is_string($body['codigo']) && is_string($body['mensagem'])) {
            return [['codigo' => $body['codigo'], 'mensagem' => $body['mensagem']]];
        }

        return [];
    }
}
