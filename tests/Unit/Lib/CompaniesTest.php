<?php

use Larafocus\Focus;

test('list method', function ()
{
    $response = Focus::companies()->list();
    $this->assertRequest('GET', '/empresas', $response);

    $response = Focus::companies()->list([], 10);
    $this->assertRequest('GET', '/empresas?offset=10', $response);
});

test('create method', function ()
{
    $response = Focus::companies()->environment('production')->create();
    $this->assertRequest('POST', '/empresas', $response);

    $response = Focus::companies()->environment('sandbox')->create();
    $this->assertRequest('POST', '/empresas?dry_run=1', $response);
});

test('get method', function ()
{
    $response = Focus::companies()->get('company-id');

    $this->assertRequest('GET', '/empresas/company-id', $response);
});

test('update method', function ()
{
    $response = Focus::companies()
        ->environment('production')
        ->update('company-id');

    $this->assertRequest('PATCH', '/empresas/company-id', $response);

    $response = Focus::companies()
        ->environment('sandbox')
        ->update('company-id');

    $this->assertRequest('PATCH', '/empresas?dry_run=1', $response);
});

test('delete method', function ()
{
    $response = Focus::companies()->delete('company-id');

    $this->assertRequest('DELETE', '/empresas/company-id', $response);
});
