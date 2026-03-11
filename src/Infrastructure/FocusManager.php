<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Larafocus\Companies;
use Larafocus\Hooks;
use Larafocus\Nfse;
use Larafocus\Nfsen;
use Larafocus\Search;

class FocusManager
{
    public function __construct(
        private int $timeout = 60,
        private ?string $environment = null,
        private ?string $token = null,
        private ?string $masterToken = null,
    ) {}

    public function setup(
        int $timeout = 60,
        ?string $environment = null,
        ?string $token = null,
        ?string $masterToken = null,
    ): self {
        $this->timeout = $timeout;
        $this->environment = $environment;
        $this->token = $token;
        $this->masterToken = $masterToken;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getMasterToken(): ?string
    {
        return $this->masterToken;
    }

    public function nfse(): Nfse
    {
        return new Nfse($this->buildConfig());
    }

    public function nfsen(): Nfsen
    {
        return new Nfsen($this->buildConfig());
    }

    public function hooks(): Hooks
    {
        return new Hooks($this->buildConfig());
    }

    public function search(): Search
    {
        return new Search($this->buildConfig());
    }

    public function companies(): Companies
    {
        return new Companies($this->buildConfig());
    }

    private function buildConfig(): HttpConfig
    {
        $environment = $this->environment ?: config()->string('larafocus.environment');

        return new HttpConfig(
            timeout: $this->timeout,
            environment: $environment,
            token: $this->token ?: config()->string('larafocus.'.$environment.'.token'),
            masterToken: $this->masterToken ?: config()->string('larafocus.master_token'),
        );
    }
}
