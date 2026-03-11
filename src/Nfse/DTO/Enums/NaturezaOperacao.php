<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum NaturezaOperacao: string
{
    case TributacaoMunicipio = '1';
    case TributacaoForaMunicipio = '2';
    case Isencao = '3';
    case Imune = '4';
    case ExigibilidadeSuspensaJudicial = '5';
    case ExigibilidadeSuspensaAdministrativa = '6';
}
