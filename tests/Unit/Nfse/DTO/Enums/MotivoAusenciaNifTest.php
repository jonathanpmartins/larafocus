<?php

use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;

covers(MotivoAusenciaNif::class);

test('has all three cases with correct values', function () {
    expect(MotivoAusenciaNif::cases())->toHaveCount(3)
        ->and(MotivoAusenciaNif::NaoInformado->value)->toBe('0')
        ->and(MotivoAusenciaNif::Dispensado->value)->toBe('1')
        ->and(MotivoAusenciaNif::NaoExigido->value)->toBe('2');
});

test('can be created from string value', function () {
    expect(MotivoAusenciaNif::from('1'))->toBe(MotivoAusenciaNif::Dispensado);
});
