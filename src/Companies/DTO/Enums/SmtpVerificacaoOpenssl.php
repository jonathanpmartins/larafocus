<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum SmtpVerificacaoOpenssl: string
{
    case Peer = 'peer';
    case None = 'none';
}
