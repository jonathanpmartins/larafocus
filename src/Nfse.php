<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\Http;

readonly class Nfse
{
    public function __construct(private Http $http) {}

    /** @param array<string, mixed> $parameters */
    public function create(string $reference, array $parameters = []): Response
    {
        return $this->http->post('/nfse?ref='.urlencode($reference), $parameters);
    }

    public function get(string $reference): Response
    {
        return $this->http->get('/nfse/'.urlencode($reference));
    }

    /** @param array<string, mixed> $parameters */
    public function cancel(string $reference, array $parameters = []): Response
    {
        return $this->http->delete('/nfse/'.urlencode($reference), $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function email(string $reference, array $parameters = []): Response
    {
        return $this->http->post('/nfse/'.urlencode($reference).'/email', $parameters);
    }
}
