<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

readonly class Companies
{
    public function __construct(
        private Http $http,
        private Environment $environment,
    ) {}

    public function list(int $offset = 0): FocusResponse
    {
        return $this->http->get('/empresas', ['offset' => $offset]);
    }

    /** @param array<string, mixed> $parameters */
    public function create(array $parameters = []): FocusResponse
    {
        $dryRun = $this->environment === Environment::Sandbox ? '?dry_run=1' : '';

        return $this->http->post('/empresas'.$dryRun, $parameters);
    }

    public function get(string $id): FocusResponse
    {
        return $this->http->get('/empresas/'.urlencode($id));
    }

    /** @param array<string, mixed> $parameters */
    public function update(string $id, array $parameters = []): FocusResponse
    {
        $url = '/empresas/'.urlencode($id).($this->environment === Environment::Sandbox ? '?dry_run=1' : '');

        return $this->http->put($url, $parameters);
    }

    public function delete(string $id): FocusResponse
    {
        return $this->http->delete('/empresas/'.urlencode($id));
    }
}
