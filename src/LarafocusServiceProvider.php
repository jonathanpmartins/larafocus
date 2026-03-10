<?php

declare(strict_types=1);

namespace Larafocus;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class LarafocusServiceProvider extends ServiceProvider
{
    public static string $prefix = '/v2';

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/larafocus.php' => config_path('larafocus.php'),
        ]);

        $buildClient = self::buildClient(...);

        Http::macro('focus', function (
            ?string $environment = null,
            bool $useMasterKey = false,
            ?string $token = null,
        ) use ($buildClient) {
            $environment = $environment ?: config()->string('larafocus.environment');
            if (! $token) {
                if ($useMasterKey) {
                    $token = config()->string('larafocus.master_token');
                } else {
                    $token = config()->string('larafocus.'.$environment.'.token');
                }
            }

            return $buildClient($environment, $token)
                ->contentType('application/json')
                ->acceptJson();
        });

        Http::macro('focusXml', function (?string $environment = null, ?string $token = null) use ($buildClient) {
            $environment = $environment ?: config()->string('larafocus.environment');
            $token = $token ?: config()->string('larafocus.'.$environment.'.token');

            return $buildClient($environment, $token)
                ->contentType('application/xml')
                ->accept('application/xml');
        });

        Http::macro('focusPdf', function (?string $environment = null, ?string $token = null) use ($buildClient) {
            $environment = $environment ?: config()->string('larafocus.environment');
            $token = $token ?: config()->string('larafocus.'.$environment.'.token');

            return $buildClient($environment, $token)
                ->contentType('application/pdf')
                ->accept('application/pdf');
        });
    }

    private static function buildClient(string $environment, string $token): PendingRequest
    {
        $encodedToken = base64_encode($token);
        $endpoint = config()->string('larafocus.'.$environment.'.endpoint').self::$prefix;

        return Http::withToken($encodedToken, 'Basic')
            ->baseUrl($endpoint);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/larafocus.php', 'larafocus'
        );

        $this->app->singleton(FocusManager::class, fn (): FocusManager => new FocusManager);
    }
}
