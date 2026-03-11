<?php

namespace Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Larafocus\Infrastructure\FocusManager;
use Larafocus\Infrastructure\FocusResponse;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

class UnitTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('larafocus.environment', 'sandbox');
        $this->app['config']->set('larafocus.sandbox.token', env('LARAFOCUS_SANDBOX_TOKEN', 'test-token'));
        $this->app['config']->set('larafocus.master_token', env('LARAFOCUS_MASTER_TOKEN', 'test-master-token'));

        $this->app->forgetInstance(FocusManager::class);
        \Larafocus\Focus::clearResolvedInstance(FocusManager::class);

        Http::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [\Larafocus\LarafocusServiceProvider::class];
    }

    public function makePath(string $path): string
    {
        return config()->string('larafocus.prefix').$path;
    }

    public function assertRequest(string $method, string $path, FocusResponse $focusResponse): void
    {
        Http::assertSent(function (Request $request) use ($method, $path) {
            return $request->method() === $method && str_contains($request->url(), $path);
        });

        if (str_contains($path, '?')) {
            [$path, $query] = explode('?', $path, 2);

            $this->assertSame(
                $query,
                $focusResponse->response->effectiveUri()->getQuery()
            );
        }

        $this->assertSame(
            $this->makePath($path),
            $focusResponse->response->effectiveUri()->getPath()
        );
    }
}
