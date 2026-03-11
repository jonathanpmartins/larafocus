<?php

use Larafocus\Infrastructure\Environment;

covers(Environment::class);

test('sandbox case has correct value', function () {
    expect(Environment::Sandbox->value)->toBe('sandbox');
});

test('production case has correct value', function () {
    expect(Environment::Production->value)->toBe('production');
});

test('creates from string value', function () {
    expect(Environment::from('sandbox'))->toBe(Environment::Sandbox)
        ->and(Environment::from('production'))->toBe(Environment::Production);
});
