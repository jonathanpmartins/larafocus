<?php

use Illuminate\Http\Client\Response;
use Larafocus\Api;

covers(Api::class);

test('validate returns validated data on success', function () {
    $api = new class extends Api
    {
        /** @param array<string, mixed> $parameters */
        public function runValidate(array $parameters): Response|array
        {
            return $this->validate($parameters, [
                'name' => 'required|string',
            ]);
        }
    };

    $result = $api->runValidate(['name' => 'John']);

    expect($result)->toBeArray()
        ->and($result['name'])->toBe('John');
});

test('validate returns 422 response on failure', function () {
    $api = new class extends Api
    {
        /** @param array<string, mixed> $parameters */
        public function runValidate(array $parameters): Response|array
        {
            return $this->validate($parameters, [
                'name' => 'required|string',
            ]);
        }
    };

    $result = $api->runValidate([]);

    expect($result)->toBeInstanceOf(Response::class)
        ->and($result->status())->toBe(422)
        ->and($result->json('message'))->toBeString()->not->toBeEmpty()
        ->and($result->json('errors'))->toHaveKey('name')
        ->and($result->header('Content-Type'))->toBe('application/json');
});

test('timeout sets timeout on Api instance', function () {
    $api = new class extends Api
    {
        public function getTimeout(): int
        {
            return $this->timeout;
        }
    };

    $api->timeout(30);

    expect($api->getTimeout())->toBe(30);
});

test('environment sets environment on Api instance', function () {
    $api = new class extends Api
    {
        public function getEnvironment(): ?string
        {
            return $this->environment;
        }
    };

    $api->environment('production');

    expect($api->getEnvironment())->toBe('production');
});

test('useMasterKey sets flag on Api instance', function () {
    $api = new class extends Api
    {
        public function getMasterKey(): bool
        {
            return $this->useMasterKey;
        }
    };

    $api->useMasterKey(true);
    expect($api->getMasterKey())->toBeTrue();

    $api->useMasterKey(false);
    expect($api->getMasterKey())->toBeFalse();
});

test('token sets token on Api instance', function () {
    $api = new class extends Api
    {
        public function getToken(): ?string
        {
            return $this->token;
        }
    };

    $api->token('my-token');

    expect($api->getToken())->toBe('my-token');
});

test('timeout defaults to 60', function () {
    $api = new class extends Api
    {
        public function getTimeout(): int
        {
            return $this->timeout;
        }

        public function getMasterKey(): bool
        {
            return $this->useMasterKey;
        }
    };

    expect($api->getTimeout())->toBe(60)
        ->and($api->getMasterKey())->toBeFalse();

    $api->timeout();

    expect($api->getTimeout())->toBe(60);
});

test('environment defaults to null', function () {
    $api = new class extends Api
    {
        public function getEnvironment(): ?string
        {
            return $this->environment;
        }
    };

    $api->environment();

    expect($api->getEnvironment())->toBeNull();
});

test('useMasterKey defaults to true', function () {
    $api = new class extends Api
    {
        public function getMasterKey(): bool
        {
            return $this->useMasterKey;
        }
    };

    $api->useMasterKey();

    expect($api->getMasterKey())->toBeTrue();
});

test('setter methods return same instance for chaining', function () {
    $api = new class extends Api {};

    $result = $api->timeout(30);
    expect($result)->toBe($api);

    $result = $api->environment('production');
    expect($result)->toBe($api);

    $result = $api->useMasterKey(true);
    expect($result)->toBe($api);

    $result = $api->token('token');
    expect($result)->toBe($api);
});
