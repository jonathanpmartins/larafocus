<?php

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

test('environment sets environment and returns Focus instance', function () {
    $result = Focus::environment('production');

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$environment)->toBe('production');
});

test('useMasterKey sets flag and returns Focus instance', function () {
    $result = Focus::useMasterKey();

    expect($result)->toBeInstanceOf(Focus::class)
        ->and(Focus::$useMasterKey)->toBeTrue();
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
    $endpoint = Focus::getEndpoint();

    expect($endpoint)->toBe('https://homologacao.focusnfe.com.br');
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
