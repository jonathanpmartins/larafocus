<?php

namespace Larafocus;

use Larafocus\Lib\Companies;
use Larafocus\Lib\Hooks;
use Larafocus\Lib\Search;
use Larafocus\Lib\Nfse;

class Focus
{
    public static int $timeout = 60;
    public static ?string $environment = null;
    public static bool $useMasterKey = false;

    public static function timeout(int $timeoutInSeconds = 60): static
    {
        self::$timeout = $timeoutInSeconds;

        return new static;
    }

    public static function environment(?string $environment = null): static
    {
        self::$environment = $environment;

        return new static;
    }

    public static function useMasterKey(bool $isTrue = true): static
    {
        self::$useMasterKey = $isTrue;

        return new static;
    }

    public static function getEnv(): ?string
    {
        return self::$environment ?: config('larafocus.environment');
    }

    public static function getEndpoint(): ?string
    {
        return config('larafocus.'.self::getEnv().'.endpoint');
    }

    public static function nfse(): Nfse
    {
        return (new Nfse())
            ->useMasterKey(self::$useMasterKey)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function hooks(): Hooks
    {
        return (new Hooks())
            ->useMasterKey(self::$useMasterKey)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function search(): Search
    {
        return (new Search())
            ->useMasterKey(self::$useMasterKey)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function companies(): Companies
    {
        return (new Companies())
            ->useMasterKey(self::$useMasterKey)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }
}
