<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

enum Environment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';
}
