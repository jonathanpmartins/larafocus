<?php

use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Client\ConnectionException;
use Larafocus\Exceptions\IndeterminateResultException;
use Larafocus\Exceptions\RequestNotDeliveredException;
use Larafocus\Support\TransportFailureClassifier;

covers(TransportFailureClassifier::class);

/** @param array<string, mixed> $context */
function tfcConnect(array $context): GuzzleConnectException
{
    return new GuzzleConnectException('cURL error', new GuzzleRequest('POST', 'https://focusnfe.test'), null, $context);
}

test('unambiguous non-delivery errnos are safe to retry', function (int $errno, string $phase) {
    $result = TransportFailureClassifier::classify(tfcConnect(['errno' => $errno]));

    expect($result)
        ->toBeInstanceOf(RequestNotDeliveredException::class)
        ->and($result->phase)->toBe($phase);
})->with([
    'dns (6)' => [6, 'dns'],
    'connect (7)' => [7, 'connect'],
    'tls handshake (35)' => [35, 'tls'],
    'tls cert (58)' => [58, 'tls'],
    'tls cert (60)' => [60, 'tls'],
]);

test('read-phase errnos are indeterminate', function (int $errno) {
    $result = TransportFailureClassifier::classify(tfcConnect(['errno' => $errno]));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBe('read');
})->with([
    'timeout (28)' => [28],
    'got nothing (52)' => [52],
]);

test('transfer-phase errnos are indeterminate', function (int $errno) {
    $result = TransportFailureClassifier::classify(tfcConnect(['errno' => $errno]));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBe('transfer');
})->with([
    'partial file (18)' => [18],
    'recv error (56)' => [56],
    'http2 (92)' => [92],
]);

test('unknown or foreign errnos fall back to indeterminate without a phase', function (int $errno) {
    $result = TransportFailureClassifier::classify(tfcConnect(['errno' => $errno]));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBeNull();
})->with([
    'send error (55) is not bucketed' => [55],
    'arbitrary errno' => [999],
]);

test('a missing errno is indeterminate without a phase', function () {
    $result = TransportFailureClassifier::classify(tfcConnect([]));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBeNull();
});

test('a non-integer errno is ignored', function () {
    $result = TransportFailureClassifier::classify(tfcConnect(['errno' => 'not-an-int']));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBeNull();
});

test('reads the errno from a RequestException', function () {
    $requestException = new GuzzleRequestException(
        'cURL error',
        new GuzzleRequest('GET', 'https://focusnfe.test'),
        null,
        null,
        ['errno' => 18],
    );

    $result = TransportFailureClassifier::classify($requestException);

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBe('transfer');
});

test('walks the exception chain to find the cURL errno', function () {
    $wrapped = new ConnectionException('cURL error 28', 0, tfcConnect(['errno' => 28]));

    $result = TransportFailureClassifier::classify($wrapped);

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBe('read');
});

test('a failure with no cURL context is indeterminate without a phase', function () {
    $result = TransportFailureClassifier::classify(new RuntimeException('no curl here'));

    expect($result)
        ->toBeInstanceOf(IndeterminateResultException::class)
        ->and($result->phase)->toBeNull();
});

test('keeps the original failure as the exception cause', function () {
    $failure = tfcConnect(['errno' => 7]);

    $result = TransportFailureClassifier::classify($failure);

    expect($result->getPrevious())->toBe($failure);
});
