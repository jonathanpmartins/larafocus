<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Infrastructure\Http as LarafocusHttp;

covers(LarafocusHttp::class);

test('get with xml format sets xml content type', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isXml();

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/xml');
    });
});

test('get with pdf format sets pdf content type', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isPdf();

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/pdf');
    });
});

test('get without xml or pdf sets json content type', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $http->get('/nfse/ref-123');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), '/nfse/ref-123')
            && $request->hasHeader('Content-Type', 'application/json');
    });
});

test('post sends correct method and data', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $http->post('/nfse', ['key' => 'value']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/nfse')
            && $request['key'] === 'value';
    });
});

test('patch sends correct method and data', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $http->patch('/empresas/123', ['name' => 'test']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'PATCH'
            && str_contains($request->url(), '/empresas/123')
            && $request['name'] === 'test';
    });
});

test('delete sends correct method', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $http->delete('/nfse/ref-123', ['justificativa' => 'test']);

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

test('default timeout is 60 seconds', function () {
    // Reflection is used here because the timeout property is private and its
    // default value is not observable through Http::fake(). This verifies the
    // safety-critical invariant that the default matches the API's expectation.
    $http = new LarafocusHttp;

    expect((new ReflectionProperty($http, 'timeout'))->getValue($http))->toBe(60);
});

test('get passes parameters as query string', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60);

    $http->get('/municipios', ['codigo' => '1234']);

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), 'codigo=1234');
    });
});

test('explicit token overrides config token', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'config-token');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('direct-token');

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('direct-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('default environment from config', function () {
    $http = new LarafocusHttp;

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('base url includes v2 prefix', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test');

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('custom endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom.example.com');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test');

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom.example.com/v2/test');
    });
});

test('token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-token-value');

    $http = (new LarafocusHttp)
        ->environment('production');

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-token-value');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('xml uses config token when not provided', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'xml-config-token');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->isXml();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('xml-config-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('pdf uses config token when not provided', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'pdf-config-token');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->isPdf();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('pdf-config-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('xml uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-xml.example.com');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test')
        ->isXml();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-xml.example.com/v2/test');
    });
});

test('pdf uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-pdf.example.com');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test')
        ->isPdf();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-pdf.example.com/v2/test');
    });
});

test('master token used when useMasterKey is true', function () {
    $this->app['config']->set('larafocus.master_token', 'master-secret');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->useMasterKey();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('master-secret');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('environment token used when useMasterKey is false', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'sandbox-secret');

    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->useMasterKey(false);

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('sandbox-secret');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('default values use environment token', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'env-token');

    $http = (new LarafocusHttp)
        ->environment('sandbox');

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('env-token');

        return $request->hasHeader('Content-Type', 'application/json')
            && $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('xml base url includes v2 prefix', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test')
        ->isXml();

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('pdf base url includes v2 prefix', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test')
        ->isPdf();

    $http->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('xml reads token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-xml-token');

    $http = (new LarafocusHttp)
        ->environment('production')
        ->isXml();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-xml-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('pdf reads token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-pdf-token');

    $http = (new LarafocusHttp)
        ->environment('production')
        ->isPdf();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-pdf-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('xml default environment from config', function () {
    $http = (new LarafocusHttp)
        ->token('test')
        ->isXml();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('pdf default environment from config', function () {
    $http = (new LarafocusHttp)
        ->token('test')
        ->isPdf();

    $http->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});
