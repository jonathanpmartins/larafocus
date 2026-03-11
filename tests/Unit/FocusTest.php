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

test('using returns new instance with configured values', function () {
    Http::fake();

    Focus::using(
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

test('using does not mutate the singleton', function () {
    Http::fake();

    Focus::using(
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

test('using without arguments returns instance with current values', function () {
    Http::fake();

    Focus::using()->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br/v2/nfse/ref-1');
    });
});

test('using merges with current config values', function () {
    Http::fake();

    Focus::config(timeout: 120, environment: Environment::Production, token: 'base-token');

    $nfse = Focus::using(token: 'override-token')->nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(120);

    $nfse->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('override-token:');

        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1')
            && $request->hasHeader('Authorization', 'Basic '.$expectedToken);
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

    Focus::using(
        timeout: 15,
        environment: Environment::Production,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1');
    });
});

test('using token is used instead of config token', function () {
    Http::fake();

    Focus::using(
        environment: Environment::Sandbox,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('custom-token:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('using masterToken is used instead of config masterToken', function () {
    Http::fake();

    Focus::using(
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

    Focus::using(
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

test('using default timeout is 60 seconds', function () {
    // Same reflection justification as above — verifies using() preserves
    // the default timeout when no explicit value is provided.
    $nfse = Focus::using()->nfse();
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

test('config mutates the singleton', function () {
    Http::fake();

    Focus::config(environment: Environment::Production, token: 'persistent-token');

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('persistent-token:');

        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1')
            && $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('config returns the same instance', function () {
    $manager = Focus::getFacadeRoot();
    $returned = Focus::config(timeout: 120);

    expect($returned)->toBe($manager);
});

test('config persists across multiple calls', function () {
    Http::fake();

    Focus::config(timeout: 120);
    Focus::config(environment: Environment::Production, token: 'my-token');

    $nfse = Focus::nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(120);

    $nfse->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br/v2/nfse/ref-1');
    });
});

test('config masterToken persists for companies', function () {
    Http::fake();

    Focus::config(masterToken: 'config-master');

    Focus::companies()->get('id');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('config-master:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('using inherits timeout from base instance', function () {
    Focus::config(timeout: 90);

    $nfse = Focus::using(token: 'override')->nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(90);
});

test('using overrides timeout from base instance', function () {
    Focus::config(timeout: 90);

    $nfse = Focus::using(timeout: 45)->nfse();
    $http = (new ReflectionProperty($nfse, 'http'))->getValue($nfse);

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(45);
});

test('config resets token to null to fall back to config default', function () {
    Http::fake();

    Focus::config(token: 'custom-token');
    Focus::config(token: null);

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('test-token:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('config resets environment to null to fall back to config default', function () {
    Http::fake();

    Focus::config(environment: Environment::Production);
    Focus::config(environment: null);

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('config without arguments does not change any values', function () {
    Http::fake();

    Focus::config(token: 'keep-this');
    Focus::config();

    Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('keep-this:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('using resets token to null to fall back to config default', function () {
    Http::fake();

    Focus::config(token: 'custom-token');

    Focus::using(token: null)->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('test-token:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});
