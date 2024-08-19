<?php

use Larafocus\Focus;

test('create method', function ()
{
    $response = Focus::nfse()->create('01');

    $this->assertRequest('POST', '/nfse?ref=01', $response);
});

test('get method', function ()
{
    $response = Focus::nfse()->get('unique-reference');

    $this->assertRequest('GET', '/nfse/unique-reference', $response);
});

test('cancel method', function ()
{
    $response = Focus::nfse()->cancel('unique-reference');

    $this->assertRequest('DELETE', '/nfse/unique-reference', $response);
});

test('email method', function ()
{
    $response = Focus::nfse()->email('unique-reference');

    $this->assertRequest('POST', '/nfse/unique-reference/email', $response);
});
