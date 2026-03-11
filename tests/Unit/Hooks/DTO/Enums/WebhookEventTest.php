<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;

covers(WebhookEvent::class);

test('has all twelve cases with correct values', function () {
    expect(WebhookEvent::cases())->toHaveCount(12)
        ->and(WebhookEvent::Nfe->value)->toBe('nfe')
        ->and(WebhookEvent::Nfse->value)->toBe('nfse')
        ->and(WebhookEvent::Nfsen->value)->toBe('nfsen')
        ->and(WebhookEvent::NfceContingencia->value)->toBe('nfce_contingencia')
        ->and(WebhookEvent::NfeRecebida->value)->toBe('nfe_recebida')
        ->and(WebhookEvent::NfeRecebidaFalhaConsulta->value)->toBe('nfe_recebida_falha_consulta')
        ->and(WebhookEvent::NfseRecebida->value)->toBe('nfse_recebida')
        ->and(WebhookEvent::CteRecebida->value)->toBe('cte_recebida')
        ->and(WebhookEvent::Inutilizacao->value)->toBe('inutilizacao')
        ->and(WebhookEvent::Cte->value)->toBe('cte')
        ->and(WebhookEvent::Mdfe->value)->toBe('mdfe')
        ->and(WebhookEvent::Nfcom->value)->toBe('nfcom');
});

test('can be created from string value', function () {
    expect(WebhookEvent::from('nfse'))->toBe(WebhookEvent::Nfse);
});
