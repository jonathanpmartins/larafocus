<?php

use Larafocus\LarafocusServiceProvider;

covers(LarafocusServiceProvider::class);

test('service provider registers singleton', function () {
    $focus1 = app(\Larafocus\Infrastructure\FocusManager::class);
    $focus2 = app(\Larafocus\Infrastructure\FocusManager::class);

    expect($focus1)->toBeInstanceOf(\Larafocus\Infrastructure\FocusManager::class)
        ->and($focus1)->toBe($focus2);
});

test('service provider merges config', function () {
    expect(config('larafocus.environment'))->not->toBeNull()
        ->and(config('larafocus.sandbox.endpoint'))->toBe('https://homologacao.focusnfe.com.br')
        ->and(config('larafocus.production.endpoint'))->toBe('https://api.focusnfe.com.br');
});

test('service provider publishes config file', function () {
    $publishes = \Illuminate\Support\ServiceProvider::$publishes;

    $found = false;
    foreach ($publishes as $provider => $paths) {
        foreach ($paths as $from => $to) {
            if (str_contains($from, 'config/larafocus.php')) {
                expect(file_exists($from))->toBeTrue();
                $found = true;
            }
        }
    }

    expect($found)->toBeTrue();
});

test('prefix is set to v2', function () {
    expect(config('larafocus.prefix'))->toBe('/v2');
});
