<?php

declare(strict_types=1);

namespace Larafocus\Lib\Search\Cities;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class TaxCodes extends Api
{
    public function __construct(protected string $cityCode) {}

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/codigos_tributarios_municipio', $parameters);
    }

    public function get(string $taxCode): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/codigos_tributarios_municipio/'.$taxCode);
    }
}
