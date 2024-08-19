<?php

use Larafocus\Focus;

test('list companies', function ()
{
    // $this->markTestSkipped();

    $response = Focus::companies()->list();

    dd($response->body());
});

test('create companies', function ()
{
    $this->markTestSkipped();

    $response = Focus::companies()->create(['aomethind' => 'jhere']);

    dd($response->body());
});
