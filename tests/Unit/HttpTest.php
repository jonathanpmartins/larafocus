<?php

use Illuminate\Support\Facades\Http;
use Larafocus\Http as LarafocusHttp;

test('get with xml format uses focusXml macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isXml();

    $response = $http->get('/nfse/ref-123');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET' && str_contains($request->url(), '/nfse/ref-123');
    });
});

test('get with pdf format uses focusPdf macro', function () {
    $http = (new LarafocusHttp)
        ->environment('sandbox')
        ->token('test-token')
        ->timeout(60)
        ->isPdf();

    $response = $http->get('/nfse/ref-123');

    Http::assertSent(function ($request) {
        return $request->method() === 'GET' && str_contains($request->url(), '/nfse/ref-123');
    });
});
