<?php

use Larafocus\Nfse\DTO\Prestador;
use Larafocus\Shared\InvalidDtoException;

covers(Prestador::class);

test('constructs with required fields', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345');

    expect($p->cnpj)->toBe('12345678000195')
        ->and($p->inscricao_municipal)->toBe('12345')
        ->and($p->codigo_municipio)->toBeNull();
});

test('constructs with all fields', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '3550308');

    expect($p->codigo_municipio)->toBe('3550308');
});

test('toArray omits null codigo_municipio', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345');

    expect($p->toArray())->toBe([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
    ]);
});

test('toArray includes codigo_municipio when set', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '3550308');

    expect($p->toArray())->toHaveKey('codigo_municipio', '3550308');
});

test('fromArray creates instance', function () {
    $p = Prestador::fromArray([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
        'codigo_municipio' => '3550308',
    ]);

    expect($p->cnpj)->toBe('12345678000195')
        ->and($p->codigo_municipio)->toBe('3550308');
});

test('fromArray handles missing optional fields', function () {
    $p = Prestador::fromArray([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
    ]);

    expect($p->codigo_municipio)->toBeNull();
});

test('validates cnpj must be 14 digits', function () {
    new Prestador(cnpj: '123', inscricao_municipal: '12345');
})->throws(InvalidDtoException::class);

test('validates cnpj rejects non-digits', function () {
    new Prestador(cnpj: '1234567800019A', inscricao_municipal: '12345');
})->throws(InvalidDtoException::class);

test('validates codigo_municipio pattern', function () {
    new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '123');
})->throws(InvalidDtoException::class);
