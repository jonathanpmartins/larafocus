<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\HasHttp;
use Larafocus\Infrastructure\HttpConfig;

class Nfse
{
    use HasHttp;

    public function __construct(private readonly HttpConfig $httpConfig) {}

    /** @param array<string, mixed> $parameters */
    public function create(string $reference, array $parameters = []): Response
    {
        return $this->http()->post('/nfse?ref='.$reference, $parameters);
    }

    public function get(string $reference): Response
    {
        return $this->http()->get('/nfse/'.$reference);
    }

    /** @param array<string, mixed> $parameters */
    public function cancel(string $reference, array $parameters = []): Response
    {
        return $this->http()->delete('/nfse/'.$reference, $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function email(string $reference, array $parameters = []): Response
    {
        return $this->http()->post('/nfse/'.$reference.'/email', $parameters);
    }
}
