<?php

use Larafocus\Nfse\DTO\Enums\RegimeEspecialTributacao;

covers(RegimeEspecialTributacao::class);

test('has all six cases with correct values', function () {
    expect(RegimeEspecialTributacao::cases())->toHaveCount(6)
        ->and(RegimeEspecialTributacao::MicroempresaMunicipal->value)->toBe('1')
        ->and(RegimeEspecialTributacao::Estimativa->value)->toBe('2')
        ->and(RegimeEspecialTributacao::SociedadeProfissionais->value)->toBe('3')
        ->and(RegimeEspecialTributacao::Cooperativa->value)->toBe('4')
        ->and(RegimeEspecialTributacao::MeiSimplesNacional->value)->toBe('5')
        ->and(RegimeEspecialTributacao::MeEppSimplesNacional->value)->toBe('6');
});

test('can be created from string value', function () {
    expect(RegimeEspecialTributacao::from('3'))->toBe(RegimeEspecialTributacao::SociedadeProfissionais);
});
