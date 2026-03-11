<?php

declare(strict_types=1);

namespace Larafocus\Hooks\DTO\Enums;

enum WebhookEvent: string
{
    case Nfe = 'nfe';
    case Nfse = 'nfse';
    case Nfsen = 'nfsen';
    case NfceContingencia = 'nfce_contingencia';
    case NfeRecebida = 'nfe_recebida';
    case NfeRecebidaFalhaConsulta = 'nfe_recebida_falha_consulta';
    case NfseRecebida = 'nfse_recebida';
    case CteRecebida = 'cte_recebida';
    case Inutilizacao = 'inutilizacao';
    case Cte = 'cte';
    case Mdfe = 'mdfe';
    case Nfcom = 'nfcom';
}
