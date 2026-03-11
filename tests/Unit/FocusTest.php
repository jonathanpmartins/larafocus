<?php

use Illuminate\Support\Facades\Http;
use Larafocus\Companies;
use Larafocus\Focus;
use Larafocus\Hooks;
use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\FocusManager;
use Larafocus\Nfse;
use Larafocus\Nfsen;
use Larafocus\Search;

covers(FocusManager::class, Focus::class);

test('default values are correct', function () {
    $manager = app(FocusManager::class);

    expect($manager->getTimeout())->toBe(60)
        ->and($manager->getEnvironment())->toBeNull()
        ->and($manager->getToken())->toBeNull()
        ->and($manager->getMasterToken())->toBeNull();
});

test('setup returns new immutable instance with configured values', function () {
    $result = Focus::setup(
        timeout: 30,
        environment: Environment::Production,
        token: 'my-token',
        masterToken: 'master',
    );

    expect($result)->toBeInstanceOf(FocusManager::class)
        ->and($result->getTimeout())->toBe(30)
        ->and($result->getEnvironment())->toBe(Environment::Production)
        ->and($result->getToken())->toBe('my-token')
        ->and($result->getMasterToken())->toBe('master');
});

test('setup does not mutate the singleton', function () {
    Focus::setup(
        timeout: 30,
        environment: Environment::Production,
        token: 'my-token',
        masterToken: 'master',
    );

    $manager = app(FocusManager::class);

    expect($manager->getTimeout())->toBe(60)
        ->and($manager->getEnvironment())->toBeNull()
        ->and($manager->getToken())->toBeNull()
        ->and($manager->getMasterToken())->toBeNull();
});

test('setup without arguments returns instance with defaults', function () {
    $result = Focus::setup();

    expect($result)->toBeInstanceOf(FocusManager::class)
        ->and($result->getTimeout())->toBe(60)
        ->and($result->getEnvironment())->toBeNull()
        ->and($result->getToken())->toBeNull()
        ->and($result->getMasterToken())->toBeNull();
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
    Http::fake();

    $response = Focus::setup(
        timeout: 15,
        environment: Environment::Production,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br')
            && str_contains($request->url(), '/nfse/ref-1');
    });
});

test('setup token is used instead of config token', function () {
    Http::fake();

    Focus::setup(
        environment: Environment::Sandbox,
        token: 'custom-token',
    )->nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        $expectedToken = base64_encode('custom-token');

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
        $expectedToken = base64_encode('custom-master');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('facade resolves to FocusManager', function () {
    expect(Focus::getFacadeRoot())->toBeInstanceOf(FocusManager::class);
});

test('nfsen factory passes configuration', function () {
    Http::fake();

    $response = Focus::nfsen()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/nfsen/ref-1');
    });
});
