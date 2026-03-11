<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Infrastructure\ContentType;
use Larafocus\Infrastructure\Http as LarafocusHttp;

covers(LarafocusHttp::class);

test('xml content type sets xml headers', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
        contentType: ContentType::Xml,
    );

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/xml');
    });
});

test('pdf content type sets pdf headers', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
        contentType: ContentType::Pdf,
    );

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/pdf');
    });
});

test('default content type is json', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/json');
    });
});

test('post sends correct method and data', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->post('/nfse', ['key' => 'value']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/nfse')
            && $request['key'] === 'value';
    });
});

test('put sends correct method and data', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->put('/empresas/123', ['name' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'PUT'
            && str_contains($request->url(), '/empresas/123')
            && $request['name'] === 'test';
    });
});

test('patch sends correct method and data', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->patch('/empresas/123', ['name' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'PATCH'
            && str_contains($request->url(), '/empresas/123')
            && $request['name'] === 'test';
    });
});

test('delete sends correct method', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->delete('/nfse/ref-123', ['justificativa' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'DELETE'
            && str_contains($request->url(), '/nfse/ref-123');
    });
});

test('default timeout is 60 seconds', function () {
    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(60);
});

test('get passes parameters as query string', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
        timeout: 60,
    );

    $http->get('/municipios', ['codigo' => '1234']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), 'codigo=1234');
    });
});

test('token is encoded and sent as basic auth', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'direct-token',
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('direct-token:');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('throws exception when token is empty', function () {
    new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: '',
    );
})->throws(InvalidArgumentException::class, 'API token must not be empty');

test('throws exception when token is whitespace only', function () {
    new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: '   ',
    );
})->throws(InvalidArgumentException::class, 'API token must not be empty');

test('request url combines base url and uri path', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://custom.example.com/v2',
        token: 'test',
    );

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom.example.com/v2/nfse/ref');
    });
});
