<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Infrastructure\ContentType;
use Larafocus\Infrastructure\Environment;
use Larafocus\Infrastructure\Http as LarafocusHttp;

covers(LarafocusHttp::class);

test('xml content type sets xml headers', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
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
        environment: Environment::Sandbox,
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
        environment: Environment::Sandbox,
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
        environment: Environment::Sandbox,
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

test('patch sends correct method and data', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
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
        environment: Environment::Sandbox,
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
    // Reflection is used here because the timeout property is private and its
    // default value is not observable through Http::fake(). This verifies the
    // safety-critical invariant that the default matches the API's expectation.
    $http = new LarafocusHttp(Environment::Sandbox, 'test-token');

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(60);
});

test('get passes parameters as query string', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
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
        environment: Environment::Sandbox,
        token: 'direct-token',
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('direct-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('sandbox environment uses sandbox endpoint', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test-token',
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('production environment uses production endpoint', function () {
    $http = new LarafocusHttp(
        environment: Environment::Production,
        token: 'test-token',
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'api.focusnfe.com.br');
    });
});

test('base url includes v2 prefix', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
    );

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('custom endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom.example.com');

    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom.example.com/v2/test');
    });
});

test('xml uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-xml.example.com');

    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
        contentType: ContentType::Xml,
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-xml.example.com/v2/test');
    });
});

test('pdf uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-pdf.example.com');

    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
        contentType: ContentType::Pdf,
    );

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-pdf.example.com/v2/test');
    });
});

test('xml base url includes v2 prefix', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
        contentType: ContentType::Xml,
    );

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('pdf base url includes v2 prefix', function () {
    $http = new LarafocusHttp(
        environment: Environment::Sandbox,
        token: 'test',
        contentType: ContentType::Pdf,
    );

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});
