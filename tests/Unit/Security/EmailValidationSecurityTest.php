<?php

use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseEmailRequest::class);

test('rejects plaintext string that is not an email', function () {
    new NfseEmailRequest(emails: ['not-an-email']);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects email with header injection attempt', function () {
    new NfseEmailRequest(emails: ["user@example.com\r\nBCC:attacker@evil.com"]);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects empty string as email', function () {
    new NfseEmailRequest(emails: ['']);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects mixed valid and invalid emails on the invalid one', function () {
    new NfseEmailRequest(emails: ['valid@example.com', 'invalid']);
})->throws(InvalidDtoException::class, '"invalid" is not a valid email');

test('accepts valid email addresses', function () {
    $request = new NfseEmailRequest(emails: ['user@example.com', 'admin@company.org']);

    expect($request->emails)->toBe(['user@example.com', 'admin@company.org']);
});

test('fromArray rejects invalid email', function () {
    NfseEmailRequest::fromArray(['emails' => ['bad-email']]);
})->throws(InvalidDtoException::class, 'not a valid email');
