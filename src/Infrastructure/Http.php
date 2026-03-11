<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http as HttpClient;
use InvalidArgumentException;

readonly class Http
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private int $timeout = 60,
        private ContentType $contentType = ContentType::Json,
    ) {
        if (trim($this->token) === '') {
            throw new InvalidArgumentException('API token must not be empty. Set your token in the LARAFOCUS_SANDBOX_TOKEN or LARAFOCUS_PRODUCTION_TOKEN environment variable.');
        }
    }

    /** @param array<string, mixed> $parameters */
    public function get(string $uri, array $parameters = []): FocusResponse
    {
        return FocusResponse::fromResponse($this->buildClient()->get($uri, $parameters));
    }

    /** @param array<string, mixed> $parameters */
    public function post(string $uri, array $parameters = []): FocusResponse
    {
        return FocusResponse::fromResponse($this->buildClient()->post($uri, $parameters));
    }

    /** @param array<string, mixed> $parameters */
    public function put(string $uri, array $parameters = []): FocusResponse
    {
        return FocusResponse::fromResponse($this->buildClient()->put($uri, $parameters));
    }

    /** @param array<string, mixed> $parameters */
    public function patch(string $uri, array $parameters = []): FocusResponse
    {
        return FocusResponse::fromResponse($this->buildClient()->patch($uri, $parameters));
    }

    /** @param array<string, mixed> $parameters */
    public function delete(string $uri, array $parameters = []): FocusResponse
    {
        return FocusResponse::fromResponse($this->buildClient()->delete($uri, $parameters));
    }

    private function buildClient(): PendingRequest
    {
        $encodedToken = base64_encode($this->token);

        $pendingRequest = HttpClient::withToken($encodedToken, 'Basic')
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout);

        return $this->contentType->applyTo($pendingRequest);
    }
}
