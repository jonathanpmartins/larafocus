<?php

namespace Larafocus\Lib;

use Larafocus\Api;
use Larafocus\Lib\Search\Cities;

class Search extends Api
{
    public function cities(): Cities
    {
        return (new Cities())
            ->useMasterKey($this->useMasterKey)
            ->environment($this->environment)
            ->timeout($this->timeout);
    }
}
