<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\Api;
use Larafocus\Search\Cities;

class Search extends Api
{
    public function cities(): Cities
    {
        return (new Cities)
            ->useMasterKey($this->useMasterKey)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }
}
