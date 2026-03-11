<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http as HttpClient;

readonly class Http
{
    public function __construct(
        private int $timeout = 60,
        private ?string $environment = null,
        private ?string $token = null,
        private bool $isXml = false,
        private bool $isPdf = false,
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
        $environment = $this->resolveEnvironment();
        $token = $this->resolveToken($environment);
        $encodedToken = base64_encode($token);
        $endpoint = config()->string('larafocus.'.$environment.'.endpoint').config()->string('larafocus.prefix');

        $pendingRequest = HttpClient::withToken($encodedToken, 'Basic')
            ->baseUrl($endpoint)
            ->timeout($this->timeout);

        if ($this->isXml) {
            return $pendingRequest->contentType('application/xml')
                ->accept('application/xml');
        }

        if ($this->isPdf) {
            return $pendingRequest->contentType('application/pdf')
                ->accept('application/pdf');
        }

        return $pendingRequest->contentType('application/json')
            ->acceptJson();
    }

    private function resolveEnvironment(): string
    {
        return $this->environment ?: config()->string('larafocus.environment');
    }

    private function resolveToken(string $environment): string
    {
        return $this->token ?: config()->string('larafocus.'.$environment.'.token');
    }
}
