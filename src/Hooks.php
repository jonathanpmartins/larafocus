<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

readonly class Hooks
{
    public function __construct(private Http $http) {}

    public function list(): FocusResponse
    {
        return $this->http->get('/hooks');
    }

    /** @param array<string, mixed> $parameters */
    public function create(array $parameters = []): FocusResponse
    {
        return $this->http->post('/hooks', $parameters);
    }

    public function get(string $hookId): FocusResponse
    {
        return $this->http->get('/hooks/'.urlencode($hookId));
    }

    public function delete(string $hookId): FocusResponse
    {
        return $this->http->delete('/hooks/'.urlencode($hookId));
    }
}
