<?php

use Illuminate\Http\Client\Response;
use Larafocus\Api;

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
        ->and($result->json('errors'))->toHaveKey('name');
});
