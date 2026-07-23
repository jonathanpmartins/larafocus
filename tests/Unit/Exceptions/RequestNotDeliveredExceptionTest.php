<?php

use Larafocus\Exceptions\CommunicationException;
use Larafocus\Exceptions\RequestNotDeliveredException;

covers(RequestNotDeliveredException::class);

test('exposes the delivery phase', function () {
    $exception = new RequestNotDeliveredException('connect');

    expect($exception->phase)->toBe('connect');
});

test('message names the phase and states retry is safe', function () {
    $exception = new RequestNotDeliveredException('dns');

    expect($exception->getMessage())
        ->toContain('dns')
        ->toContain('seguro repetir');
});

test('uses a zero code', function () {
    $exception = new RequestNotDeliveredException('tls');

    expect($exception->getCode())->toBe(0);
});

test('keeps the underlying failure as previous', function () {
    $previous = new RuntimeException('boom');

    $exception = new RequestNotDeliveredException('connect', $previous);

    expect($exception->getPrevious())->toBe($previous);
});

test('is a communication exception', function () {
    $exception = new RequestNotDeliveredException('connect');

    expect($exception)
        ->toBeInstanceOf(CommunicationException::class)
        ->toBeInstanceOf(RuntimeException::class);
});
