<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Support\ServiceProvider;
use Larafocus\Infrastructure\FocusManager;

class LarafocusServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/larafocus.php' => config_path('larafocus.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/larafocus.php', 'larafocus'
        );

        $this->app->singleton(FocusManager::class, fn (): FocusManager => new FocusManager);
    }
}
