<?php

declare(strict_types=1);

namespace Larafocus\Search\Cities;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\Api;

class Services extends Api
{
    public function __construct(protected string $cityCode)
    {
        parent::__construct();
    }

    /** @param array<string, mixed> $parameters */
    public function list(array $parameters = []): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/itens_lista_servico', $parameters);
    }

    public function get(string $serviceCode): Response
    {
        return $this->http()->get('/municipios/'.$this->cityCode.'/itens_lista_servico/'.$serviceCode);
    }
}
