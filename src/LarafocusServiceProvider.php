<?php

namespace Larafocus;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class LarafocusServiceProvider extends ServiceProvider
{
    public static string $prefix = '/v2';

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/larafocus.php' => config_path('larafocus.php'),
        ]);

        Http::macro('focus', function (
            string $environment = null,
            bool $useMasterKey = false,
            ?string $token = null,
        )
        {
            $environment = $environment ?: config('larafocus.environment');
            if ($useMasterKey)
            {
                $token = base64_encode(config('larafocus.master_token'));
            }
            else
            {
                $token = base64_encode($token ?: config('larafocus.'.$environment.'.token'));
            }
            $endpoint = config('larafocus.'.$environment.'.endpoint').LarafocusServiceProvider::$prefix;

            return Http::withToken($token, 'Basic')
                ->contentType('application/json')
                ->acceptJson()
                ->baseUrl($endpoint);
        });

        Http::macro('focusXml', function (string $environment = null, ?string $token = null)
        {
            $environment = $environment ?: config('larafocus.environment');
            $token = base64_encode($token ?: config('larafocus.'.$environment.'.token'));
            $endpoint = config('larafocus.'.$environment.'.endpoint').LarafocusServiceProvider::$prefix;

            return Http::withToken($token, 'Basic')
                ->contentType('application/xml')
                ->accept('application/xml')
                ->baseUrl($endpoint);
        });

        Http::macro('focusPdf', function (string $environment = null, ?string $token = null)
        {
            $environment = $environment ?: config('larafocus.environment');
            $token = base64_encode($token ?: config('larafocus.'.$environment.'.token'));
            $endpoint = config('larafocus.'.$environment.'.endpoint').LarafocusServiceProvider::$prefix;

            return Http::withToken($token, 'Basic')
                ->contentType('application/pdf')
                ->accept('application/pdf')
                ->baseUrl($endpoint);
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/larafocus.php', 'larafocus'
        );

        $this->app->singleton(Focus::class, function()
        {
            return new Focus();
        });
    }
}
