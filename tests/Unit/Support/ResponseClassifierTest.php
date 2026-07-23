<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Larafocus\Exceptions\IndeterminateResultException;
use Larafocus\Support\ResponseClassifier;

covers(ResponseClassifier::class);

/** @param array<string, string> $headers */
function clientResponse(int $status, ?string $body = null, array $headers = []): Response
{
    return new Response(new Psr7Response($status, $headers, $body));
}

/** @param array<string, mixed> $body */
function jsonClientResponse(int $status, array $body): Response
{
    return clientResponse($status, json_encode($body, JSON_THROW_ON_ERROR), ['Content-Type' => 'application/json']);
}

test('a 5xx write with no provider envelope may have been processed', function () {
    try {
        ResponseClassifier::guard(clientResponse(500, 'Server Error Page'), isWrite: true);
        $this->fail('Expected an IndeterminateResultException.');
    } catch (IndeterminateResultException $exception) {
        expect($exception->phase)->toBeNull()
            ->and($exception->getMessage())
            ->toContain('500')
            ->toContain('Server Error Page');
    }
});

test('a 5xx write with a structured provider error is a definitive verdict', function (array $body) {
    expect(fn () => ResponseClassifier::guard(jsonClientResponse(500, $body), isWrite: true))
        ->not->toThrow(IndeterminateResultException::class);
})->with([
    'codigo + mensagem' => [['codigo' => 'erro_interno', 'mensagem' => 'falhou']],
    'erros array' => [['erros' => [['codigo' => 'x', 'mensagem' => 'y']]]],
]);

test('a 5xx write whose error shape is not a real envelope stays indeterminate', function (array $body) {
    expect(fn () => ResponseClassifier::guard(jsonClientResponse(500, $body), isWrite: true))
        ->toThrow(IndeterminateResultException::class);
})->with([
    'erros is not an array' => [['erros' => 'nope']],
    'codigo without mensagem' => [['codigo' => 'erro_interno']],
]);

test('a 5xx write whose body is valid JSON but not an object stays indeterminate', function () {
    expect(fn () => ResponseClassifier::guard(clientResponse(500, '"just a string"'), isWrite: true))
        ->toThrow(IndeterminateResultException::class);
});

test('a 5xx read is left as a definitive response', function () {
    expect(fn () => ResponseClassifier::guard(clientResponse(500, 'Server Error Page'), isWrite: false))
        ->not->toThrow(IndeterminateResultException::class);
});

test('any status below the server-error boundary on a write is definitive', function (int $status) {
    expect(fn () => ResponseClassifier::guard(clientResponse($status), isWrite: true))
        ->not->toThrow(IndeterminateResultException::class);
})->with([
    'bad request (400)' => [400],
    'just below 500 (499)' => [499],
]);

test('an empty 2xx is unreadable', function () {
    expect(fn () => ResponseClassifier::guard(clientResponse(200), isWrite: false))
        ->toThrow(IndeterminateResultException::class);
});

test('a non-JSON 2xx reports the body under the body phase', function () {
    try {
        ResponseClassifier::guard(clientResponse(200, 'not json at all'), isWrite: false);
        $this->fail('Expected an IndeterminateResultException.');
    } catch (IndeterminateResultException $exception) {
        expect($exception->phase)->toBe('body')
            ->and($exception->getMessage())
            ->toContain('200')
            ->toContain('not json at all');
    }
});

test('a 2xx with a readable JSON body is left as a definitive response', function () {
    expect(fn () => ResponseClassifier::guard(jsonClientResponse(200, ['status' => 'ok']), isWrite: false))
        ->not->toThrow(IndeterminateResultException::class);
});

test('a 204 No Content is not treated as unreadable', function () {
    expect(fn () => ResponseClassifier::guard(clientResponse(204), isWrite: false))
        ->not->toThrow(IndeterminateResultException::class);
});
