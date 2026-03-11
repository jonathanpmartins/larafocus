<?php

use Larafocus\Companies;
use Larafocus\Companies\DTO\EmpresaRequest;
use Larafocus\Companies\DTO\Enums\RegimeTributario;
use Larafocus\Focus;
use Larafocus\Infrastructure\Environment;

covers(Companies::class);

test('list method', function () {
    $response = Focus::companies()->list();
    $this->assertRequest('GET', '/empresas', $response);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), 'offset=0');
    });

    $response = Focus::companies()->list(10);
    $this->assertRequest('GET', '/empresas?offset=10', $response);
});

test('create method', function () {
    $response = Focus::setup(environment: Environment::Production)->companies()->create();
    $this->assertRequest('POST', '/empresas', $response);

    $response = Focus::setup(environment: Environment::Sandbox)->companies()->create();
    $this->assertRequest('POST', '/empresas?dry_run=1', $response);
});

test('create method defaults to config environment for dry_run', function () {
    // config environment is 'sandbox' (set in UnitTestCase)
    $response = Focus::companies()->create();
    $this->assertRequest('POST', '/empresas?dry_run=1', $response);
});

test('get method', function () {
    $response = Focus::companies()->get('company-id');

    $this->assertRequest('GET', '/empresas/company-id', $response);
});

test('update method', function () {
    $response = Focus::setup(environment: Environment::Production)
        ->companies()
        ->update('company-id');

    $this->assertRequest('PUT', '/empresas/company-id', $response);

    $response = Focus::setup(environment: Environment::Sandbox)
        ->companies()
        ->update('company-id');

    $this->assertRequest('PUT', '/empresas/company-id?dry_run=1', $response);
});

test('create method accepts EmpresaRequest DTO', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste',
        cnpj: '12345678000195',
        regime_tributario: RegimeTributario::SimplesNacional,
    );

    $response = Focus::setup(environment: Environment::Production)->companies()->create($request);

    $this->assertRequest('POST', '/empresas', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::setup(environment: Environment::Production)->companies()->create([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000195',
        'regime_tributario' => 1,
    ]);

    $this->assertRequest('POST', '/empresas', $response);
});

test('update method accepts EmpresaRequest DTO', function () {
    $request = new EmpresaRequest(nome: 'Novo Nome');

    $response = Focus::setup(environment: Environment::Production)
        ->companies()
        ->update('company-id', $request);

    $this->assertRequest('PUT', '/empresas/company-id', $response);
});

test('update method accepts array and converts to DTO', function () {
    $response = Focus::setup(environment: Environment::Production)
        ->companies()
        ->update('company-id', ['nome' => 'Novo Nome']);

    $this->assertRequest('PUT', '/empresas/company-id', $response);
});

test('delete method', function () {
    $response = Focus::companies()->delete('company-id');

    $this->assertRequest('DELETE', '/empresas/company-id', $response);
});
