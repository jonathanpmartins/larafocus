<?php

declare(strict_types=1);

namespace Larafocus\Search\Cities;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\Http;

readonly class TaxCodes
{
    public function __construct(
        private Http $http,
        private string $cityCode,
    ) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): Response
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/codigos_tributarios_municipio', $parameters);
    }

    public function get(string $taxCode): Response
    {
        return $this->http->get('/municipios/'.urlencode($this->cityCode).'/codigos_tributarios_municipio/'.urlencode($taxCode));
    }
}
