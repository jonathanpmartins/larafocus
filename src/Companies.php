<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\Http;
use Larafocus\Infrastructure\HttpConfig;

class Companies
{
    public function __construct(private readonly HttpConfig $httpConfig) {}

    public function list(int $offset = 0): Response
    {
        return $this->http()->get('/empresas', ['offset' => $offset]);
    }

    /** @param array<string, mixed> $parameters */
    public function create(array $parameters = []): Response
    {
        $dryRun = $this->httpConfig->environment === Environment::Sandbox ? '?dry_run=1' : '';

        return $this->http()->post('/empresas'.$dryRun, $parameters);
    }

    public function get(string $id): Response
    {
        return $this->http()->get('/empresas/'.urlencode($id));
    }

    /** @param array<string, mixed> $parameters */
    public function update(string $id, array $parameters = []): Response
    {
        $url = '/empresas/'.urlencode($id).($this->httpConfig->environment === Environment::Sandbox ? '?dry_run=1' : '');

        return $this->http()->patch($url, $parameters);
    }

    public function delete(string $id): Response
    {
        return $this->http()->delete('/empresas/'.urlencode($id));
    }

    private function http(): Http
    {
        return new Http(
            environment: Environment::Production,
            token: $this->httpConfig->masterToken,
            timeout: $this->httpConfig->timeout,
        );
    }
}
