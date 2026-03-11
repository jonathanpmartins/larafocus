<?php

declare(strict_types=1);

namespace Larafocus\Search\Cities;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

readonly class TaxCodes
{
    public function __construct(
        private Http $http,
        private string $cityCode,
    ) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): FocusResponse
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/codigos_tributarios_municipio', $parameters);
    }

    public function get(string $taxCode): FocusResponse
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/codigos_tributarios_municipio/'.urlencode($taxCode));
    }
}
