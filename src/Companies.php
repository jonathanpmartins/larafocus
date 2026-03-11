<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Companies\DTO\EmpresaRequest;
use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

/**
 * @phpstan-import-type EmpresaRequestArray from EmpresaRequest
 */
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

    /**
     * @param  EmpresaRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param EmpresaRequest|EmpresaRequestArray $parameters
     */
    public function create(EmpresaRequest|array $parameters = new EmpresaRequest): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = EmpresaRequest::fromArray($parameters);
        }

        $dryRun = $this->environment === Environment::Sandbox ? '?dry_run=1' : '';

        return $this->http->post('/empresas'.$dryRun, $parameters->toArray());
    }

    public function get(string $id): FocusResponse
    {
        return $this->http->get('/empresas/'.urlencode($id));
    }

    /**
     * @param  EmpresaRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param EmpresaRequest|EmpresaRequestArray $parameters
     */
    public function update(string $id, EmpresaRequest|array $parameters = new EmpresaRequest): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = EmpresaRequest::fromArray($parameters);
        }

        $url = '/empresas/'.urlencode($id).($this->environment === Environment::Sandbox ? '?dry_run=1' : '');

        return $this->http->put($url, $parameters->toArray());
    }

    public function delete(string $id): FocusResponse
    {
        return $this->http->delete('/empresas/'.urlencode($id));
    }
}
