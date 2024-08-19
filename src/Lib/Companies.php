<?php

namespace Larafocus\Lib;

use Illuminate\Http\Client\Response;
use Larafocus\Api;

class Companies extends Api
{
    public function list(array $parameters = [], int $offset = 0): Response
    {
        return $this->http()
            ->useMasterKey()
            ->environment('production')
            ->get('/empresas', array_merge($parameters, ['offset' => $offset]));
    }

    public function create(array $parameters = []): Response
    {
        $environment = $this->environment ?: config('larafocus.environment');
        $dryRun = $environment == 'sandbox' ? '?dry_run=1' : '';

        return $this->http()
            ->useMasterKey()
            ->environment('production')
            ->post('/empresas'.$dryRun, $parameters);
    }

    public function get(string $id): Response
    {
        return $this->http()
            ->useMasterKey()
            ->environment('production')
            ->get('/empresas/'.$id);
    }

    public function update(string $id, array $parameters = []): Response
    {
        $environment = $this->environment ?: config('larafocus.environment');
        $url = $environment == 'sandbox' ? '/empresas?dry_run=1' : '/empresas/'.$id;

        return $this->http()
            ->useMasterKey()
            ->environment('production')
            ->patch($url, $parameters);
    }

    public function delete(string $id): Response
    {
        return $this->http()
            ->useMasterKey()
            ->environment('production')
            ->delete('/empresas/'.$id);
    }
}
