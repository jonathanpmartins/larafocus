<?php

use Larafocus\Exceptions\CommunicationException;
use Larafocus\Exceptions\IndeterminateResultException;

covers(IndeterminateResultException::class);

test('defaults the phase to null', function () {
    $exception = new IndeterminateResultException('unknown');

    expect($exception->phase)->toBeNull()
        ->and($exception->getMessage())->toBe('unknown')
        ->and($exception->getCode())->toBe(0);
});

test('keeps the given phase and previous', function () {
    $previous = new RuntimeException('boom');

    $exception = new IndeterminateResultException('unknown', 'read', $previous);

    expect($exception->phase)->toBe('read')
        ->and($exception->getPrevious())->toBe($previous);
});

test('is a communication exception', function () {
    $exception = new IndeterminateResultException('unknown');

    expect($exception)
        ->toBeInstanceOf(CommunicationException::class)
        ->toBeInstanceOf(RuntimeException::class);
});

test('fromTransportFailure has no phase and wraps the failure', function () {
    $previous = new RuntimeException('boom');

    $exception = IndeterminateResultException::fromTransportFailure($previous);

    expect($exception->phase)->toBeNull()
        ->and($exception->getPrevious())->toBe($previous)
        ->and($exception->getMessage())->toContain('indeterminada');
});

test('fromTransportFailureWithPhase keeps the phase and wraps the failure', function () {
    $previous = new RuntimeException('boom');

    $exception = IndeterminateResultException::fromTransportFailureWithPhase($previous, 'transfer');

    expect($exception->phase)->toBe('transfer')
        ->and($exception->getPrevious())->toBe($previous)
        ->and($exception->getMessage())->toContain('indeterminada');
});

test('fromUnreadableResponse uses the body phase and reports status and body', function () {
    $exception = IndeterminateResultException::fromUnreadableResponse(200, '<html>oops</html>');

    expect($exception->phase)->toBe('body')
        ->and($exception->getMessage())
        ->toContain('200')
        ->toContain('<html>oops</html>');
});

test('fromServerError has no phase and reports status and body', function () {
    $exception = IndeterminateResultException::fromServerError(503, 'gateway down');

    expect($exception->phase)->toBeNull()
        ->and($exception->getMessage())
        ->toContain('503')
        ->toContain('gateway down');
});

test('an oversized body is truncated so it does not bloat logs', function () {
    $exception = IndeterminateResultException::fromServerError(502, str_repeat('x', 5000));

    expect($exception->getMessage())
        ->toContain('…')
        ->and(mb_strlen($exception->getMessage()))->toBeLessThan(1200);
});
