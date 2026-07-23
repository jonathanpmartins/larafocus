<?php

declare(strict_types=1);

namespace Larafocus\Support;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Larafocus\Exceptions\CommunicationException;
use Larafocus\Exceptions\IndeterminateResultException;
use Larafocus\Exceptions\RequestNotDeliveredException;
use Throwable;

/**
 * Translates a raw transport failure into a typed {@see CommunicationException}.
 *
 * Certainty is mandatory, bias toward indeterminate: only unambiguous evidence of
 * non-delivery (DNS/connect/TLS) yields {@see RequestNotDeliveredException}. Everything
 * else — including a timeout (cURL 28) or an unknown/absent errno — is indeterminate,
 * because a false "not delivered" invites a blind retry that could duplicate a document.
 *
 * The decision is made from the cURL errno alone, never from the message text.
 */
final class TransportFailureClassifier
{
    public static function classify(Throwable $throwable): CommunicationException
    {
        return match (self::curlErrno($throwable)) {
            6 => new RequestNotDeliveredException('dns', $throwable),                    // CURLE_COULDNT_RESOLVE_HOST
            7 => new RequestNotDeliveredException('connect', $throwable),                // CURLE_COULDNT_CONNECT
            35, 58, 60 => new RequestNotDeliveredException('tls', $throwable),           // SSL / cert
            28, 52 => IndeterminateResultException::fromTransportFailureWithPhase($throwable, 'read'),      // TIMEDOUT / GOT_NOTHING
            18, 56, 92 => IndeterminateResultException::fromTransportFailureWithPhase($throwable, 'transfer'),
            default => IndeterminateResultException::fromTransportFailure($throwable),
        };
    }

    /**
     * Walk the exception chain until a Guzzle exception carrying a cURL errno is found.
     */
    private static function curlErrno(Throwable $throwable): ?int
    {
        for ($current = $throwable; $current instanceof Throwable; $current = $current->getPrevious()) {
            if (! $current instanceof ConnectException && ! $current instanceof RequestException) {
                continue;
            }

            $errno = $current->getHandlerContext()['errno'] ?? null;

            if (is_int($errno)) {
                return $errno;
            }
        }

        return null;
    }
}
