<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Larafocus\Infrastructure\FocusResponse;

covers(FocusResponse::class);

function makeResponse(int $status = 200, mixed $body = null): Response
{
    $headers = ['Content-Type' => 'application/json'];
    $encodedBody = $body !== null ? json_encode($body, JSON_THROW_ON_ERROR) : null;

    return new Response(new Psr7Response($status, $headers, $encodedBody));
}

test('fromResponse sets status code', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(201));

    expect($focusResponse->statusCode)->toBe(201);
});

test('fromResponse marks 2xx as success', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(200));

    expect($focusResponse->success)->toBeTrue();
});

test('fromResponse marks 4xx as not success', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(422));

    expect($focusResponse->success)->toBeFalse();
});

test('fromResponse marks 5xx as not success', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(500));

    expect($focusResponse->success)->toBeFalse();
});

test('fromResponse decodes json body', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(200, ['key' => 'value']));

    expect($focusResponse->body)->toBe(['key' => 'value']);
});

test('fromResponse handles null json as empty array', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(200));

    expect($focusResponse->body)->toBe([]);
});

test('fromResponse extracts erros array', function () {
    $errors = [
        ['codigo' => 'campo_invalido', 'mensagem' => 'CNPJ inválido', 'campo' => 'cnpj'],
        ['codigo' => 'campo_obrigatorio', 'mensagem' => 'Razão social é obrigatória'],
    ];

    $focusResponse = FocusResponse::fromResponse(makeResponse(422, ['erros' => $errors]));

    expect($focusResponse->errors)->toBe($errors);
});

test('fromResponse wraps top-level codigo and mensagem as single error', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(404, [
        'codigo' => 'nao_encontrado',
        'mensagem' => 'Nota fiscal não encontrada',
    ]));

    expect($focusResponse->errors)->toBe([
        ['codigo' => 'nao_encontrado', 'mensagem' => 'Nota fiscal não encontrada'],
    ]);
});

test('fromResponse returns empty errors when no error fields present', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(200, ['status' => 'autorizado']));

    expect($focusResponse->errors)->toBe([]);
});

test('fromResponse preserves original response', function () {
    $response = makeResponse(200, ['test' => true]);
    $focusResponse = FocusResponse::fromResponse($response);

    expect($focusResponse->response)->toBe($response);
});

test('fromResponse reindexes erros array keys', function () {
    $errors = [
        5 => ['codigo' => 'erro_a', 'mensagem' => 'Primeiro erro'],
        10 => ['codigo' => 'erro_b', 'mensagem' => 'Segundo erro'],
    ];

    $focusResponse = FocusResponse::fromResponse(makeResponse(422, ['erros' => $errors]));

    expect($focusResponse->errors)->toBe([
        ['codigo' => 'erro_a', 'mensagem' => 'Primeiro erro'],
        ['codigo' => 'erro_b', 'mensagem' => 'Segundo erro'],
    ]);
});

test('fromResponse filters non-array items from erros', function () {
    $focusResponse = FocusResponse::fromResponse(makeResponse(422, [
        'erros' => [
            ['codigo' => 'valid', 'mensagem' => 'Erro válido'],
            'not-an-array',
            42,
            ['codigo' => 'also_valid', 'mensagem' => 'Outro erro'],
        ],
    ]));

    expect($focusResponse->errors)->toBe([
        ['codigo' => 'valid', 'mensagem' => 'Erro válido'],
        ['codigo' => 'also_valid', 'mensagem' => 'Outro erro'],
    ]);
});

test('fromResponse prefers erros array over top-level codigo and mensagem', function () {
    $errors = [['codigo' => 'detalhe', 'mensagem' => 'Erro detalhado']];

    $focusResponse = FocusResponse::fromResponse(makeResponse(422, [
        'codigo' => 'validacao',
        'mensagem' => 'Erro de validação',
        'erros' => $errors,
    ]));

    expect($focusResponse->errors)->toBe($errors);
});
