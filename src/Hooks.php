<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\Http;

readonly class Hooks
{
    public function __construct(private Http $http) {}

    public function list(): Response
    {
        return $this->http->get('/hooks');
    }

    /** @param array<string, mixed> $parameters */
    public function create(array $parameters = []): Response
    {
        return $this->http->post('/hooks', $parameters);
    }

    public function get(string $hookId): Response
    {
        return $this->http->get('/hooks/'.urlencode($hookId));
    }

    public function delete(string $hookId): Response
    {
        return $this->http->delete('/hooks/'.urlencode($hookId));
    }
}
