<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum SmtpAutenticacao: string
{
    case Plain = 'plain';
    case Login = 'login';
    case CramMd5 = 'cram_md5';
}
