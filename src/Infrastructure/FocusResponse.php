<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\Response;
use Larafocus\Support\BodySnippet;

readonly class FocusResponse
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{codigo?: string, mensagem?: string, campo?: string}>  $errors
     */
    public function __construct(
        public int $statusCode,
        public bool $success,
        public array $data,
        public array $errors,
        public Response $response,
    ) {}

    /**
     * Whether Focus reported the reference as non-existent (HTTP 404).
     *
     * After a timeout, an unambiguous 404 on `nfse()->get($ref)` proves the
     * document was NOT emitted, so re-emitting the same `ref` is safe.
     *
     * @api
     */
    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    public static function fromResponse(Response $response): self
    {
        /** @var array<string, mixed> $data */
        $data = $response->json() ?? [];

        return new self(
            statusCode: $response->status(),
            success: $response->successful(),
            data: $data,
            errors: self::extractErrors($data, $response),
            response: $response,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{codigo?: string, mensagem?: string, campo?: string}>
     */
    private static function extractErrors(array $data, Response $response): array
    {
        if (isset($data['erros']) && is_array($data['erros'])) {
            /** @var list<array{codigo?: string, mensagem?: string, campo?: string}> */
            return array_values(array_filter($data['erros'], is_array(...)));
        }

        if (isset($data['codigo'], $data['mensagem']) && is_string($data['codigo']) && is_string($data['mensagem'])) {
            return [['codigo' => $data['codigo'], 'mensagem' => $data['mensagem']]];
        }

        if ($data === [] && ! $response->successful()) {
            return [self::buildNonJsonError($response)];
        }

        return [];
    }

    /** @return array{codigo: string, mensagem: string} */
    private static function buildNonJsonError(Response $response): array
    {
        $snippet = BodySnippet::of($response->body());
        $statusCode = (string) $response->status();
        $statusPhrase = self::statusPhrase($response->status());

        if ($snippet === '') {
            return ['codigo' => $statusCode, 'mensagem' => $statusPhrase];
        }

        return ['codigo' => $statusCode, 'mensagem' => $statusPhrase.': '.$snippet];
    }

    private static function statusPhrase(int $statusCode): string
    {
        /** @var array<int, string> $phrases */
        $phrases = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return $phrases[$statusCode] ?? 'HTTP Error';
    }
}
