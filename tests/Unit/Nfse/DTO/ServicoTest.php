<?php

use Larafocus\Nfse\DTO\Servico;
use Larafocus\Shared\InvalidDtoException;

covers(Servico::class);

test('constructs with only required fields', function () {
    $s = new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '3550308',
    );

    expect($s->valor_servicos)->toBe(1500.00)
        ->and($s->iss_retido)->toBeFalse()
        ->and($s->item_lista_servico)->toBe('1.07')
        ->and($s->discriminacao)->toBe('Desenvolvimento de software')
        ->and($s->codigo_municipio)->toBe('3550308')
        ->and($s->aliquota)->toBeNull()
        ->and($s->valor_deducoes)->toBeNull()
        ->and($s->codigo_cnae)->toBeNull()
        ->and($s->fonte_total_tributos)->toBeNull();
});

test('toArray with only required fields returns exactly 5 keys', function () {
    $s = new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '3550308',
    );

    $array = $s->toArray();

    expect($array)->toHaveCount(5)
        ->and($array)->toBe([
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
        ]);
});

test('toArray with all optional fields includes them', function () {
    $s = new Servico(
        valor_servicos: 1500.00,
        iss_retido: true,
        item_lista_servico: '1.07',
        discriminacao: 'Dev',
        codigo_municipio: '3550308',
        aliquota: 5.0,
        valor_deducoes: 100.0,
        valor_pis: 10.0,
        valor_cofins: 20.0,
        valor_inss: 30.0,
        valor_ir: 40.0,
        valor_csll: 50.0,
        valor_iss: 60.0,
        valor_iss_retido: 70.0,
        outras_retencoes: 80.0,
        base_calculo: 1400.0,
        desconto_incondicionado: 5.0,
        desconto_condicionado: 10.0,
        codigo_cnae: '6201501',
        codigo_tributario_municipio: '123',
        codigo_nbs: '456',
        codigo_indicador_operacao: '789',
        ibs_cbs_classificacao_tributaria: 'CT1',
        ibs_cbs_situacao_tributaria: 'ST1',
        ibs_cbs_base_calculo: 1300.0,
        ibs_uf_aliquota: 0.02,
        ibs_mun_aliquota: 0.03,
        cbs_aliquota: 0.04,
        ibs_uf_valor: 26.0,
        ibs_mun_valor: 39.0,
        cbs_valor: 52.0,
        percentual_total_tributos: 0.15,
        fonte_total_tributos: 'IBPT',
    );

    expect($s->toArray())->toBe([
        'valor_servicos' => 1500.00,
        'iss_retido' => true,
        'item_lista_servico' => '1.07',
        'discriminacao' => 'Dev',
        'codigo_municipio' => '3550308',
        'aliquota' => 5.0,
        'valor_deducoes' => 100.0,
        'valor_pis' => 10.0,
        'valor_cofins' => 20.0,
        'valor_inss' => 30.0,
        'valor_ir' => 40.0,
        'valor_csll' => 50.0,
        'valor_iss' => 60.0,
        'valor_iss_retido' => 70.0,
        'outras_retencoes' => 80.0,
        'base_calculo' => 1400.0,
        'desconto_incondicionado' => 5.0,
        'desconto_condicionado' => 10.0,
        'codigo_cnae' => '6201501',
        'codigo_tributario_municipio' => '123',
        'codigo_nbs' => '456',
        'codigo_indicador_operacao' => '789',
        'ibs_cbs_classificacao_tributaria' => 'CT1',
        'ibs_cbs_situacao_tributaria' => 'ST1',
        'ibs_cbs_base_calculo' => 1300.0,
        'ibs_uf_aliquota' => 0.02,
        'ibs_mun_aliquota' => 0.03,
        'cbs_aliquota' => 0.04,
        'ibs_uf_valor' => 26.0,
        'ibs_mun_valor' => 39.0,
        'cbs_valor' => 52.0,
        'percentual_total_tributos' => 0.15,
        'fonte_total_tributos' => 'IBPT',
    ]);
});

test('fromArray creates instance with all fields', function () {
    $s = Servico::fromArray([
        'valor_servicos' => 2000.00,
        'iss_retido' => true,
        'item_lista_servico' => '2.01',
        'discriminacao' => 'Consultoria',
        'codigo_municipio' => '1234567',
        'aliquota' => 3.5,
        'valor_deducoes' => 100.00,
        'valor_pis' => 10.00,
        'valor_cofins' => 20.00,
        'valor_inss' => 30.00,
        'valor_ir' => 40.00,
        'valor_csll' => 50.00,
        'valor_iss' => 70.00,
        'valor_iss_retido' => 70.00,
        'outras_retencoes' => 5.00,
        'base_calculo' => 1900.00,
        'desconto_incondicionado' => 50.00,
        'desconto_condicionado' => 25.00,
        'codigo_cnae' => '6201501',
        'codigo_tributario_municipio' => '123456',
        'codigo_nbs' => '1.0101',
        'codigo_indicador_operacao' => 'A',
        'ibs_cbs_classificacao_tributaria' => 'CT1',
        'ibs_cbs_situacao_tributaria' => 'ST1',
        'ibs_cbs_base_calculo' => 1900.00,
        'ibs_uf_aliquota' => 2.0,
        'ibs_mun_aliquota' => 3.0,
        'cbs_aliquota' => 1.5,
        'ibs_uf_valor' => 38.00,
        'ibs_mun_valor' => 57.00,
        'cbs_valor' => 28.50,
        'percentual_total_tributos' => 6.5,
        'fonte_total_tributos' => 'IBPT',
    ]);

    expect($s->valor_servicos)->toBe(2000.00)
        ->and($s->iss_retido)->toBeTrue()
        ->and($s->aliquota)->toBe(3.5)
        ->and($s->valor_deducoes)->toBe(100.00)
        ->and($s->codigo_cnae)->toBe('6201501')
        ->and($s->ibs_cbs_base_calculo)->toBe(1900.00)
        ->and($s->fonte_total_tributos)->toBe('IBPT');
});

