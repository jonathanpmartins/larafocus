<?php

use Larafocus\Companies\DTO\Enums\OrientacaoDanfe;

covers(OrientacaoDanfe::class);

test('has both cases with correct values', function () {
    expect(OrientacaoDanfe::cases())->toHaveCount(2)
        ->and(OrientacaoDanfe::Portrait->value)->toBe('portrait')
        ->and(OrientacaoDanfe::Landscape->value)->toBe('landscape');
});

test('can be created from string value', function () {
    expect(OrientacaoDanfe::from('landscape'))->toBe(OrientacaoDanfe::Landscape);
});
