<?php

namespace Larafocus\Lib\Search;

use Illuminate\Http\Client\Response;
use Larafocus\Api;
use Larafocus\Lib\Search\Cities\Services;
use Larafocus\Lib\Search\Cities\TaxCodes;

class Cities extends Api
{
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
        return (new Services($cityCode))
            ->useMasterKey($this->useMasterKey)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }

    public function taxCodesFor(string $cityCode): TaxCodes
    {
        return (new TaxCodes($cityCode))
            ->useMasterKey($this->useMasterKey)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }
}
