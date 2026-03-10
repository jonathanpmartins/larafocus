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
        public int $timeout = 60,
        public ?string $environment = null,
        public bool $useMasterKey = false,
        public ?string $token = null,
    ) {}

    public function timeout(int $timeoutInSeconds = 60): self
    {
        $this->timeout = $timeoutInSeconds;

        return $this;
    }

    public function environment(?string $environment = null): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function useMasterKey(bool $isTrue = true): self
    {
        $this->useMasterKey = $isTrue;

        return $this;
    }

    public function token(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getEnv(): string
    {
        return $this->environment ?: config()->string('larafocus.environment');
    }

    public function getEndpoint(): string
    {
        return config()->string('larafocus.'.$this->getEnv().'.endpoint');
    }

    public function nfse(): Nfse
    {
        return (new Nfse)
            ->useMasterKey($this->useMasterKey)
            ->token($this->token)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }

    public function nfsen(): Nfsen
    {
        return (new Nfsen)
            ->useMasterKey($this->useMasterKey)
            ->token($this->token)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }

    public function hooks(): Hooks
    {
        return (new Hooks)
            ->useMasterKey($this->useMasterKey)
            ->token($this->token)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }

    public function search(): Search
    {
        return (new Search)
            ->useMasterKey($this->useMasterKey)
            ->token($this->token)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }

    public function companies(): Companies
    {
        return (new Companies)
            ->useMasterKey($this->useMasterKey)
            ->token($this->token)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }
}
