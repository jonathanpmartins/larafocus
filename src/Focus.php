<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Lib\Companies;
use Larafocus\Lib\Hooks;
use Larafocus\Lib\Search;
use Larafocus\Lib\Nfse;
use Larafocus\Lib\Nfsen;

class Focus
{
    public static int $timeout = 60;

    public static ?string $environment = null;

    public static bool $useMasterKey = false;

    public static ?string $token = null;

    public static function timeout(int $timeoutInSeconds = 60): self
    {
        self::$timeout = $timeoutInSeconds;

        return new self();
    }

    public static function environment(?string $environment = null): self
    {
        self::$environment = $environment;

        return new self();
    }

    public static function useMasterKey(bool $isTrue = true): self
    {
        self::$useMasterKey = $isTrue;

        return new self();
    }

    public static function token(string $token): self
    {
        self::$token = $token;

        return new self();
    }

    public static function getEnv(): string
    {
        return self::$environment ?: config()->string('larafocus.environment', '');
    }

    public static function getEndpoint(): string
    {
        return config()->string('larafocus.'.self::getEnv().'.endpoint', '');
    }

    public static function nfse(): Nfse
    {
        return (new Nfse())
            ->useMasterKey(self::$useMasterKey)
            ->token(self::$token)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function nfsen(): Nfsen
    {
        return (new Nfsen())
            ->useMasterKey(self::$useMasterKey)
            ->token(self::$token)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function hooks(): Hooks
    {
        return (new Hooks())
            ->useMasterKey(self::$useMasterKey)
            ->token(self::$token)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function search(): Search
    {
        return (new Search())
            ->useMasterKey(self::$useMasterKey)
            ->token(self::$token)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }

    public static function companies(): Companies
    {
        return (new Companies())
            ->useMasterKey(self::$useMasterKey)
            ->token(self::$token)
            ->environment(self::$environment)
            ->timeout(self::$timeout);
    }
}
