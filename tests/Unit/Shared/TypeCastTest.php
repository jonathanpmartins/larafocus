<?php

declare(strict_types=1);

use Larafocus\Shared\TypeCast;

covers(TypeCast::class);

// toBool

test('toBool casts string "1" to true', function () {
    expect(TypeCast::toBool('1'))->toBeTrue();
});

test('toBool casts string "0" to false', function () {
    expect(TypeCast::toBool('0'))->toBeFalse();
});

test('toBool casts string "true" to true', function () {
    expect(TypeCast::toBool('true'))->toBeTrue();
});

test('toBool casts string "false" to false', function () {
    expect(TypeCast::toBool('false'))->toBeFalse();
});

test('toBool casts string "yes" to true', function () {
    expect(TypeCast::toBool('yes'))->toBeTrue();
});

test('toBool casts string "no" to false', function () {
    expect(TypeCast::toBool('no'))->toBeFalse();
});

test('toBool casts empty string to false', function () {
    expect(TypeCast::toBool(''))->toBeFalse();
});

test('toBool casts arbitrary non-boolean string to false', function () {
    expect(TypeCast::toBool('abc'))->toBeFalse();
});

test('toBool casts int 1 to true', function () {
    expect(TypeCast::toBool(1))->toBeTrue();
});

test('toBool casts int 0 to false', function () {
    expect(TypeCast::toBool(0))->toBeFalse();
});

test('toBool preserves native bool', function () {
    expect(TypeCast::toBool(true))->toBeTrue()
        ->and(TypeCast::toBool(false))->toBeFalse();
});

// nullableBool

test('nullableBool returns null for null', function () {
    expect(TypeCast::nullableBool(null))->toBeNull();
});

test('nullableBool casts string "1" to true', function () {
    expect(TypeCast::nullableBool('1'))->toBeTrue();
});

test('nullableBool casts string "0" to false', function () {
    expect(TypeCast::nullableBool('0'))->toBeFalse();
});

test('nullableBool casts string "true" to true', function () {
    expect(TypeCast::nullableBool('true'))->toBeTrue();
});

test('nullableBool casts string "false" to false', function () {
    expect(TypeCast::nullableBool('false'))->toBeFalse();
});

test('nullableBool casts empty string to false', function () {
    expect(TypeCast::nullableBool(''))->toBeFalse();
});

test('nullableBool preserves native true', function () {
    expect(TypeCast::nullableBool(true))->toBeTrue();
});

test('nullableBool preserves native false', function () {
    expect(TypeCast::nullableBool(false))->toBeFalse();
});

test('nullableBool casts int 1 to true', function () {
    expect(TypeCast::nullableBool(1))->toBeTrue();
});

test('nullableBool casts int 0 to false', function () {
    expect(TypeCast::nullableBool(0))->toBeFalse();
});

// nullableInt

test('nullableInt returns null for null', function () {
    expect(TypeCast::nullableInt(null))->toBeNull();
});

test('nullableInt casts string "123" to int', function () {
    expect(TypeCast::nullableInt('123'))->toBe(123);
});

test('nullableInt casts string "0" to zero', function () {
    expect(TypeCast::nullableInt('0'))->toBe(0);
});

test('nullableInt preserves native int', function () {
    expect(TypeCast::nullableInt(42))->toBe(42);
});

test('nullableInt throws on non-numeric string', function () {
    TypeCast::nullableInt('abc');
})->throws(\InvalidArgumentException::class, "Cannot cast non-numeric string 'abc' to int");

test('nullableInt casts negative numeric string', function () {
    expect(TypeCast::nullableInt('-5'))->toBe(-5);
});
