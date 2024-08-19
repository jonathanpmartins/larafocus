<?php

use Larafocus\Focus;

test('test cities search all', function ()
{
    $this->markTestSkipped();

    $response = Focus::search()->cities()->list();

    $json = $response->json();

    expect($json)->toBeArray()
        ->and($json)->toHaveCount(100);
});

test('test cities search specific city', function ()
{
    $this->markTestSkipped();

    $locations = [
        'RS' => ['Gramado', 'Canela'],
        'PE' => ['Recife'],
        'PB' => ['João Pessoa'],
        'CE' => ['Fortaleza'],
        'RN' => ['Natal'],
    ];

    foreach ($locations as $uf => $cities)
    {
        foreach ($cities as $city)
        {
            $search = [
                'sigla_uf' => $uf,
                'nome_municipio' => $city
            ];

            $response = Focus::search()->cities()->list($search);

            $json = $response->json();

            expect($json)->toBeArray()
                ->and($json)->toHaveCount(1, 'Resultado não encontrado para a pesquisa: '.json_encode($search))
                ->and($json[0]['nome_municipio'])->toBe($city)
                ->and($json[0]['sigla_uf'])->toBe($uf);
        }
    }
});


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
