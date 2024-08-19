<?php

namespace Tests\Integration;

use Dotenv\Dotenv;
use Larafocus\LarafocusServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Tests\TestCase;

class IntegrationTestCase extends TestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('larafocus.environment', 'sandbox');
        $this->app['config']->set('larafocus.sandbox.token', env('LARAFOCUS_SANDBOX_TOKEN'));
        $this->app['config']->set('larafocus.master_token', env('LARAFOCUS_MASTER_TOKEN'));
    }

    protected function getPackageProviders($app): array
    {
        return [LarafocusServiceProvider::class];
    }

    public function makePath(string $path): string
    {
        return LarafocusServiceProvider::$prefix.$path;
    }
}
