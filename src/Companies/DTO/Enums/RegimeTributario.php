<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum RegimeTributario: int
{
    case SimplesNacional = 1; // @pest-mutate-ignore
    case SimplesNacionalExcesso = 2; // @pest-mutate-ignore
    case RegimeNormal = 3; // @pest-mutate-ignore
    case SimplesNacionalMei = 4; // @pest-mutate-ignore
}
