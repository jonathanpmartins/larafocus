<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Hooks\DTO\WebhookRequest;
use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;

/**
 * @phpstan-import-type WebhookRequestArray from WebhookRequest
 */
readonly class Hooks
{
    public function __construct(private Http $http) {}

    public function list(): FocusResponse
    {
        return $this->http->get('/hooks');
    }

    /**
     * @param  WebhookRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param WebhookRequest|WebhookRequestArray $parameters
     */
    public function create(WebhookRequest|array $parameters): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = WebhookRequest::fromArray($parameters);
        }

        return $this->http->post('/hooks', $parameters->toArray());
    }

    public function get(string $hookId): FocusResponse
    {
        return $this->http->get('/hooks/'.urlencode($hookId));
    }

    public function delete(string $hookId): FocusResponse
    {
        return $this->http->delete('/hooks/'.urlencode($hookId));
    }
}
