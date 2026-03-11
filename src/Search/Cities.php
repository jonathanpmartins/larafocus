<?php

declare(strict_types=1);

namespace Larafocus\Search;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\HasHttp;
use Larafocus\Infrastructure\HttpConfig;
use Larafocus\Search\Cities\Services;
use Larafocus\Search\Cities\TaxCodes;

class Cities
{
    use HasHttp;

    public function __construct(private readonly HttpConfig $httpConfig) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): Response
    {
        return $this->http()->get('/municipios', $parameters);
    }

    public function get(string $cityCode): Response
    {
        return $this->http()->get('/municipios/'.$cityCode);
    }

    public function servicesFor(string $cityCode): Services
    {
        return new Services($this->httpConfig, $cityCode);
    }

    public function taxCodesFor(string $cityCode): TaxCodes
    {
        return new TaxCodes($this->httpConfig, $cityCode);
    }
}
