<?php

use Larafocus\Companies\DTO\Enums\RegimeTributario;

covers(RegimeTributario::class);

test('SimplesNacional has value 1', function () {
    expect(RegimeTributario::SimplesNacional->value)->toBe(1)
        ->and(RegimeTributario::from(1))->toBe(RegimeTributario::SimplesNacional);
});

test('SimplesNacionalExcesso has value 2', function () {
    expect(RegimeTributario::SimplesNacionalExcesso->value)->toBe(2)
        ->and(RegimeTributario::from(2))->toBe(RegimeTributario::SimplesNacionalExcesso);
});

test('RegimeNormal has value 3', function () {
    expect(RegimeTributario::RegimeNormal->value)->toBe(3)
        ->and(RegimeTributario::from(3))->toBe(RegimeTributario::RegimeNormal);
});

test('SimplesNacionalMei has value 4', function () {
    expect(RegimeTributario::SimplesNacionalMei->value)->toBe(4)
        ->and(RegimeTributario::from(4))->toBe(RegimeTributario::SimplesNacionalMei);
});

test('has exactly four cases', function () {
    expect(RegimeTributario::cases())->toHaveCount(4);
});
