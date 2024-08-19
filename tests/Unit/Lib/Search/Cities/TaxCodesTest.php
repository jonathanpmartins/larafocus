<?php

use Larafocus\Focus;

test('list method', function ()
{
    $response = Focus::search()
        ->cities()
        ->taxCodesFor('ibge-code')
        ->list();

    $this->assertRequest('GET', '/municipios/ibge-code/codigos_tributarios_municipio', $response);
});

test('get method', function ()
{
    $response = Focus::search()
        ->cities()
        ->taxCodesFor('ibge-code')
        ->get('tax-code');

    $this->assertRequest('GET', '/municipios/ibge-code/codigos_tributarios_municipio/tax-code', $response);
});
