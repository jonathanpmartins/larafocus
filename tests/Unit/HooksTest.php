<?php

use Larafocus\Focus;
use Larafocus\Hooks;
use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;

covers(Hooks::class);

test('list method', function () {
    $response = Focus::hooks()->list();

    $this->assertRequest('GET', '/hooks', $response);
});

test('create method', function () {
    $response = Focus::hooks()->create(new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    ));

    $this->assertRequest('POST', '/hooks', $response);
});

test('get method', function () {
    $response = Focus::hooks()->get('hook-id');

    $this->assertRequest('GET', '/hooks/hook-id', $response);
});

test('delete method', function () {
    $response = Focus::hooks()->delete('hook-id');

    $this->assertRequest('DELETE', '/hooks/hook-id', $response);
});

test('create method accepts WebhookRequest DTO', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
    );

    $response = Focus::hooks()->create($request);

    $this->assertRequest('POST', '/hooks', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::hooks()->create([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
    ]);

    $this->assertRequest('POST', '/hooks', $response);
});
