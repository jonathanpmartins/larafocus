<?php

declare(strict_types=1);

namespace Larafocus\Search;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;
use Larafocus\Search\Cities\Services;
use Larafocus\Search\Cities\TaxCodes;

readonly class Cities
{
    public function __construct(private Http $http) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): FocusResponse
    {
        return $this->http->get('/municipios', $parameters);
    }

    public function get(string $cityCode): FocusResponse
    {
        return $this->http->get('/municipios/'.urlencode($cityCode));
    }

    public function servicesFor(string $cityCode): Services
    {
        return new Services($this->http, $cityCode);
    }

    public function taxCodesFor(string $cityCode): TaxCodes
    {
        return new TaxCodes($this->http, $cityCode);
    }
}
