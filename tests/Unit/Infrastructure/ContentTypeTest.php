<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Infrastructure\ContentType;

covers(ContentType::class);

test('json has correct value', function () {
    expect(ContentType::Json->value)->toBe('application/json');
});

test('xml has correct value', function () {
    expect(ContentType::Xml->value)->toBe('application/xml');
});

test('pdf has correct value', function () {
    expect(ContentType::Pdf->value)->toBe('application/pdf');
});

test('json applies content type and accept json', function () {
    Http::fake();

    $pendingRequest = Http::contentType('text/plain');
    ContentType::Json->applyTo($pendingRequest)->get('https://example.com/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/json')
            && $request->hasHeader('Accept', 'application/json');
    });
});

test('xml applies content type and accept xml', function () {
    Http::fake();

    $pendingRequest = Http::contentType('text/plain');
    ContentType::Xml->applyTo($pendingRequest)->get('https://example.com/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/xml')
            && $request->hasHeader('Accept', 'application/xml');
    });
});

test('pdf applies content type and accept pdf', function () {
    Http::fake();

    $pendingRequest = Http::contentType('text/plain');
    ContentType::Pdf->applyTo($pendingRequest)->get('https://example.com/test');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Content-Type', 'application/pdf')
            && $request->hasHeader('Accept', 'application/pdf');
    });
});
