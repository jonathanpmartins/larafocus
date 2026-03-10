<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http as HttpClient;

class Http
{
    public function __construct(
        private bool $isXml = false,
        private bool $isPdf = false,
        private int $timeout = 60,
        private ?string $environment = null,
        private bool $useMasterKey = false,
        private ?string $token = null,
    ) {}

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function environment(?string $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    public function useMasterKey(bool $isTrue = true): static
    {
        $this->useMasterKey = $isTrue;

        return $this;
    }

    public function token(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function isXml(): static
    {
        $this->isXml = true;

        return $this;
    }

    public function isPdf(): static
    {
        $this->isPdf = true;

        return $this;
    }

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
        if ($this->token) {
            return $this->token;
        }

        if ($this->useMasterKey) {
            return config()->string('larafocus.master_token');
        }

        return config()->string('larafocus.'.$environment.'.token');
    }
}
