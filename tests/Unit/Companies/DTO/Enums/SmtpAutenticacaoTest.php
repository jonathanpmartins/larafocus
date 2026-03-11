<?php

use Larafocus\Companies\DTO\Enums\SmtpAutenticacao;

covers(SmtpAutenticacao::class);

test('has all three cases with correct values', function () {
    expect(SmtpAutenticacao::cases())->toHaveCount(3)
        ->and(SmtpAutenticacao::Plain->value)->toBe('plain')
        ->and(SmtpAutenticacao::Login->value)->toBe('login')
        ->and(SmtpAutenticacao::CramMd5->value)->toBe('cram_md5');
});

test('can be created from string value', function () {
    expect(SmtpAutenticacao::from('login'))->toBe(SmtpAutenticacao::Login);
});
