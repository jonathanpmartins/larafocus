<?php

use Larafocus\Companies\DTO\Enums\SmtpVerificacaoOpenssl;

covers(SmtpVerificacaoOpenssl::class);

test('has both cases with correct values', function () {
    expect(SmtpVerificacaoOpenssl::cases())->toHaveCount(2)
        ->and(SmtpVerificacaoOpenssl::Peer->value)->toBe('peer')
        ->and(SmtpVerificacaoOpenssl::None->value)->toBe('none');
});

test('can be created from string value', function () {
    expect(SmtpVerificacaoOpenssl::from('peer'))->toBe(SmtpVerificacaoOpenssl::Peer);
});
