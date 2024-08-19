<?php

use Tests\Integration\IntegrationTestCase;
use Tests\Unit\UnitTestCase;

uses(
    UnitTestCase::class,
)->in('Unit');

uses(
    IntegrationTestCase::class,
)->in('Integration');
