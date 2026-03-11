<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Larafocus\Companies;
use Larafocus\Hooks;
use Larafocus\Nfse;
use Larafocus\Search;
use Larafocus\Shared\Undefined;

class FocusManager
{
    public function __construct(
        private int $timeout = 60,
        private ?Environment $environment = null,
        private ?string $token = null,
        private ?string $masterToken = null,
    ) {}

    public function config(
        int|Undefined $timeout = Undefined::Value,
        Environment|Undefined|null $environment = Undefined::Value,
        string|Undefined|null $token = Undefined::Value,
        string|Undefined|null $masterToken = Undefined::Value,
    ): self {
        if ($timeout !== Undefined::Value) {
            $this->timeout = $timeout;
        }

        if ($environment !== Undefined::Value) {
            $this->environment = $environment;
        }

        if ($token !== Undefined::Value) {
            $this->token = $token;
        }

        if ($masterToken !== Undefined::Value) {
            $this->masterToken = $masterToken;
        }

        return $this;
    }

    public function using(
        int|Undefined $timeout = Undefined::Value,
        Environment|Undefined|null $environment = Undefined::Value,
        string|Undefined|null $token = Undefined::Value,
        string|Undefined|null $masterToken = Undefined::Value,
    ): self {
        return new self(
            timeout: $timeout !== Undefined::Value ? $timeout : $this->timeout,
            environment: $environment !== Undefined::Value ? $environment : $this->environment,
            token: $token !== Undefined::Value ? $token : $this->token,
            masterToken: $masterToken !== Undefined::Value ? $masterToken : $this->masterToken,
        );
    }

    public function nfse(): Nfse
    {
        return new Nfse($this->buildHttp());
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
