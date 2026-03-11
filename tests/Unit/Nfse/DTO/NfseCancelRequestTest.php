<?php

use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseCancelRequest::class);

test('constructs with valid justificativa', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    expect($request->justificativa)->toBe('Cancelamento solicitado pelo tomador');
});

test('toArray returns justificativa', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    expect($request->toArray())->toBe([
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);
});

test('fromArray creates instance', function () {
    $request = NfseCancelRequest::fromArray([
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);

    expect($request->justificativa)->toBe('Cancelamento solicitado pelo tomador');
});

test('validates justificativa minimum length', function () {
    new NfseCancelRequest(justificativa: 'short');
})->throws(InvalidDtoException::class);

test('validates justificativa at exactly 15 characters passes', function () {
    $request = new NfseCancelRequest(justificativa: str_repeat('A', 15));

    expect($request->justificativa)->toBe(str_repeat('A', 15));
});

test('validates justificativa at exactly 14 characters fails', function () {
    new NfseCancelRequest(justificativa: str_repeat('A', 14));
})->throws(InvalidDtoException::class);

test('validates justificativa maximum length', function () {
    new NfseCancelRequest(justificativa: str_repeat('A', 256));
})->throws(InvalidDtoException::class);

test('validates justificativa at exactly 255 characters passes', function () {
    $request = new NfseCancelRequest(justificativa: str_repeat('A', 255));

    expect($request->justificativa)->toBe(str_repeat('A', 255));
});
