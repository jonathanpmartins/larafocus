<?php

use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;

covers(NaturezaOperacao::class);

test('has all six cases with correct values', function () {
    expect(NaturezaOperacao::cases())->toHaveCount(6)
        ->and(NaturezaOperacao::TributacaoMunicipio->value)->toBe('1')
        ->and(NaturezaOperacao::TributacaoForaMunicipio->value)->toBe('2')
        ->and(NaturezaOperacao::Isencao->value)->toBe('3')
        ->and(NaturezaOperacao::Imune->value)->toBe('4')
        ->and(NaturezaOperacao::ExigibilidadeSuspensaJudicial->value)->toBe('5')
        ->and(NaturezaOperacao::ExigibilidadeSuspensaAdministrativa->value)->toBe('6');
});

test('can be created from string value', function () {
    expect(NaturezaOperacao::from('1'))->toBe(NaturezaOperacao::TributacaoMunicipio);
});
