<?php

use Larafocus\Nfse\DTO\Endereco;
use Larafocus\Shared\InvalidDtoException;

covers(Endereco::class);

test('constructs with all fields', function () {
    $endereco = new Endereco(
        logradouro: 'Rua Exemplo',
        tipo_logradouro: 'Rua',
        numero: '100',
        complemento: 'Sala 1',
        bairro: 'Centro',
        codigo_municipio: '3550308',
        uf: 'SP',
        cep: '01001000',
    );

    expect($endereco->logradouro)->toBe('Rua Exemplo')
        ->and($endereco->cep)->toBe('01001000');
});

test('constructs with no fields', function () {
    $endereco = new Endereco;

    expect($endereco->logradouro)->toBeNull()
        ->and($endereco->cep)->toBeNull();
});

test('toArray omits null values', function () {
    $endereco = new Endereco(logradouro: 'Rua A', uf: 'SP');

    expect($endereco->toArray())->toBe([
        'logradouro' => 'Rua A',
        'uf' => 'SP',
    ]);
});

test('toArray returns all fields when set', function () {
    $endereco = new Endereco(
        logradouro: 'Rua B',
        tipo_logradouro: 'Rua',
        numero: '200',
        complemento: 'Apto 3',
        bairro: 'Vila',
        codigo_municipio: '1234567',
        uf: 'RJ',
        cep: '20000000',
    );

    expect($endereco->toArray())->toBe([
        'logradouro' => 'Rua B',
        'tipo_logradouro' => 'Rua',
        'numero' => '200',
        'complemento' => 'Apto 3',
        'bairro' => 'Vila',
        'codigo_municipio' => '1234567',
        'uf' => 'RJ',
        'cep' => '20000000',
    ]);
});

test('fromArray maps all fields correctly', function () {
    $endereco = Endereco::fromArray([
        'logradouro' => 'Rua X',
        'tipo_logradouro' => 'Rua',
        'numero' => '42',
        'complemento' => 'Sala 5',
        'bairro' => 'Centro',
        'codigo_municipio' => '1234567',
        'uf' => 'MG',
        'cep' => '30100000',
    ]);

    expect($endereco->logradouro)->toBe('Rua X')
        ->and($endereco->tipo_logradouro)->toBe('Rua')
        ->and($endereco->numero)->toBe('42')
        ->and($endereco->complemento)->toBe('Sala 5')
        ->and($endereco->bairro)->toBe('Centro')
        ->and($endereco->codigo_municipio)->toBe('1234567')
        ->and($endereco->uf)->toBe('MG')
        ->and($endereco->cep)->toBe('30100000');
});

test('fromArray handles missing keys as null', function () {
    $endereco = Endereco::fromArray([
        'logradouro' => 'Rua C',
        'cep' => '30000000',
    ]);

    expect($endereco->logradouro)->toBe('Rua C')
        ->and($endereco->cep)->toBe('30000000')
        ->and($endereco->bairro)->toBeNull();
});

test('validates cep pattern', function () {
    new Endereco(cep: 'invalid');
})->throws(InvalidDtoException::class);

test('validates codigo_municipio pattern', function () {
    new Endereco(codigo_municipio: '123');
})->throws(InvalidDtoException::class);

test('validates uf exact length', function () {
    new Endereco(uf: 'SPP');
})->throws(InvalidDtoException::class);

test('accepts logradouro at max length', function () {
    $endereco = new Endereco(logradouro: str_repeat('A', 125));
    expect($endereco->logradouro)->toBe(str_repeat('A', 125));
});

test('validates logradouro max length', function () {
    new Endereco(logradouro: str_repeat('A', 126));
})->throws(InvalidDtoException::class);

test('accepts tipo_logradouro at max length', function () {
    $endereco = new Endereco(tipo_logradouro: 'Rua');
    expect($endereco->tipo_logradouro)->toBe('Rua');
});

test('validates tipo_logradouro max length', function () {
    new Endereco(tipo_logradouro: 'ABCD');
})->throws(InvalidDtoException::class);

test('accepts numero at max length', function () {
    $endereco = new Endereco(numero: str_repeat('1', 10));
    expect($endereco->numero)->toBe(str_repeat('1', 10));
});

test('validates numero max length', function () {
    new Endereco(numero: str_repeat('1', 11));
})->throws(InvalidDtoException::class);

test('accepts complemento at max length', function () {
    $endereco = new Endereco(complemento: str_repeat('A', 60));
    expect($endereco->complemento)->toBe(str_repeat('A', 60));
});

test('validates complemento max length', function () {
    new Endereco(complemento: str_repeat('A', 61));
})->throws(InvalidDtoException::class);

test('accepts bairro at max length', function () {
    $endereco = new Endereco(bairro: str_repeat('A', 60));
    expect($endereco->bairro)->toBe(str_repeat('A', 60));
});

test('validates bairro max length', function () {
    new Endereco(bairro: str_repeat('A', 61));
})->throws(InvalidDtoException::class);
