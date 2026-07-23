<?php

declare(strict_types=1);

namespace Larafocus\Support;

use Illuminate\Http\Client\Response;
use Larafocus\Exceptions\IndeterminateResultException;

/**
 * Detects the two responses that arrived but whose outcome cannot be trusted, and
 * raises {@see IndeterminateResultException} for them. Every other response — including
 * definitive provider rejections (4xx/429/408) and redirects (3xx) — is left untouched
 * so the caller can surface it as a normal {@see \Larafocus\Infrastructure\FocusResponse}.
 *
 * Only meaningful for JSON responses; callers must gate non-JSON content types out.
 */
final class ResponseClassifier
{
    public static function guard(Response $response, bool $isWrite): void
    {
        $statusCode = $response->status();

        if ($isWrite && $statusCode >= 500 && ! self::hasProviderErrorEnvelope($response)) {
            throw IndeterminateResultException::fromServerError($statusCode, $response->body());
        }

        if ($response->successful() && $statusCode !== 204 && $response->json() === null) {
            throw IndeterminateResultException::fromUnreadableResponse($statusCode, $response->body());
        }
    }

    private static function hasProviderErrorEnvelope(Response $response): bool
    {
        $data = $response->json();

        return is_array($data)
            && ((isset($data['erros']) && is_array($data['erros'])) || isset($data['codigo'], $data['mensagem']));
    }
}
