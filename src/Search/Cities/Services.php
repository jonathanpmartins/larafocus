<?php

declare(strict_types=1);

namespace Larafocus\Search\Cities;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

readonly class Services
{
    public function __construct(
        private Http $http,
        private string $cityCode,
    ) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): FocusResponse
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/itens_lista_servico', $parameters);
    }

    public function get(string $serviceCode): FocusResponse
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/itens_lista_servico/'.urlencode($serviceCode));
    }
}
