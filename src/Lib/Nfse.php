<?php

namespace Larafocus\Lib;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Nfse extends Api
{
    public function create(string $reference, array $parameters = []): Response
    {
        return $this->http()->post('/nfse?ref='.$reference, $parameters);
    }

    public function get(string $reference): Response
    {
        return $this->http()->get('/nfse/'.$reference);
    }

    public function cancel(string $reference): Response
    {
        return $this->http()->delete('/nfse/'.$reference);
    }

    public function email(string $reference, array $parameters = []): Response
    {
        return $this->http()->post('/nfse/'.$reference.'/email', $parameters);
    }
}
