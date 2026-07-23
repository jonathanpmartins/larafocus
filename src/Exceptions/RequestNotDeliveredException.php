<?php

declare(strict_types=1);

namespace Larafocus\Exceptions;

use Throwable;

/**
 * The request never reached Focus, so nothing was emitted or cancelled.
 *
 * Retrying is safe: no operation was performed on the provider side.
 *
 * @api
 */
final class RequestNotDeliveredException extends CommunicationException
{
    /**
     * @param  'connect'|'dns'|'tls'  $phase  The transport phase that proves non-delivery.
     */
    public function __construct(
        public readonly string $phase,
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf('A requisição não foi entregue ao Focus (%s); é seguro repetir.', $phase), 0, $previous);
    }
}
