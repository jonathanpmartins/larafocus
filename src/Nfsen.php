<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

readonly class Nfsen
{
    public function __construct(private Http $http) {}

    /** @param array<string, mixed> $parameters */
    public function create(string $reference, array $parameters = []): FocusResponse
    {
        return $this->http->post('/nfsen?ref='.urlencode($reference), $parameters);
    }

    public function get(string $reference): FocusResponse
    {
        return $this->http->get('/nfsen/'.urlencode($reference));
    }

    /** @param array<string, mixed> $parameters */
    public function cancel(string $reference, array $parameters = []): FocusResponse
    {
        return $this->http->delete('/nfsen/'.urlencode($reference), $parameters);
    }

    public function hook(string $reference): FocusResponse
    {
        return $this->http->post('/nfsen/'.urlencode($reference).'/hook');
    }
}
