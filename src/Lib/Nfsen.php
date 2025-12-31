<?php

namespace Larafocus\Lib;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Nfsen extends Api
{
    public function create(string $reference, array $parameters = []): Response
    {
        return $this->http()->post('/nfsen?ref='.$reference, $parameters);
    }

    public function get(string $reference): Response
    {
        return $this->http()->get('/nfsen/'.$reference);
    }

    public function cancel(string $reference, array $parameters = []): Response
    {
        return $this->http()->delete('/nfsen/'.$reference, $parameters);
    }
}
