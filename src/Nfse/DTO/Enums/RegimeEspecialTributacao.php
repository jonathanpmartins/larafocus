<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum RegimeEspecialTributacao: string
{
    case MicroempresaMunicipal = '1';
    case Estimativa = '2';
    case SociedadeProfissionais = '3';
    case Cooperativa = '4';
    case MeiSimplesNacional = '5';
    case MeEppSimplesNacional = '6';
}
