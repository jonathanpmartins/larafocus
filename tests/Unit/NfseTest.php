<?php

use Larafocus\Focus;
use Larafocus\Nfse;
use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;
use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Nfse\DTO\NfseRequest;
use Larafocus\Nfse\DTO\Prestador;
use Larafocus\Nfse\DTO\Servico;
use Larafocus\Nfse\DTO\Tomador;

covers(Nfse::class);

test('create method with DTO', function () {
    $response = Focus::nfse()->create('01', new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345'),
        tomador: new Tomador(cnpj: '98765432000187'),
        servico: new Servico(
            valor_servicos: 1500.00,
            iss_retido: false,
            item_lista_servico: '1.07',
            discriminacao: 'Dev',
            codigo_municipio: '3550308',
        ),
    ));

    $this->assertRequest('POST', '/nfse?ref=01', $response);
});

test('get method', function () {
    $response = Focus::nfse()->get('unique-reference');

    $this->assertRequest('GET', '/nfse/unique-reference', $response);
});

test('cancel method', function () {
    $response = Focus::nfse()->cancel('unique-reference', new NfseCancelRequest(
        justificativa: 'Cancelamento solicitado',
    ));

    $this->assertRequest('DELETE', '/nfse/unique-reference', $response);
});

test('email method', function () {
    $response = Focus::nfse()->email('unique-reference', new NfseEmailRequest(
        emails: ['test@example.com'],
    ));

    $this->assertRequest('POST', '/nfse/unique-reference/email', $response);
});

test('hook method', function () {
    $response = Focus::nfse()->hook('unique-reference');

    $this->assertRequest('POST', '/nfse/unique-reference/hook', $response);
});

test('create method accepts NfseRequest DTO', function () {
    $request = new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345'),
        tomador: new Tomador(cnpj: '98765432000187'),
        servico: new Servico(
            valor_servicos: 1500.00,
            iss_retido: false,
            item_lista_servico: '1.07',
            discriminacao: 'Desenvolvimento de software',
            codigo_municipio: '3550308',
        ),
    );

    $response = Focus::nfse()->create('REF-001', $request);

    $this->assertRequest('POST', '/nfse?ref=REF-001', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->create('REF-002', [
        'data_emissao' => '2024-01-15T10:30:00-03:00',
        'natureza_operacao' => '1',
        'optante_simples_nacional' => true,
        'prestador' => ['cnpj' => '12345678000195', 'inscricao_municipal' => '12345'],
        'tomador' => ['cnpj' => '98765432000187'],
        'servico' => [
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
        ],
    ]);

    $this->assertRequest('POST', '/nfse?ref=REF-002', $response);
});

test('cancel method accepts NfseCancelRequest DTO', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    $response = Focus::nfse()->cancel('REF-001', $request);

    $this->assertRequest('DELETE', '/nfse/REF-001', $response);
});

test('cancel method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->cancel('REF-002', [
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);

    $this->assertRequest('DELETE', '/nfse/REF-002', $response);
});

test('email method accepts NfseEmailRequest DTO', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    $response = Focus::nfse()->email('REF-001', $request);

    $this->assertRequest('POST', '/nfse/REF-001/email', $response);
});

test('email method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->email('REF-002', [
        'emails' => ['a@example.com', 'b@example.com'],
    ]);

    $this->assertRequest('POST', '/nfse/REF-002/email', $response);
});
