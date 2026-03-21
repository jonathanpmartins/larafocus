<?php

use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;
use Larafocus\Nfse\DTO\Enums\RegimeEspecialTributacao;
use Larafocus\Nfse\DTO\Intermediario;
use Larafocus\Nfse\DTO\NfseRequest;
use Larafocus\Nfse\DTO\Prestador;
use Larafocus\Nfse\DTO\Servico;
use Larafocus\Nfse\DTO\Tomador;
use Larafocus\Shared\InvalidDtoException;

covers(NfseRequest::class);

function makeServico(): Servico
{
    return new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '3550308',
    );
}

function makePrestador(): Prestador
{
    return new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345');
}

function makeTomador(): Tomador
{
    return new Tomador(cnpj: '98765432000187');
}

test('constructs with all required fields', function () {
    $request = new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: makePrestador(),
        tomador: makeTomador(),
        servico: makeServico(),
    );

    expect($request->data_emissao)->toBe('2024-01-15T10:30:00-03:00')
        ->and($request->natureza_operacao)->toBe(NaturezaOperacao::TributacaoMunicipio)
        ->and($request->optante_simples_nacional)->toBeTrue()
        ->and($request->prestador)->toBeInstanceOf(Prestador::class)
        ->and($request->tomador)->toBeInstanceOf(Tomador::class)
        ->and($request->servico)->toBeInstanceOf(Servico::class)
        ->and($request->regime_especial_tributacao)->toBeNull()
        ->and($request->incentivador_cultural)->toBeNull()
        ->and($request->intermediario)->toBeNull()
        ->and($request->codigo_obra)->toBeNull()
        ->and($request->art)->toBeNull()
        ->and($request->numero_nfse_substituido)->toBeNull()
        ->and($request->numero_rps_substituido)->toBeNull()
        ->and($request->serie_rps_substituido)->toBeNull()
        ->and($request->tipo_rps_substituido)->toBeNull();
});

test('toArray converts enums and nested DTOs, omits null optionals', function () {
    $request = new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: makePrestador(),
        tomador: makeTomador(),
        servico: makeServico(),
    );

    $array = $request->toArray();

    expect($array)->toBe([
        'data_emissao' => '2024-01-15T10:30:00-03:00',
        'natureza_operacao' => '1',
        'optante_simples_nacional' => true,
        'prestador' => [
            'cnpj' => '12345678000195',
            'inscricao_municipal' => '12345',
        ],
        'tomador' => [
            'cnpj' => '98765432000187',
        ],
        'servico' => [
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
        ],
    ]);
});

test('toArray includes optional fields when set', function () {
    $request = new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoForaMunicipio,
        optante_simples_nacional: false,
        prestador: makePrestador(),
        tomador: makeTomador(),
        servico: makeServico(),
        regime_especial_tributacao: RegimeEspecialTributacao::MicroempresaMunicipal,
        incentivador_cultural: true,
        intermediario: new Intermediario(cpf: '12345678901'),
        codigo_obra: 'OBRA123',
        art: 'ART456',
        numero_nfse_substituido: 'NFSe789',
        numero_rps_substituido: 'RPS001',
        serie_rps_substituido: 'S1',
        tipo_rps_substituido: 'T1',
    );

    $array = $request->toArray();

    expect($array['regime_especial_tributacao'])->toBe('1')
        ->and($array['incentivador_cultural'])->toBeTrue()
        ->and($array['intermediario'])->toBe(['cpf' => '12345678901'])
        ->and($array['codigo_obra'])->toBe('OBRA123')
        ->and($array['art'])->toBe('ART456')
        ->and($array['numero_nfse_substituido'])->toBe('NFSe789')
        ->and($array['numero_rps_substituido'])->toBe('RPS001')
        ->and($array['serie_rps_substituido'])->toBe('S1')
        ->and($array['tipo_rps_substituido'])->toBe('T1');
});

