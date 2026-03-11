<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\HttpConfig;
use Larafocus\Search\Cities;

class Search
{
    public function __construct(private readonly HttpConfig $httpConfig) {}

    public function cities(): Cities
    {
        return new Cities($this->httpConfig);
    }
}
