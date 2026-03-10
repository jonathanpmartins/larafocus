<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use RectorLaravel\Rector\MethodCall\ContainerBindConcreteWithClosureOnlyRector;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withSkip([
        ClassMethodArrayDocblockParamFromLocalCallsRector::class,
        ContainerBindConcreteWithClosureOnlyRector::class,
        AddParamArrayDocblockFromDimFetchAccessRector::class,
    ])
    ->withPhpSets(php82: true)
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(laravel: true)
    ->withImportNames(removeUnusedImports: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        rectorPreset: true,
    );