test('fromArray maps all optional fields', function () {
    $servico = Servico::fromArray([
        'valor_servicos' => 1500.00,
        'iss_retido' => false,
        'item_lista_servico' => '1.07',
        'discriminacao' => 'Test',
        'codigo_municipio' => '3550308',
        'aliquota' => 0.05,
        'valor_deducoes' => 100.0,
        'valor_pis' => 10.0,
        'valor_cofins' => 20.0,
        'valor_inss' => 30.0,
        'valor_ir' => 40.0,
        'valor_csll' => 50.0,
        'valor_iss' => 60.0,
        'valor_iss_retido' => 70.0,
        'outras_retencoes' => 80.0,
        'base_calculo' => 1400.0,
        'desconto_incondicionado' => 5.0,
        'desconto_condicionado' => 10.0,
        'codigo_cnae' => '6201501',
        'codigo_tributario_municipio' => '123',
        'codigo_nbs' => '456',
        'codigo_indicador_operacao' => '789',
        'ibs_cbs_classificacao_tributaria' => 'CT1',
        'ibs_cbs_situacao_tributaria' => 'ST1',
        'ibs_cbs_base_calculo' => 1300.0,
        'ibs_uf_aliquota' => 0.02,
        'ibs_mun_aliquota' => 0.03,
        'cbs_aliquota' => 0.04,
        'ibs_uf_valor' => 26.0,
        'ibs_mun_valor' => 39.0,
        'cbs_valor' => 52.0,
        'percentual_total_tributos' => 0.15,
        'fonte_total_tributos' => 'IBPT',
    ]);

    expect($servico->aliquota)->toBe(0.05)
        ->and($servico->valor_deducoes)->toBe(100.0)
        ->and($servico->valor_pis)->toBe(10.0)
        ->and($servico->valor_cofins)->toBe(20.0)
        ->and($servico->valor_inss)->toBe(30.0)
        ->and($servico->valor_ir)->toBe(40.0)
        ->and($servico->valor_csll)->toBe(50.0)
        ->and($servico->valor_iss)->toBe(60.0)
        ->and($servico->valor_iss_retido)->toBe(70.0)
        ->and($servico->outras_retencoes)->toBe(80.0)
        ->and($servico->base_calculo)->toBe(1400.0)
        ->and($servico->desconto_incondicionado)->toBe(5.0)
        ->and($servico->desconto_condicionado)->toBe(10.0)
        ->and($servico->codigo_cnae)->toBe('6201501')
        ->and($servico->codigo_tributario_municipio)->toBe('123')
        ->and($servico->codigo_nbs)->toBe('456')
        ->and($servico->codigo_indicador_operacao)->toBe('789')
        ->and($servico->ibs_cbs_classificacao_tributaria)->toBe('CT1')
        ->and($servico->ibs_cbs_situacao_tributaria)->toBe('ST1')
        ->and($servico->ibs_cbs_base_calculo)->toBe(1300.0)
        ->and($servico->ibs_uf_aliquota)->toBe(0.02)
        ->and($servico->ibs_mun_aliquota)->toBe(0.03)
        ->and($servico->cbs_aliquota)->toBe(0.04)
        ->and($servico->ibs_uf_valor)->toBe(26.0)
        ->and($servico->ibs_mun_valor)->toBe(39.0)
        ->and($servico->cbs_valor)->toBe(52.0)
        ->and($servico->percentual_total_tributos)->toBe(0.15)
        ->and($servico->fonte_total_tributos)->toBe('IBPT');
});

test('fromArray casts string iss_retido to bool', function () {
    $s = Servico::fromArray([
        'valor_servicos' => 1500.00,
        'iss_retido' => '1',
        'item_lista_servico' => '1.07',
        'discriminacao' => 'Desenvolvimento de software',
        'codigo_municipio' => '3550308',
    ]);

    expect($s->iss_retido)->toBeTrue();
});

test('fromArray handles missing optional fields', function () {
    $s = Servico::fromArray([
        'valor_servicos' => 500.00,
        'iss_retido' => false,
        'item_lista_servico' => '1.07',
        'discriminacao' => 'Serviço básico',
        'codigo_municipio' => '3550308',
    ]);

    expect($s->valor_servicos)->toBe(500.00)
        ->and($s->aliquota)->toBeNull()
        ->and($s->valor_deducoes)->toBeNull()
        ->and($s->codigo_cnae)->toBeNull()
        ->and($s->fonte_total_tributos)->toBeNull();
});

test('validates codigo_municipio pattern', function () {
    new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '123',
    );
})->throws(InvalidDtoException::class);

test('validates codigo_municipio rejects non-digits', function () {
    new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: 'ABCDEFG',
    );
})->throws(InvalidDtoException::class);
