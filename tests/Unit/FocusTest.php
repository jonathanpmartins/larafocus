<?php

use Illuminate\Support\Facades\Http;
use Larafocus\Focus;
use Larafocus\Lib\Companies;
use Larafocus\Lib\Hooks;
use Larafocus\Lib\Nfse;
use Larafocus\Lib\Nfsen;
use Larafocus\Lib\Search;

beforeEach(function () {
    Focus::$timeout = 60;
    Focus::$environment = null;
    Focus::$useMasterKey = false;
    Focus::$token = null;
});

test('timeout sets timeout and returns Focus instance', function () {
    $result = Focus::timeout(30);

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$timeout)->toBe(30);
});

test('timeout uses default value', function () {
    Focus::$timeout = 999;
    Focus::timeout();

    expect(Focus::$timeout)->toBe(60);
});

test('environment sets environment and returns Focus instance', function () {
    $result = Focus::environment('production');

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$environment)->toBe('production');
});

test('environment defaults to null', function () {
    Focus::$environment = 'production';
    Focus::environment();

    expect(Focus::$environment)->toBeNull();
});

test('useMasterKey sets flag and returns Focus instance', function () {
    $result = Focus::useMasterKey();

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$useMasterKey)->toBeTrue();
});

test('useMasterKey can be set to false', function () {
    Focus::useMasterKey(true);
    Focus::useMasterKey(false);

    expect(Focus::$useMasterKey)->toBeFalse();
});

test('token sets token and returns Focus instance', function () {
    $result = Focus::token('my-token');

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$token)->toBe('my-token');
});

test('getEnv returns static environment when set', function () {
    Focus::$environment = 'production';

    expect(Focus::getEnv())->toBe('production');
});

test('getEnv returns config environment when static is null', function () {
    expect(Focus::getEnv())->toBe('sandbox');
});

test('getEndpoint returns endpoint from config', function () {
    expect(Focus::getEndpoint())->toBe('https://homologacao.focusnfe.com.br');
});

test('nfse returns Nfse instance', function () {
    expect(Focus::nfse())->toBeInstanceOf(Nfse::class);
});

test('nfsen returns Nfsen instance', function () {
    expect(Focus::nfsen())->toBeInstanceOf(Nfsen::class);
});

test('hooks returns Hooks instance', function () {
    expect(Focus::hooks())->toBeInstanceOf(Hooks::class);
});

test('search returns Search instance', function () {
    expect(Focus::search())->toBeInstanceOf(Search::class);
});

test('companies returns Companies instance', function () {
    expect(Focus::companies())->toBeInstanceOf(Companies::class);
});

test('factory methods pass configuration to instances', function () {
    Focus::timeout(15);
    Focus::environment('production');
    Focus::useMasterKey(true);
    Focus::token('custom-token');

    Http::fake();

    $response = Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br')
            && str_contains($request->url(), '/nfse/ref-1');
    });
});

test('nfsen factory passes configuration', function () {
    Http::fake();

    $response = Focus::nfsen()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/nfsen/ref-1');
    });
});
