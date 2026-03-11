<?php

declare(strict_types=1);

namespace Larafocus\Infrastructure;

readonly class HttpConfig
{
    public function __construct(
        public int $timeout,
        public string $environment,
        public string $token,
        public string $masterToken,
    ) {}
}
