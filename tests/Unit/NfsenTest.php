<?php

use Larafocus\Focus;
use Larafocus\Nfsen;

covers(Nfsen::class);

test('create method', function () {
    $response = Focus::nfsen()->create('01');

    $this->assertRequest('POST', '/nfsen?ref=01', $response);
});

test('get method', function () {
    $response = Focus::nfsen()->get('unique-reference');

    $this->assertRequest('GET', '/nfsen/unique-reference', $response);
});

test('cancel method', function () {
    $response = Focus::nfsen()->cancel('unique-reference');

    $this->assertRequest('DELETE', '/nfsen/unique-reference', $response);
});

test('hook method', function () {
    $response = Focus::nfsen()->hook('unique-reference');

    $this->assertRequest('POST', '/nfsen/unique-reference/hook', $response);
});
