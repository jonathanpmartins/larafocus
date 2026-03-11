<?php

use Larafocus\Shared\InvalidDtoException;

covers(InvalidDtoException::class);

test('it extends InvalidArgumentException', function () {
    $exception = new InvalidDtoException('test message');

    expect($exception)
        ->toBeInstanceOf(InvalidArgumentException::class)
        ->and($exception->getMessage())->toBe('test message');
});
