<?php

declare(strict_types=1);

namespace Larafocus\Support;

/**
 * A log-safe excerpt of a response body.
 *
 * Bodies that reach an error path are arbitrary — a gateway's HTML error page, a
 * stack dump — and they end up inside exception messages and application logs.
 * Trimming and capping the length keeps those readable.
 */
final class BodySnippet
{
    public static function of(string $body): string
    {
        $maxLength = 1024;
        $trimmed = trim($body);

        if (mb_strlen($trimmed) > $maxLength) {
            return mb_substr($trimmed, 0, $maxLength).'…';
        }

        return $trimmed;
    }
}
