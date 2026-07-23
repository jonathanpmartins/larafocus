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
    'environment' => env('LARAFOCUS_ENVIRONMENT', 'sandbox'),
    'master_token' => env('LARAFOCUS_MASTER_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Timeouts (seconds)
    |--------------------------------------------------------------------------
    |
    | "timeout" bounds the whole request. "connect_timeout" bounds only establishing
    | the TCP/TLS connection, so a dead host fails fast instead of waiting for the
    | full timeout. Both must be at least 1 second; 0 would wait forever.
    |
    */
    'timeout' => (int) env('LARAFOCUS_TIMEOUT', 60),
    'connect_timeout' => (int) env('LARAFOCUS_CONNECT_TIMEOUT', 10),

    'sandbox' => [
        'endpoint' => env('LARAFOCUS_SANDBOX_ENDPOINT', 'https://homologacao.focusnfe.com.br'),
        'token' => env('LARAFOCUS_SANDBOX_TOKEN', ''),
    ],
    'production' => [
        'endpoint' => env('LARAFOCUS_PRODUCTION_ENDPOINT', 'https://api.focusnfe.com.br'),
        'token' => env('LARAFOCUS_PRODUCTION_TOKEN', ''),
    ],
];
