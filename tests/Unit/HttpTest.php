<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Http as LarafocusHttp;

test('get with xml format uses focusXml macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isXml();

    $response = $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/xml');
    });
});

test('get with pdf format uses focusPdf macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isPdf();

    $response = $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/pdf');
    });
});

test('get without xml or pdf uses focus macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $response = $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/json');
    });
});

test('post uses focus macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $response = $http->post('/nfse', ['key' => 'value']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/nfse')
            && $request['key'] === 'value';
    });
});

test('patch uses focus macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $response = $http->patch('/empresas/123', ['name' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'PATCH'
            && str_contains($request->url(), '/empresas/123')
            && $request['name'] === 'test';
    });
});

test('delete uses focus macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $response = $http->delete('/nfse/ref-123', ['justificativa' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'DELETE'
            && str_contains($request->url(), '/nfse/ref-123');
    });
});

test('setter methods return same instance for chaining', function () {
    $http = new LarafocusHttp;

    expect($http->timeout(30))->toBe($http);
    expect($http->environment('sandbox'))->toBe($http);
    expect($http->useMasterKey(true))->toBe($http);
    expect($http->token('token'))->toBe($http);
    expect($http->isXml())->toBe($http);
    expect($http->isPdf())->toBe($http);
});

test('get passes parameters as query string', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $response = $http->get('/municipios', ['codigo' => '1234']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), 'codigo=1234');
    });
});

test('useMasterKey is passed to focus macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->useMasterKey(true)
        ->timeout(60);

    $response = $http->post('/empresas', []);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/empresas');
    });
});
