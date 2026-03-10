<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Support\Facades\Facade;
use Larafocus\Infrastructure\FocusManager;

/**
 * @mixin FocusManager
 */
class Focus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FocusManager::class;
    }
}
