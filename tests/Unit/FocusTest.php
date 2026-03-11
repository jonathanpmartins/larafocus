<?php

use Illuminate\Support\Facades\Http;
use Larafocus\Companies;
use Larafocus\Focus;
use Larafocus\Hooks;
use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\FocusManager;
use Larafocus\Nfse;
use Larafocus\Search;

covers(FocusManager::class, Focus::class);

test('default values resolve from config', function () {
    Http::fake();

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('test-token:');

        return str_contains($request->url(), 'homologacao.focusnfe.com.br/v2/nfse/ref-1')
            && $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('setup returns new instance with configured values', function () {
    Http::fake();

    Focus::setup(
        timeout: 30,
        environment: Environment::Production,
        token: 'my-token',
        masterToken: 'master',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('my-token:');

        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1')
            && $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('setup does not mutate the singleton', function () {
    Http::fake();

    Focus::setup(
        timeout: 30,
        environment: Environment::Production,
        token: 'custom-token',
        masterToken: 'master',
    );

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('setup without arguments returns instance with defaults', function () {
    Http::fake();

    Focus::setup()->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br/v2/nfse/ref-1');
    });
});

test('nfse returns Nfse instance', function () {
    expect(Focus::nfse())->toBeInstanceOf(Nfse::class);
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
    Http::fake();

    Focus::setup(
        timeout: 15,
        environment: Environment::Production,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1');
    });
});

test('setup token is used instead of config token', function () {
    Http::fake();

    Focus::setup(
        environment: Environment::Sandbox,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('custom-token:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('setup masterToken is used instead of config masterToken', function () {
    Http::fake();

    Focus::setup(
        environment: Environment::Sandbox,
        token: 'some-token',
        masterToken: 'custom-master',
    )->companies()->get('id');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('custom-master:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('facade resolves to FocusManager', function () {
    expect(Focus::getFacadeRoot())->toBeInstanceOf(FocusManager::class);
});

test('companies always uses production endpoint with prefix', function () {
    Http::fake();

    Focus::setup(
        environment: Environment::Sandbox,
        token: 'some-token',
        masterToken: 'master',
    )->companies()->get('id');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br/v2/empresas/id');
    });
});

test('default timeout is 60 seconds', function () {
    // Reflection is used here because the timeout is not observable through
    // Http::fake(). This verifies the safety-critical invariant that requests
    // have a sensible default timeout and do not hang indefinitely.
    $nfse = Focus::nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(60);
});

test('setup default timeout is 60 seconds', function () {
    // Same reflection justification as above — verifies setup() preserves
    // the default timeout when no explicit value is provided.
    $nfse = Focus::setup()->nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(60);
});

test('base url includes endpoint and prefix for configured environment', function () {
    Http::fake();

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br/v2/nfse/ref-1');
    });
});
