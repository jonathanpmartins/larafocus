<?php

use Larafocus\Focus;

test('list method', function ()
{
    $response = Focus::search()->cities()->list();

    $this->assertRequest('GET', '/municipios', $response);
});

test('get method', function ()
{
    $response = Focus::search()->cities()->get('ibge-code');

    $this->assertRequest('GET', '/municipios/ibge-code', $response);
});
