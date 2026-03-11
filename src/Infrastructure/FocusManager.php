<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Larafocus\Companies;
use Larafocus\Hooks;
use Larafocus\Nfse;
use Larafocus\Nfsen;
use Larafocus\Search;

readonly class FocusManager
{
    public function __construct(
        private int $timeout = 60,
        private ?Environment $environment = null,
        private ?string $token = null,
        private ?string $masterToken = null,
    ) {}

    public function setup(
        int $timeout = 60,
        ?Environment $environment = null,
        ?string $token = null,
        ?string $masterToken = null,
    ): self {
        return new self($timeout, $environment, $token, $masterToken);
    }

    public function nfse(): Nfse
    {
        return new Nfse($this->buildHttp());
    }

    public function nfsen(): Nfsen
    {
        return new Nfsen($this->buildHttp());
    }

    public function hooks(): Hooks
    {
        return new Hooks($this->buildHttp());
    }

    public function search(): Search
    {
        return new Search($this->buildHttp());
    }

    public function companies(): Companies
    {
        $prefix = config()->string('larafocus.prefix');
        $productionBaseUrl = config()->string('larafocus.production.endpoint').$prefix;

        return new Companies(
            new Http(
                baseUrl: $productionBaseUrl,
                token: $this->masterToken ?? config()->string('larafocus.master_token'),
                timeout: $this->timeout,
            ),
            $this->resolveEnvironment(),
        );
    }

    private function buildHttp(): Http
    {
        $environment = $this->resolveEnvironment();
        $prefix = config()->string('larafocus.prefix');

        return new Http(
            baseUrl: config()->string('larafocus.'.$environment->value.'.endpoint').$prefix,
            token: $this->token ?? config()->string('larafocus.'.$environment->value.'.token'),
            timeout: $this->timeout,
        );
    }

    private function resolveEnvironment(): Environment
    {
        return $this->environment ?? Environment::from(config()->string('larafocus.environment'));
    }
}
