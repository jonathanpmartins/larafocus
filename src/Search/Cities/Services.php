<?php

declare(strict_types=1);

namespace Larafocus\Search\Cities;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\HasHttp;
use Larafocus\Infrastructure\HttpConfig;

class Services
{
    use HasHttp;

    public function __construct(
        private readonly HttpConfig $httpConfig,
        private readonly string $cityCode,
    ) {}

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
