<?php

namespace Larafocus;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http AS HttpClient;

class Http
{
    private bool $isXml = false;
    private bool $isPdf = false;
    private int $timeout = 60;
    private ?string $environment = null;
    private bool $useMasterKey = false;
    private ?string $token = null;

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

    public function get(string $uri, array $parameters = []): Response
    {
        if ($this->isXml)
        {
            $client = HttpClient::focusXml(
                environment: $this->environment,
                // useMasterKey: $this->useMasterKey,
                token: $this->token,
            )->timeout($this->timeout);
        }
        else if ($this->isPdf)
        {
            $client = HttpClient::focusPdf(
                environment: $this->environment,
                // useMasterKey: $this->useMasterKey,
                token: $this->token,
            )->timeout($this->timeout);
        }
        else
        {
            $client = HttpClient::focus(
                environment: $this->environment,
                useMasterKey: $this->useMasterKey,
                token: $this->token,
            )->timeout($this->timeout);
        }

        return $client->get($uri, $parameters);
    }

    public function post(string $uri, array $parameters = []): Response
    {
        return HttpClient::focus(
            environment: $this->environment,
            useMasterKey: $this->useMasterKey,
            token: $this->token,
        )
            ->timeout($this->timeout)
            ->post($uri, $parameters);
    }

    public function patch(string $uri, array $parameters = []): Response
    {
        return HttpClient::focus(
            environment: $this->environment,
            useMasterKey: $this->useMasterKey,
            token: $this->token,
        )
            ->timeout($this->timeout)
            ->patch($uri, $parameters);
    }

    public function delete(string $uri, array $parameters = []): Response
    {
        return HttpClient::focus(
            environment: $this->environment,
            useMasterKey: $this->useMasterKey,
            token: $this->token,
        )
            ->timeout($this->timeout)
            ->delete($uri, $parameters);
    }
}
