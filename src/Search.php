<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\Http;
use Larafocus\Search\Cities;

readonly class Search
{
    public function __construct(private Http $http) {}

    public function cities(): Cities
    {
        return new Cities($this->http);
    }
}
