<?php

use Larafocus\Focus;

test('list method', function ()
{
    $response = Focus::search()
        ->cities()
        ->servicesFor('ibge-code')
        ->list();

    $this->assertRequest('GET', '/municipios/ibge-code/itens_lista_servico', $response);
});

test('get method', function ()
{
    $response = Focus::search()
        ->cities()
        ->servicesFor('ibge-code')
        ->get('service-code');

    $this->assertRequest('GET', '/municipios/ibge-code/itens_lista_servico/service-code', $response);
});
