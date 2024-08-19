<?php

namespace Larafocus\Lib;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Hooks extends Api
{
    public function list(): Response
    {
        return $this->http()->get('/hooks');
    }

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
