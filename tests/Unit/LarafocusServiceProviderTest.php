<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\LarafocusServiceProvider;

test('focusXml macro sets xml content type', function () {
    Http::fake();

    Http::focusXml(environment: 'sandbox', token: 'my-token')->get('/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/xml');
    });
});

test('focusPdf macro sets pdf content type', function () {
    Http::fake();

    Http::focusPdf(environment: 'sandbox', token: 'my-token')->get('/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/pdf');
    });
});

test('focus macro sets json content type', function () {
    Http::fake();

    Http::focus(environment: 'sandbox', token: 'my-token')->get('/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/json');
    });
});

test('focus macro uses master token when useMasterKey is true', function () {
    $this->app['config']->set('larafocus.master_token', 'master-secret');
    Http::fake();

    Http::focus(environment: 'sandbox', useMasterKey: true)->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('master-secret');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focus macro uses environment token when useMasterKey is false', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'sandbox-secret');
    Http::fake();

    Http::focus(environment: 'sandbox')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('sandbox-secret');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focus macro uses provided token over config', function () {
    Http::fake();

    Http::focus(environment: 'sandbox', token: 'direct-token')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('direct-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focus macro defaults environment from config', function () {
    Http::fake();

    Http::focus()->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('focusXml macro defaults environment from config', function () {
    Http::fake();

    Http::focusXml()->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('focusPdf macro defaults environment from config', function () {
    Http::fake();

    Http::focusPdf()->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'homologacao.focusnfe.com.br');
    });
});

test('focusXml macro uses provided token', function () {
    Http::fake();

    Http::focusXml(environment: 'sandbox', token: 'xml-token')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('xml-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focusPdf macro uses provided token', function () {
    Http::fake();

    Http::focusPdf(environment: 'sandbox', token: 'pdf-token')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('pdf-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('service provider registers singleton', function () {
    $focus1 = app(\Larafocus\Focus::class);
    $focus2 = app(\Larafocus\Focus::class);

    expect($focus1)->toBeInstanceOf(\Larafocus\Focus::class)
        ->and($focus1)->toBe($focus2);
});

test('service provider merges config', function () {
    expect(config('larafocus.environment'))->not->toBeNull()
        ->and(config('larafocus.sandbox.endpoint'))->toBe('https://homologacao.focusnfe.com.br')
        ->and(config('larafocus.production.endpoint'))->toBe('https://api.focusnfe.com.br');
});

test('service provider publishes config file', function () {
    $publishes = \Illuminate\Support\ServiceProvider::$publishes;

    $found = false;
    foreach ($publishes as $provider => $paths) {
        foreach ($paths as $from => $to) {
            if (str_contains($from, 'config/larafocus.php')) {
                expect(file_exists($from))->toBeTrue();
                $found = true;
            }
        }
    }

    expect($found)->toBeTrue();
});

test('prefix is set to v2', function () {
    expect(LarafocusServiceProvider::$prefix)->toBe('/v2');
});

test('focus macro base url includes prefix', function () {
    Http::fake();

    Http::focus(environment: 'sandbox', token: 'test')->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('focusXml macro base url includes prefix', function () {
    Http::fake();

    Http::focusXml(environment: 'sandbox', token: 'test')->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('focusPdf macro base url includes prefix', function () {
    Http::fake();

    Http::focusPdf(environment: 'sandbox', token: 'test')->get('/nfse/ref');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), '/v2/nfse/ref');
    });
});

test('focusXml macro uses config token when not provided', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'xml-config-token');
    Http::fake();

    Http::focusXml(environment: 'sandbox')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('xml-config-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focusPdf macro uses config token when not provided', function () {
    $this->app['config']->set('larafocus.sandbox.token', 'pdf-config-token');
    Http::fake();

    Http::focusPdf(environment: 'sandbox')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('pdf-config-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focusXml macro uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-xml.example.com');
    Http::fake();

    Http::focusXml(environment: 'sandbox', token: 'test')->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-xml.example.com/v2/test');
    });
});

test('focusPdf macro uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-pdf.example.com');
    Http::fake();

    Http::focusPdf(environment: 'sandbox', token: 'test')->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-pdf.example.com/v2/test');
    });
});

test('focus macro uses correct endpoint from config', function () {
    $this->app['config']->set('larafocus.sandbox.endpoint', 'https://custom-json.example.com');
    Http::fake();

    Http::focus(environment: 'sandbox', token: 'test')->get('/test');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'custom-json.example.com/v2/test');
    });
});

test('focus macro reads token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-token-value');
    Http::fake();

    Http::focus(environment: 'production')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-token-value');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focusXml macro reads token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-xml-token');
    Http::fake();

    Http::focusXml(environment: 'production')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-xml-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});

test('focusPdf macro reads token from correct environment config key', function () {
    $this->app['config']->set('larafocus.production.token', 'prod-pdf-token');
    Http::fake();

    Http::focusPdf(environment: 'production')->get('/test');

    Http::assertSent(function (Request $request) {
        $expectedToken = base64_encode('prod-pdf-token');

        return $request->hasHeader('Authorization', 'Basic '.$expectedToken);
    });
});
