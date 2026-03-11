<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

use Illuminate\Http\Client\PendingRequest;

enum ContentType: string
{
    case Json = 'application/json';
    case Xml = 'application/xml';
    case Pdf = 'application/pdf';

    public function applyTo(PendingRequest $pendingRequest): PendingRequest
    {
        if ($this === self::Json) {
            return $pendingRequest->contentType($this->value)->acceptJson();
        }

        return $pendingRequest->contentType($this->value)->accept($this->value);
    }
}
