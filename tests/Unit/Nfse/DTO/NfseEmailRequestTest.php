<?php

use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseEmailRequest::class);

test('constructs with valid emails', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com', 'b@example.com']);

    expect($request->emails)->toBe(['a@example.com', 'b@example.com']);
});

test('toArray returns emails', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    expect($request->toArray())->toBe([
        'emails' => ['a@example.com'],
    ]);
});

test('fromArray creates instance', function () {
    $request = NfseEmailRequest::fromArray([
        'emails' => ['a@example.com', 'b@example.com'],
    ]);

    expect($request->emails)->toBe(['a@example.com', 'b@example.com']);
});

test('validates emails cannot be empty', function () {
    new NfseEmailRequest(emails: []);
})->throws(InvalidDtoException::class);

test('validates emails maximum count', function () {
    new NfseEmailRequest(emails: array_map(
        fn (int $i): string => "user{$i}@example.com",
        range(1, 11),
    ));
})->throws(InvalidDtoException::class);

test('validates emails at exactly 10 items passes', function () {
    $emails = array_map(
        fn (int $i): string => "user{$i}@example.com",
        range(1, 10),
    );
    $request = new NfseEmailRequest(emails: $emails);

    expect($request->emails)->toHaveCount(10);
});

test('validates emails with single item passes', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    expect($request->emails)->toHaveCount(1);
});
