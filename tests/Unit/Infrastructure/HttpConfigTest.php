<?php

use Larafocus\Infrastructure\HttpConfig;

covers(HttpConfig::class);

test('stores all configuration values', function () {
    $config = new HttpConfig(
        timeout: 30,
        environment: 'production',
        token: 'my-token',
        masterToken: 'master-token',
    );

    expect($config->timeout)->toBe(30)
        ->and($config->environment)->toBe('production')
        ->and($config->token)->toBe('my-token')
        ->and($config->masterToken)->toBe('master-token');
});
