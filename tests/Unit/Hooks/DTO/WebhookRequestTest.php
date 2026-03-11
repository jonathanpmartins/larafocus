<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;

covers(WebhookRequest::class);

test('constructs with required fields', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    );

    expect($request->event)->toBe(WebhookEvent::Nfse)
        ->and($request->url)->toBe('https://example.com/webhook')
        ->and($request->cnpj)->toBeNull()
        ->and($request->cpf)->toBeNull()
        ->and($request->authorization)->toBeNull()
        ->and($request->authorization_header)->toBeNull();
});

test('constructs with all fields', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfe,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
        cpf: '12345678901',
        authorization: 'Bearer token',
        authorization_header: 'X-Custom-Auth',
    );

    expect($request->cnpj)->toBe('12345678000195')
        ->and($request->cpf)->toBe('12345678901')
        ->and($request->authorization)->toBe('Bearer token')
        ->and($request->authorization_header)->toBe('X-Custom-Auth');
});

test('toArray omits null values', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    );

    expect($request->toArray())->toBe([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
    ]);
});

test('toArray includes all fields when set', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfe,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
        cpf: '12345678901',
        authorization: 'Bearer token',
        authorization_header: 'X-Custom-Auth',
    );

    expect($request->toArray())->toBe([
        'event' => 'nfe',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
        'cpf' => '12345678901',
        'authorization' => 'Bearer token',
        'authorization_header' => 'X-Custom-Auth',
    ]);
});

test('fromArray creates instance with required fields', function () {
    $request = WebhookRequest::fromArray([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
    ]);

    expect($request->event)->toBe(WebhookEvent::Nfse)
        ->and($request->url)->toBe('https://example.com/webhook')
        ->and($request->cnpj)->toBeNull();
});

test('fromArray creates instance with all fields', function () {
    $request = WebhookRequest::fromArray([
        'event' => 'nfe',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
        'cpf' => '12345678901',
        'authorization' => 'Bearer token',
        'authorization_header' => 'X-Custom-Auth',
    ]);

    expect($request->event)->toBe(WebhookEvent::Nfe)
        ->and($request->url)->toBe('https://example.com/webhook')
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->cpf)->toBe('12345678901')
        ->and($request->authorization)->toBe('Bearer token')
        ->and($request->authorization_header)->toBe('X-Custom-Auth');
});
