<?php

use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\TransferException as GuzzleTransferException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Exceptions\IndeterminateResultException;
use Larafocus\Exceptions\RequestNotDeliveredException;
use Larafocus\Infrastructure\ContentType;
use Larafocus\Infrastructure\Http as LarafocusHttp;

covers(LarafocusHttp::class);

/** Throwing connection failure whose cause carries a cURL errno. */
function httpConnectionFailure(int $errno): ConnectionException
{
    return new ConnectionException(
        "cURL error {$errno}",
        0,
        new GuzzleConnectException('boom', new GuzzleRequest('POST', 'https://x'), null, ['errno' => $errno]),
    );
}

test('xml content type sets xml headers', function () {
    $http = new LarafocusHttp(
        baseUrl: 'https://example.com/v2',
        token: 'test-token',
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

test('default connect timeout is 10 seconds', function () {
    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect((new ReflectionProperty($http, 'connectTimeout'))->getValue($http))->toBe(10);
});

test('throws when timeout is below 1 second', function () {
    new LarafocusHttp('https://example.com/v2', 'test-token', timeout: 0);
})->throws(InvalidArgumentException::class, 'Timeout must be at least 1 second');

test('throws when connect timeout is below 1 second', function () {
    new LarafocusHttp('https://example.com/v2', 'test-token', connectTimeout: 0);
})->throws(InvalidArgumentException::class, 'Connect timeout must be at least 1 second');

test('accepts the minimum timeouts of 1 second', function () {
    $http = new LarafocusHttp('https://example.com/v2', 'test-token', timeout: 1, connectTimeout: 1);

    expect($http->get('/x')->statusCode)->toBe(200);
});

test('does not follow redirects', function () {
    Http::swap(new Factory);
    Http::fake([
        'https://redirect.test/v2/start' => Http::response('', 302, ['Location' => 'https://redirect.test/v2/final']),
        'https://redirect.test/v2/final' => Http::response(['status' => 'ok'], 200),
    ]);

    $http = new LarafocusHttp('https://redirect.test/v2', 'test-token');

    expect($http->get('/start')->statusCode)->toBe(302);
});

test('an unambiguous non-delivery is surfaced as request not delivered', function () {
    Http::swap(new Factory);
    Http::fake(fn () => throw httpConnectionFailure(7));

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect(fn () => $http->post('/nfse'))->toThrow(RequestNotDeliveredException::class);
});

test('a timeout is surfaced as an indeterminate result', function () {
    Http::swap(new Factory);
    Http::fake(fn () => throw httpConnectionFailure(28));

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect(fn () => $http->post('/nfse'))->toThrow(IndeterminateResultException::class);
});

test('a bare guzzle transfer failure is surfaced as an indeterminate result', function () {
    Http::swap(new Factory);
    Http::fake(fn () => throw new GuzzleTransferException('boom'));

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect(fn () => $http->get('/nfse/ref'))->toThrow(IndeterminateResultException::class);
});

test('an unreadable JSON 2xx is surfaced as an indeterminate result', function () {
    Http::swap(new Factory);
    Http::fake(['*' => Http::response('', 200)]);

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect(fn () => $http->get('/nfse/ref'))->toThrow(IndeterminateResultException::class);
});

test('non-JSON content types are never classified as indeterminate', function () {
    Http::swap(new Factory);
    Http::fake(['*' => Http::response('', 200)]);

    $http = new LarafocusHttp('https://example.com/v2', 'test-token', contentType: ContentType::Xml);

    expect($http->get('/nfse/ref')->statusCode)->toBe(200);
});

test('a 5xx on a write is surfaced as an indeterminate result', function (string $method) {
    Http::swap(new Factory);
    Http::fake(['*' => Http::response('', 500)]);

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    expect(fn () => $http->{$method}('/nfse'))->toThrow(IndeterminateResultException::class);
})->with(['post', 'put', 'patch', 'delete']);

test('a 5xx on a read is returned as a definitive response', function () {
    Http::swap(new Factory);
    Http::fake(['*' => Http::response('', 500)]);

    $http = new LarafocusHttp('https://example.com/v2', 'test-token');

    $response = $http->get('/nfse/ref');

    expect($response->statusCode)->toBe(500)
        ->and($response->success)->toBeFalse();
});
