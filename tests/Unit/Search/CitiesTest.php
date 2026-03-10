<?php

use Larafocus\Focus;
use Larafocus\Search\Cities;
use Larafocus\Search\Cities\Services;
use Larafocus\Search\Cities\TaxCodes;

covers(Cities::class, \Larafocus\Search::class);

test('list method', function () {
    $response = Focus::search()->cities()->list();

    $this->assertRequest('GET', '/municipios', $response);
});

test('get method', function () {
    $response = Focus::search()->cities()->get('ibge-code');

    $this->assertRequest('GET', '/municipios/ibge-code', $response);
});

test('servicesFor returns Services instance', function () {
    $services = Focus::search()->cities()->servicesFor('1234');

    expect($services)->toBeInstanceOf(Services::class);
});

test('taxCodesFor returns TaxCodes instance', function () {
    $taxCodes = Focus::search()->cities()->taxCodesFor('1234');

    expect($taxCodes)->toBeInstanceOf(TaxCodes::class);
});
