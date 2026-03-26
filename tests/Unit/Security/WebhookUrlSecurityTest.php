<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;
use Larafocus\Shared\InvalidDtoException;

covers(WebhookRequest::class);

test('rejects javascript scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'javascript:alert(1)');
})->throws(InvalidDtoException::class, 'valid URL');

test('rejects file scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'file:///etc/passwd');
})->throws(InvalidDtoException::class, 'must use https or http');

test('rejects ftp scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'ftp://evil.com/payload');
})->throws(InvalidDtoException::class, 'must use https or http');

test('rejects malformed URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'not a url at all');
})->throws(InvalidDtoException::class, 'valid URL');

test('rejects empty URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: '');
})->throws(InvalidDtoException::class, 'valid URL');

test('accepts https URL', function () {
    $request = new WebhookRequest(event: WebhookEvent::Nfse, url: 'https://example.com/webhook');

    expect($request->url)->toBe('https://example.com/webhook');
});

test('accepts http URL for sandbox use', function () {
    $request = new WebhookRequest(event: WebhookEvent::Nfse, url: 'http://localhost:8080/webhook');

    expect($request->url)->toBe('http://localhost:8080/webhook');
});

test('fromArray rejects invalid URL', function () {
    WebhookRequest::fromArray(['event' => 'nfse', 'url' => 'javascript:alert(1)']);
})->throws(InvalidDtoException::class, 'valid URL');
