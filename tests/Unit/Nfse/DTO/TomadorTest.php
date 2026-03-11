<?php

use Larafocus\Nfse\DTO\Endereco;
use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;
use Larafocus\Nfse\DTO\Tomador;
use Larafocus\Shared\InvalidDtoException;

covers(Tomador::class);

test('constructs with cnpj', function () {
    $t = new Tomador(cnpj: '12345678000195');

    expect($t->cnpj)->toBe('12345678000195')
        ->and($t->cpf)->toBeNull()
        ->and($t->razao_social)->toBeNull()
        ->and($t->nif)->toBeNull()
        ->and($t->motivo_ausencia_nif)->toBeNull()
        ->and($t->inscricao_municipal)->toBeNull()
        ->and($t->email)->toBeNull()
        ->and($t->telefone)->toBeNull()
        ->and($t->endereco)->toBeNull();
});

test('constructs with cpf', function () {
    $t = new Tomador(cpf: '12345678901');

    expect($t->cpf)->toBe('12345678901')
        ->and($t->cnpj)->toBeNull();
});

test('constructs with nested Endereco object', function () {
    $endereco = new Endereco(logradouro: 'Rua Exemplo', uf: 'SP');
    $t = new Tomador(cnpj: '12345678000195', endereco: $endereco);

    expect($t->endereco)->toBe($endereco)
        ->and($t->endereco->logradouro)->toBe('Rua Exemplo');
});

test('constructs with all fields', function () {
    $endereco = new Endereco(logradouro: 'Rua A');
    $t = new Tomador(
        cpf: '12345678901',
        cnpj: '12345678000195',
        razao_social: 'Empresa Teste',
        nif: 'NIF123',
        motivo_ausencia_nif: MotivoAusenciaNif::Dispensado,
        inscricao_municipal: '12345',
        email: 'test@example.com',
        telefone: '11999998888',
        endereco: $endereco,
    );

    expect($t->cpf)->toBe('12345678901')
        ->and($t->cnpj)->toBe('12345678000195')
        ->and($t->razao_social)->toBe('Empresa Teste')
        ->and($t->nif)->toBe('NIF123')
        ->and($t->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::Dispensado)
        ->and($t->inscricao_municipal)->toBe('12345')
        ->and($t->email)->toBe('test@example.com')
        ->and($t->telefone)->toBe('11999998888')
        ->and($t->endereco)->toBe($endereco);
});

test('toArray omits null fields', function () {
    $t = new Tomador(cpf: '12345678901');

    expect($t->toArray())->toBe(['cpf' => '12345678901']);
});

test('toArray includes inscricao_municipal when set', function () {
    $t = new Tomador(inscricao_municipal: '12345');

    expect($t->toArray())->toBe(['inscricao_municipal' => '12345']);
});

test('toArray converts motivo_ausencia_nif enum to value', function () {
    $t = new Tomador(nif: 'ABC123', motivo_ausencia_nif: MotivoAusenciaNif::Dispensado);

    expect($t->toArray())->toBe([
        'nif' => 'ABC123',
        'motivo_ausencia_nif' => '1',
    ]);
});

test('toArray recursively calls endereco toArray', function () {
    $endereco = new Endereco(logradouro: 'Rua B', uf: 'RJ');
    $t = new Tomador(cnpj: '12345678000195', endereco: $endereco);

    expect($t->toArray())->toBe([
        'cnpj' => '12345678000195',
        'endereco' => [
            'logradouro' => 'Rua B',
            'uf' => 'RJ',
        ],
    ]);
});

test('toArray includes all non-null fields', function () {
    $t = new Tomador(
        cpf: '12345678901',
        razao_social: 'Teste',
        email: 'a@b.com',
        telefone: '1199999888',
    );

    expect($t->toArray())->toBe([
        'cpf' => '12345678901',
        'razao_social' => 'Teste',
        'email' => 'a@b.com',
        'telefone' => '1199999888',
    ]);
});

