<?php

use Illuminate\Support\Facades\Http;
use Larafocus\Companies;
use Larafocus\Focus;
use Larafocus\Hooks;
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

test('setup sets all values and returns instance', function () {
    $result = Focus::setup(
        timeout: 30,
        environment: 'production',
        token: 'my-token',
        masterToken: 'master',
    );

    expect($result)->toBeInstanceOf(FocusManager::class)
        ->and($result->getTimeout())->toBe(30)
        ->and($result->getEnvironment())->toBe('production')
        ->and($result->getToken())->toBe('my-token')
        ->and($result->getMasterToken())->toBe('master');
});

test('setup resets all values to defaults', function () {
    Focus::setup(
        timeout: 30,
        environment: 'production',
        token: 'my-token',
        masterToken: 'master',
    );

    Focus::setup();

    $manager = app(FocusManager::class);

    expect($manager->getTimeout())->toBe(60)
        ->and($manager->getEnvironment())->toBeNull()
        ->and($manager->getToken())->toBeNull()
        ->and($manager->getMasterToken())->toBeNull();
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
    Focus::setup(
        timeout: 15,
        environment: 'production',
        token: 'custom-token',
    );

    Http::fake();

    $response = Focus::nfse()->get('ref-1');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.focusnfe.com.br')
            && str_contains($request->url(), '/nfse/ref-1');
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
