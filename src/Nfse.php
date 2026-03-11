<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;
use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Nfse\DTO\NfseRequest;

/**
 * @phpstan-import-type NfseRequestArray from NfseRequest
 * @phpstan-import-type NfseCancelRequestArray from NfseCancelRequest
 * @phpstan-import-type NfseEmailRequestArray from NfseEmailRequest
 */
readonly class Nfse
{
    public function __construct(private Http $http) {}

    /**
     * @param  NfseRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param NfseRequest|NfseRequestArray $parameters
     */
    public function create(string $reference, NfseRequest|array $parameters): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = NfseRequest::fromArray($parameters);
        }

        return $this->http->post('/nfse?ref='.urlencode($reference), $parameters->toArray());
    }

    public function get(string $reference): FocusResponse
    {
        return $this->http->get('/nfse/'.urlencode($reference));
    }

    /**
     * @param  NfseCancelRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param NfseCancelRequest|NfseCancelRequestArray $parameters
     */
    public function cancel(string $reference, NfseCancelRequest|array $parameters): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = NfseCancelRequest::fromArray($parameters);
        }

        return $this->http->delete('/nfse/'.urlencode($reference), $parameters->toArray());
    }

    /**
     * @param  NfseEmailRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param NfseEmailRequest|NfseEmailRequestArray $parameters
     */
    public function email(string $reference, NfseEmailRequest|array $parameters): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = NfseEmailRequest::fromArray($parameters);
        }

        return $this->http->post('/nfse/'.urlencode($reference).'/email', $parameters->toArray());
    }

    public function hook(string $reference): FocusResponse
    {
        return $this->http->post('/nfse/'.urlencode($reference).'/hook');
    }
}
