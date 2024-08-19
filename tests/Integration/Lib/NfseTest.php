<?php

test('test focus api municipalities consultation', function ()
{
    $this->markTestSkipped();
//    $response = Focus::search()->cities()->services('4309100')->all();
//    dd($response->body());
//
//    $response = Focus::search()->cities()->services('4304408')->all();
//    dd($response->body());

    $response = Focus::search()->cities()->list([
        // 'sigla_uf' => 'RS',
        'nome' => 'João Pessoa',
        // 'nome',
    ]);

    dd($response->body());



    $response = Focus::search()->cities()->filter([
        'sigla_uf' => 'RS',
        'nome_municipio' => 'Canela',
        // 'nome',
    ]);

    dd($response->body());
});
