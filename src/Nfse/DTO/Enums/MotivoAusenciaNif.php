<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum MotivoAusenciaNif: string
{
    case NaoInformado = '0';
    case Dispensado = '1';
    case NaoExigido = '2';
}