test('fromArray creates instance with all fields including nested endereco', function () {
    $t = Tomador::fromArray([
        'cpf' => '12345678901',
        'cnpj' => '12345678000195',
        'razao_social' => 'Empresa',
        'nif' => 'NIF123',
        'motivo_ausencia_nif' => '2',
        'inscricao_municipal' => '12345',
        'email' => 'test@example.com',
        'telefone' => '11999998888',
        'endereco' => [
            'logradouro' => 'Rua C',
            'uf' => 'SP',
        ],
    ]);

    expect($t->cpf)->toBe('12345678901')
        ->and($t->cnpj)->toBe('12345678000195')
        ->and($t->razao_social)->toBe('Empresa')
        ->and($t->nif)->toBe('NIF123')
        ->and($t->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::NaoExigido)
        ->and($t->inscricao_municipal)->toBe('12345')
        ->and($t->email)->toBe('test@example.com')
        ->and($t->telefone)->toBe('11999998888')
        ->and($t->endereco)->toBeInstanceOf(Endereco::class)
        ->and($t->endereco->logradouro)->toBe('Rua C');
});

test('fromArray without endereco key leaves it null', function () {
    $t = Tomador::fromArray(['cpf' => '12345678901']);

    expect($t->cpf)->toBe('12345678901')
        ->and($t->endereco)->toBeNull();
});

test('fromArray converts motivo_ausencia_nif string to enum', function () {
    $t = Tomador::fromArray(['motivo_ausencia_nif' => '0']);

    expect($t->motivo_ausencia_nif)->toBe(MotivoAusenciaNif::NaoInformado);
});

test('fromArray handles missing optional fields', function () {
    $t = Tomador::fromArray([]);

    expect($t->cpf)->toBeNull()
        ->and($t->cnpj)->toBeNull()
        ->and($t->razao_social)->toBeNull()
        ->and($t->nif)->toBeNull()
        ->and($t->motivo_ausencia_nif)->toBeNull()
        ->and($t->inscricao_municipal)->toBeNull()
        ->and($t->email)->toBeNull()
        ->and($t->telefone)->toBeNull()
        ->and($t->endereco)->toBeNull();
});

test('validates cpf must be 11 digits', function () {
    new Tomador(cpf: '123');
})->throws(InvalidDtoException::class);

test('validates cpf rejects non-digits', function () {
    new Tomador(cpf: '1234567890A');
})->throws(InvalidDtoException::class);

test('validates cnpj must be 14 digits', function () {
    new Tomador(cnpj: '123');
})->throws(InvalidDtoException::class);

test('validates cnpj rejects non-digits', function () {
    new Tomador(cnpj: '1234567800019A');
})->throws(InvalidDtoException::class);

test('validates razao_social max 115 characters', function () {
    new Tomador(razao_social: str_repeat('A', 116));
})->throws(InvalidDtoException::class);

test('accepts razao_social at exactly 115 characters', function () {
    $t = new Tomador(razao_social: str_repeat('A', 115));

    expect($t->razao_social)->toBe(str_repeat('A', 115));
});

test('validates email max 80 characters', function () {
    new Tomador(email: str_repeat('a', 81));
})->throws(InvalidDtoException::class);

test('accepts email at exactly 80 characters', function () {
    $t = new Tomador(email: str_repeat('a', 80));

    expect($t->email)->toBe(str_repeat('a', 80));
});

test('validates telefone pattern must be 10 or 11 digits', function () {
    new Tomador(telefone: '123');
})->throws(InvalidDtoException::class);

test('validates telefone rejects non-digits', function () {
    new Tomador(telefone: '1199999888A');
})->throws(InvalidDtoException::class);

test('accepts telefone with 10 digits', function () {
    $t = new Tomador(telefone: '1199999888');

    expect($t->telefone)->toBe('1199999888');
});

test('accepts telefone with 11 digits', function () {
    $t = new Tomador(telefone: '11999998888');

    expect($t->telefone)->toBe('11999998888');
});
