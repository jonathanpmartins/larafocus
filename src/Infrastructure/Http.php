<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Closure;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http as HttpClient;
use InvalidArgumentException;
use Larafocus\Support\ResponseClassifier;
use Larafocus\Support\TransportFailureClassifier;

readonly class Http
{
    public function __construct(
        private string $baseUrl,
        private string $token,
        private int $timeout = 60,
        private int $connectTimeout = 10,
        private ContentType $contentType = ContentType::Json,
    ) {
        if (trim($this->token) === '') {
            throw new InvalidArgumentException('API token must not be empty. Set your token in the LARAFOCUS_SANDBOX_TOKEN or LARAFOCUS_PRODUCTION_TOKEN environment variable.');
        }

        if ($this->timeout < 1) {
            throw new InvalidArgumentException('Timeout must be at least 1 second; 0 would wait forever.');
        }

        if ($this->connectTimeout < 1) {
            throw new InvalidArgumentException('Connect timeout must be at least 1 second; 0 would wait forever.');
        }
    }

    /** @param array<string, mixed> $parameters */
    public function get(string $uri, array $parameters = []): FocusResponse
    {
        return $this->send(fn (PendingRequest $pendingRequest): Response => $pendingRequest->get($uri, $parameters), isWrite: false);
    }

    /** @param array<string, mixed> $parameters */
    public function post(string $uri, array $parameters = []): FocusResponse
    {
        return $this->send(fn (PendingRequest $pendingRequest): Response => $pendingRequest->post($uri, $parameters), isWrite: true);
    }

    /** @param array<string, mixed> $parameters */
    public function put(string $uri, array $parameters = []): FocusResponse
    {
        return $this->send(fn (PendingRequest $pendingRequest): Response => $pendingRequest->put($uri, $parameters), isWrite: true);
    }

    /** @param array<string, mixed> $parameters */
    public function patch(string $uri, array $parameters = []): FocusResponse
    {
        return $this->send(fn (PendingRequest $pendingRequest): Response => $pendingRequest->patch($uri, $parameters), isWrite: true);
    }

    /** @param array<string, mixed> $parameters */
    public function delete(string $uri, array $parameters = []): FocusResponse
    {
        return $this->send(fn (PendingRequest $pendingRequest): Response => $pendingRequest->delete($uri, $parameters), isWrite: true);
    }

    /**
     * @param  Closure(PendingRequest): Response  $send
     */
    private function send(Closure $send, bool $isWrite): FocusResponse
    {
        try {
            $response = $send($this->buildClient());
        } catch (ConnectionException|RequestException|TransferException $failure) {
            throw TransportFailureClassifier::classify($failure);
        }

        if ($this->contentType === ContentType::Json) {
            ResponseClassifier::guard($response, $isWrite);
        }

        return FocusResponse::fromResponse($response);
    }

    private function buildClient(): PendingRequest
    {
        $pendingRequest = HttpClient::withBasicAuth($this->token, '')
            ->baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withOptions(['allow_redirects' => false]);

        return $this->contentType->applyTo($pendingRequest);
    }
}