test('fromArray converts enum strings to enums and nested arrays to DTOs', function () {
    $request = NfseRequest::fromArray([
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
        'regime_especial_tributacao' => '3',
        'incentivador_cultural' => false,
        'intermediario' => ['cpf' => '12345678901'],
        'codigo_obra' => 'OBRA123',
        'art' => 'ART456',
        'numero_nfse_substituido' => 'NFSe789',
        'numero_rps_substituido' => 'RPS001',
        'serie_rps_substituido' => 'S1',
        'tipo_rps_substituido' => 'T1',
    ]);

    expect($request->natureza_operacao)->toBe(NaturezaOperacao::TributacaoMunicipio)
        ->and($request->regime_especial_tributacao)->toBe(RegimeEspecialTributacao::SociedadeProfissionais)
        ->and($request->incentivador_cultural)->toBeFalse()
        ->and($request->prestador)->toBeInstanceOf(Prestador::class)
        ->and($request->tomador)->toBeInstanceOf(Tomador::class)
        ->and($request->servico)->toBeInstanceOf(Servico::class)
        ->and($request->intermediario)->toBeInstanceOf(Intermediario::class)
        ->and($request->codigo_obra)->toBe('OBRA123')
        ->and($request->art)->toBe('ART456')
        ->and($request->numero_nfse_substituido)->toBe('NFSe789')
        ->and($request->numero_rps_substituido)->toBe('RPS001')
        ->and($request->serie_rps_substituido)->toBe('S1')
        ->and($request->tipo_rps_substituido)->toBe('T1');
});

test('fromArray handles missing optional fields', function () {
    $request = NfseRequest::fromArray([
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

    expect($request->regime_especial_tributacao)->toBeNull()
        ->and($request->incentivador_cultural)->toBeNull()
        ->and($request->intermediario)->toBeNull()
        ->and($request->codigo_obra)->toBeNull()
        ->and($request->art)->toBeNull()
        ->and($request->numero_nfse_substituido)->toBeNull()
        ->and($request->numero_rps_substituido)->toBeNull()
        ->and($request->serie_rps_substituido)->toBeNull()
        ->and($request->tipo_rps_substituido)->toBeNull();
});

test('validates codigo_obra max 15 characters', function () {
    new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: makePrestador(),
        tomador: makeTomador(),
        servico: makeServico(),
        codigo_obra: str_repeat('A', 16),
    );
})->throws(InvalidDtoException::class);

test('fromArray casts string values for bool fields', function () {
    $request = NfseRequest::fromArray([
        'data_emissao' => '2024-01-15T10:30:00-03:00',
        'natureza_operacao' => '1',
        'optante_simples_nacional' => '1',
        'prestador' => ['cnpj' => '12345678000195', 'inscricao_municipal' => '12345'],
        'tomador' => ['cnpj' => '98765432000187'],
        'servico' => [
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
        ],
        'incentivador_cultural' => '0',
    ]);

    expect($request->optante_simples_nacional)->toBeTrue()
        ->and($request->incentivador_cultural)->toBeFalse();
});

test('fromArray toArray round-trip preserves data', function () {
    // Keys ordered to match toArray() output order
    $data = [
        'data_emissao' => '2024-01-15T10:30:00-03:00',
        'natureza_operacao' => '1',
        'optante_simples_nacional' => true,
        'regime_especial_tributacao' => '3',
        'incentivador_cultural' => false,
        'prestador' => ['cnpj' => '12345678000195', 'inscricao_municipal' => '12345', 'codigo_municipio' => '3550308'],
        'tomador' => [
            'cpf' => '12345678901',
            'razao_social' => 'Empresa Ltda',
            'email' => 'contato@empresa.com',
            'endereco' => [
                'logradouro' => 'Rua Exemplo',
                'numero' => '100',
                'bairro' => 'Centro',
                'codigo_municipio' => '3550308',
                'uf' => 'SP',
                'cep' => '01001000',
            ],
        ],
        'servico' => [
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
            'aliquota' => 0.05,
        ],
        'intermediario' => ['cpf' => '12345678901', 'razao_social' => 'Intermediário'],
        'codigo_obra' => 'OBRA123',
        'art' => 'ART456',
    ];

    $result = NfseRequest::fromArray($data)->toArray();

    expect($result)->toBe($data);
});

test('accepts codigo_obra at exactly 15 characters', function () {
    $request = new NfseRequest(
        data_emissao: '2024-01-15T10:30:00-03:00',
        natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
        optante_simples_nacional: true,
        prestador: makePrestador(),
        tomador: makeTomador(),
        servico: makeServico(),
        codigo_obra: str_repeat('A', 15),
    );

    expect($request->codigo_obra)->toBe(str_repeat('A', 15));
});
