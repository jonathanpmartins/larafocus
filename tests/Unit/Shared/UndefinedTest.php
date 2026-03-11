<?php

use Larafocus\Shared\Undefined;

covers(Undefined::class);

test('has a single Value case', function () {
    expect(Undefined::cases())->toHaveCount(1)
        ->and(Undefined::Value->name)->toBe('Value');
});
