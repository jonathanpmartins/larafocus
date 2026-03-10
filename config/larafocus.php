<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Currently supported values: "sandbox", "production"
    |
    */
    'prefix' => env('LARAFOCUS_PREFIX', '/v2'),
    'environment' => env('LARAFOCUS_ENVIRONMENT', 'production'),
    'master_token' => env('LARAFOCUS_MASTER_TOKEN', ''),
    'sandbox' => [
        'endpoint' => env('LARAFOCUS_SANDBOX_ENDPOINT', 'https://homologacao.focusnfe.com.br'),
        'token' => env('LARAFOCUS_SANDBOX_TOKEN', ''),
    ],
    'production' => [
        'endpoint' => env('LARAFOCUS_PRODUCTION_ENDPOINT', 'https://api.focusnfe.com.br'),
        'token' => env('LARAFOCUS_PRODUCTION_TOKEN', ''),
    ],
];
