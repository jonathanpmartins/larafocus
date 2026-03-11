<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http as HttpClient;

readonly class Http
{
    public function __construct(
        private Environment $environment,
        private string $token,
        private int $timeout = 60,
        private ContentType $contentType = ContentType::Json,
    ) {}

    /** @param array<string, mixed> $parameters */
    public function get(string $uri, array $parameters = []): Response
    {
        return $this->buildClient()->get($uri, $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function post(string $uri, array $parameters = []): Response
    {
        return $this->buildClient()->post($uri, $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function patch(string $uri, array $parameters = []): Response
    {
        return $this->buildClient()->patch($uri, $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function delete(string $uri, array $parameters = []): Response
    {
        return $this->buildClient()->delete($uri, $parameters);
    }

    private function buildClient(): PendingRequest
    {
        $encodedToken = base64_encode($this->token);
        $endpoint = config()->string('larafocus.'.$this->environment->value.'.endpoint').config()->string('larafocus.prefix');

        $pendingRequest = HttpClient::withToken($encodedToken, 'Basic')
            ->baseUrl($endpoint)
            ->timeout($this->timeout);

        return $this->contentType->applyTo($pendingRequest);
    }
}
