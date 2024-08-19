<?php

namespace Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Larafocus\LarafocusServiceProvider;

class UnitTestCase extends BaseTestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('larafocus.environment', 'sandbox');
        $this->app['config']->set('larafocus.sandbox.token', env('LARAFOCUS_SANDBOX_TOKEN'));

        // $endpoint = config('larafocus.sandbox.endpoint').LarafocusServiceProvider::$prefix;

        Http::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [LarafocusServiceProvider::class];
    }

    public function makePath(string $path): string
    {
        return LarafocusServiceProvider::$prefix.$path;
    }

    public function assertRequest(string $method, string $path, Response $response): void
    {
        Http::assertSent(function (Request $request) use ($method, $path)
        {
            return $request->method() == $method && str_contains($request->url(), $path);
        });

        if (str_contains($path, '?'))
        {
            list($path, $query) = explode('?', $path, 2);

            $this->assertSame(
                $query,
                $response->effectiveUri()->getQuery()
            );
        }

        $this->assertSame(
            $this->makePath($path),
            $response->effectiveUri()->getPath()
        );
    }

    public function assertRequestNotSent(string $method, string $path): void
    {
        Http::assertNotSent(function (Request $request) use ($method, $path)
        {
            return $request->method() == $method && str_contains($request->url(), $path);
        });
    }

    public function assertValidation(Response $response, string $invalidField, string $errorKey, array $errorParams = []): void
    {
        expect($response)->toBeInstanceOf(Response::class)
            ->and($response->status())->toBe(422);

        $data = $response->json();

        expect($data)->toBeArray()
            ->and($data)->toBeArray()
            ->and(isset($data['message']))->toBeTrue()
            ->and($data['message'])->toBeString()
            ->and(isset($data['errors']))->toBeTrue()
            ->and($data['errors'])->toBeArray()
            ->and($data['errors'])->not->toBeEmpty()
            ->and(isset($data['errors'][$invalidField]))->toBeTrue();

        $message = Lang::get(
            'validation.'.$errorKey,
            array_merge(
                [
                    'attribute' => str($invalidField)
                        ->snake()
                        ->replace('_', ' ')
                ],
                $errorParams,
            )
        );

        expect($data['errors'][$invalidField][0])
            ->toBe($message);
    }
}
