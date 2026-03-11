<?php

use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;
use Larafocus\Nfse\DTO\Intermediario;
use Larafocus\Shared\InvalidDtoException;

covers(Intermediario::class);

test('constructs with cpf', function () {
    $i = new Intermediario(cpf: '12345678901');

    expect($i->cpf)->toBe('12345678901')
        ->and($i->cnpj)->toBeNull()
        ->and($i->nif)->toBeNull()
        ->and($i->motivo_ausencia_nif)->toBeNull()
        ->and($i->razao_social)->toBeNull()
        ->and($i->inscricao_municipal)->toBeNull();
});

test('constructs with cnpj', function () {
    $i = new Intermediario(cnpj: '12345678000195');

    expect($i->cnpj)->toBe('12345678000195');
});

test('constructs with nif', function () {
    $i = new Intermediario(nif: 'ABC123', motivo_ausencia_nif: MotivoAusenciaNif::Dispensado);

    expect($i->nif)->toBe('ABC123')
        ->and($i->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::Dispensado);
});

test('constructs with all fields', function () {
    $i = new Intermediario(
        cpf: '12345678901',
        cnpj: '12345678000195',
        nif: 'NIF123',
        motivo_ausencia_nif: MotivoAusenciaNif::NaoExigido,
        razao_social: 'Empresa Teste',
        inscricao_municipal: '12345',
    );

    expect($i->cpf)->toBe('12345678901')
        ->and($i->cnpj)->toBe('12345678000195')
        ->and($i->nif)->toBe('NIF123')
        ->and($i->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::NaoExigido)
        ->and($i->razao_social)->toBe('Empresa Teste')
        ->and($i->inscricao_municipal)->toBe('12345');
});

test('toArray omits null fields', function () {
    $i = new Intermediario(cpf: '12345678901');

    expect($i->toArray())->toBe(['cpf' => '12345678901']);
});

test('toArray includes cnpj when set', function () {
    $i = new Intermediario(cnpj: '12345678000195');

    expect($i->toArray())->toBe(['cnpj' => '12345678000195']);
});

test('toArray converts motivo_ausencia_nif enum to value', function () {
    $i = new Intermediario(nif: 'ABC123', motivo_ausencia_nif: MotivoAusenciaNif::Dispensado);

    expect($i->toArray())->toBe([
        'nif' => 'ABC123',
        'motivo_ausencia_nif' => '1',
    ]);
});

test('toArray includes all non-null fields', function () {
    $i = new Intermediario(
        cpf: '12345678901',
        razao_social: 'Teste',
        inscricao_municipal: '99999',
    );

    expect($i->toArray())->toBe([
        'cpf' => '12345678901',
        'razao_social' => 'Teste',
        'inscricao_municipal' => '99999',
    ]);
});

test('fromArray creates instance with all fields', function () {
    $i = Intermediario::fromArray([
        'cpf' => '12345678901',
        'cnpj' => '12345678000195',
        'nif' => 'NIF123',
        'motivo_ausencia_nif' => '2',
        'razao_social' => 'Empresa',
        'inscricao_municipal' => '12345',
    ]);

    expect($i->cpf)->toBe('12345678901')
        ->and($i->cnpj)->toBe('12345678000195')
        ->and($i->nif)->toBe('NIF123')
        ->and($i->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::NaoExigido)
        ->and($i->razao_social)->toBe('Empresa')
        ->and($i->inscricao_municipal)->toBe('12345');
});

test('fromArray converts motivo_ausencia_nif string to enum', function () {
    $i = Intermediario::fromArray(['motivo_ausencia_nif' => '0']);

    expect($i->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::NaoInformado);
});

test('fromArray handles missing optional fields', function () {
    $i = Intermediario::fromArray([]);

    expect($i->cpf)->toBeNull()
        ->and($i->cnpj)->toBeNull()
        ->and($i->nif)->toBeNull()
        ->and($i->motivo_ausencia_nif)->toBeNull()
        ->and($i->razao_social)->toBeNull()
        ->and($i->inscricao_municipal)->toBeNull();
});

test('validates cpf must be 11 digits', function () {
    new Intermediario(cpf: '123');
})->throws(InvalidDtoException::class);

test('validates cpf rejects non-digits', function () {
    new Intermediario(cpf: '1234567890A');
})->throws(InvalidDtoException::class);

test('validates cnpj must be 14 digits', function () {
    new Intermediario(cnpj: '123');
})->throws(InvalidDtoException::class);

test('validates cnpj rejects non-digits', function () {
    new Intermediario(cnpj: '1234567800019A');
})->throws(InvalidDtoException::class);

test('validates razao_social max 115 characters', function () {
    new Intermediario(razao_social: str_repeat('A', 116));
})->throws(InvalidDtoException::class);

test('accepts razao_social at exactly 115 characters', function () {
    $i = new Intermediario(razao_social: str_repeat('A', 115));

    expect($i->razao_social)->toBe(str_repeat('A', 115));
});
