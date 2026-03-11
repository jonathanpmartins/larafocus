<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

trait HasHttp
{
    private function http(): Http
    {
        return new Http(
            timeout: $this->httpConfig->timeout,
            environment: $this->httpConfig->environment,
            token: $this->httpConfig->token,
        );
    }
}
