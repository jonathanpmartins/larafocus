<?php

use Larafocus\Companies;
use Larafocus\Focus;

covers(Companies::class);

test('list method', function () {
    $response = Focus::companies()->list();
    $this->assertRequest('GET', '/empresas', $response);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && str_contains($request->url(), 'offset=0');
    });

    $response = Focus::companies()->list(10);
    $this->assertRequest('GET', '/empresas?offset=10', $response);
});

test('create method', function () {
    $response = Focus::companies()->environment('production')->create();
    $this->assertRequest('POST', '/empresas', $response);

    $response = Focus::companies()->environment('sandbox')->create();
    $this->assertRequest('POST', '/empresas?dry_run=1', $response);
});

test('create method defaults to config environment for dry_run', function () {
    // config environment is 'sandbox' (set in UnitTestCase)
    $response = Focus::companies()->create();
    $this->assertRequest('POST', '/empresas?dry_run=1', $response);
});

test('get method', function () {
    $response = Focus::companies()->get('company-id');

    $this->assertRequest('GET', '/empresas/company-id', $response);
});

test('update method', function () {
    $response = Focus::companies()
        ->environment('production')
        ->update('company-id');

    $this->assertRequest('PATCH', '/empresas/company-id', $response);

    $response = Focus::companies()
        ->environment('sandbox')
        ->update('company-id');

    $this->assertRequest('PATCH', '/empresas?dry_run=1', $response);
});

test('delete method', function () {
    $response = Focus::companies()->delete('company-id');

    $this->assertRequest('DELETE', '/empresas/company-id', $response);
});
