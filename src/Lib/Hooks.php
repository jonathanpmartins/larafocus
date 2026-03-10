<?php

declare(strict_types=1);

namespace Larafocus\Lib;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Hooks extends Api
{
    public function list(): Response
    {
        return $this->http()->get('/hooks');
    }

    /** @param array<string, mixed> $parameters */
    public function create(array $parameters = []): Response
    {
        return $this->http()->post('/hooks', $parameters);
    }

    public function get(string $hookId): Response
    {
        return $this->http()->get('/hooks/'.$hookId);
    }

    public function delete(string $hookId): Response
    {
        return $this->http()->delete('/hooks/'.$hookId);
    }
}
