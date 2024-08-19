<?php

namespace Larafocus\Lib\Search\Cities;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Services extends Api
{
    protected string $cityCode;

    public function __construct(string $cityCode)
    {
        $this->cityCode = $cityCode;
    }

    public function list(array $parameters = []): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/itens_lista_servico', $parameters);
    }

    public function get(string $serviceCode): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/itens_lista_servico/'.$serviceCode);
    }
}
