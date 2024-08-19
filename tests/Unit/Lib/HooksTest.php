<?php

use Larafocus\Focus;

test('list method', function ()
{
    $response = Focus::hooks()->list();

    $this->assertRequest('GET', '/hooks', $response);
});

test('create method', function ()
{
    $response = Focus::hooks()->create();

    $this->assertRequest('POST', '/hooks', $response);
});

test('get method', function ()
{
    $response = Focus::hooks()->get('hook-id');

    $this->assertRequest('GET', '/hooks/hook-id', $response);
});

test('delete method', function ()
{
    $response = Focus::hooks()->delete('hook-id');

    $this->assertRequest('DELETE', '/hooks/hook-id', $response);
});
