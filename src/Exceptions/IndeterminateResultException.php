<?php

declare(strict_types=1);

namespace Larafocus\Exceptions;

use Larafocus\Support\BodySnippet;
use Throwable;

/**
 * The request may have been processed by Focus, but the outcome is unknown.
 *
 * Retrying blindly risks a duplicate operation. Reconcile first (for NFSe,
 * `Focus::nfse()->get($ref)`) to learn whether the document was emitted before
 * deciding to retry. The provider `ref` deduplicates, so a reconciled retry is safe.
 *
 * @api
 */
final class IndeterminateResultException extends CommunicationException
{
    private const string TRANSPORT_MESSAGE = 'A resposta do Focus é indeterminada; reconcilie a operação antes de repetir.';

    /**
     * @param  'body'|'connect'|'dns'|'read'|'tls'|'transfer'|null  $phase  The transport phase, when one is likely; null otherwise.
     */
    public function __construct(
        string $message,
        public readonly ?string $phase = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function fromTransportFailure(Throwable $throwable): self
    {
        return new self(self::TRANSPORT_MESSAGE, null, $throwable);
    }

    /**
     * @param  'body'|'connect'|'dns'|'read'|'tls'|'transfer'|null  $phase
     */
    public static function fromTransportFailureWithPhase(Throwable $throwable, ?string $phase): self
    {
        return new self(self::TRANSPORT_MESSAGE, $phase, $throwable);
    }

    public static function fromUnreadableResponse(int $statusCode, string $body): self
    {
        return new self(
            sprintf('O Focus respondeu HTTP %d com um corpo ilegível; reconcilie antes de repetir. Corpo: %s', $statusCode, BodySnippet::of($body)),
            'body',
        );
    }

    public static function fromServerError(int $statusCode, string $body): self
    {
        return new self(
            sprintf('O Focus respondeu HTTP %d; a operação pode ter sido processada. Reconcilie antes de repetir. Corpo: %s', $statusCode, BodySnippet::of($body)),
        );
    }
}
