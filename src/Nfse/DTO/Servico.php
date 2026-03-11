<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;

readonly class Servico
{
    use ValidatesConstraints;

    public function __construct(
        public float $valor_servicos,
        public bool $iss_retido,
        public string $item_lista_servico,
        public string $discriminacao,
        public string $codigo_municipio,
        public ?float $aliquota = null,
        public ?float $valor_deducoes = null,
        public ?float $valor_pis = null,
        public ?float $valor_cofins = null,
        public ?float $valor_inss = null,
        public ?float $valor_ir = null,
        public ?float $valor_csll = null,
        public ?float $valor_iss = null,
        public ?float $valor_iss_retido = null,
        public ?float $outras_retencoes = null,
        public ?float $base_calculo = null,
        public ?float $desconto_incondicionado = null,
        public ?float $desconto_condicionado = null,
        public ?string $codigo_cnae = null,
        public ?string $codigo_tributario_municipio = null,
        public ?string $codigo_nbs = null,
        public ?string $codigo_indicador_operacao = null,
        public ?string $ibs_cbs_classificacao_tributaria = null,
        public ?string $ibs_cbs_situacao_tributaria = null,
        public ?float $ibs_cbs_base_calculo = null,
        public ?float $ibs_uf_aliquota = null,
        public ?float $ibs_mun_aliquota = null,
        public ?float $cbs_aliquota = null,
        public ?float $ibs_uf_valor = null,
        public ?float $ibs_mun_valor = null,
        public ?float $cbs_valor = null,
        public ?float $percentual_total_tributos = null,
        public ?string $fonte_total_tributos = null,
    ) {
        self::validatePattern('codigo_municipio', $this->codigo_municipio, '/^\d{7}$/');
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{valor_servicos: float, iss_retido: bool, item_lista_servico: string, discriminacao: string, codigo_municipio: string, aliquota?: float|null, valor_deducoes?: float|null, valor_pis?: float|null, valor_cofins?: float|null, valor_inss?: float|null, valor_ir?: float|null, valor_csll?: float|null, valor_iss?: float|null, valor_iss_retido?: float|null, outras_retencoes?: float|null, base_calculo?: float|null, desconto_incondicionado?: float|null, desconto_condicionado?: float|null, codigo_cnae?: string|null, codigo_tributario_municipio?: string|null, codigo_nbs?: string|null, codigo_indicador_operacao?: string|null, ibs_cbs_classificacao_tributaria?: string|null, ibs_cbs_situacao_tributaria?: string|null, ibs_cbs_base_calculo?: float|null, ibs_uf_aliquota?: float|null, ibs_mun_aliquota?: float|null, cbs_aliquota?: float|null, ibs_uf_valor?: float|null, ibs_mun_valor?: float|null, cbs_valor?: float|null, percentual_total_tributos?: float|null, fonte_total_tributos?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            valor_servicos: $data['valor_servicos'],
            iss_retido: $data['iss_retido'],
            item_lista_servico: $data['item_lista_servico'],
            discriminacao: $data['discriminacao'],
            codigo_municipio: $data['codigo_municipio'],
            aliquota: $data['aliquota'] ?? null,
            valor_deducoes: $data['valor_deducoes'] ?? null,
            valor_pis: $data['valor_pis'] ?? null,
            valor_cofins: $data['valor_cofins'] ?? null,
            valor_inss: $data['valor_inss'] ?? null,
            valor_ir: $data['valor_ir'] ?? null,
            valor_csll: $data['valor_csll'] ?? null,
            valor_iss: $data['valor_iss'] ?? null,
            valor_iss_retido: $data['valor_iss_retido'] ?? null,
            outras_retencoes: $data['outras_retencoes'] ?? null,
            base_calculo: $data['base_calculo'] ?? null,
            desconto_incondicionado: $data['desconto_incondicionado'] ?? null,
            desconto_condicionado: $data['desconto_condicionado'] ?? null,
            codigo_cnae: $data['codigo_cnae'] ?? null,
            codigo_tributario_municipio: $data['codigo_tributario_municipio'] ?? null,
            codigo_nbs: $data['codigo_nbs'] ?? null,
            codigo_indicador_operacao: $data['codigo_indicador_operacao'] ?? null,
            ibs_cbs_classificacao_tributaria: $data['ibs_cbs_classificacao_tributaria'] ?? null,
            ibs_cbs_situacao_tributaria: $data['ibs_cbs_situacao_tributaria'] ?? null,
            ibs_cbs_base_calculo: $data['ibs_cbs_base_calculo'] ?? null,
            ibs_uf_aliquota: $data['ibs_uf_aliquota'] ?? null,
            ibs_mun_aliquota: $data['ibs_mun_aliquota'] ?? null,
            cbs_aliquota: $data['cbs_aliquota'] ?? null,
            ibs_uf_valor: $data['ibs_uf_valor'] ?? null,
            ibs_mun_valor: $data['ibs_mun_valor'] ?? null,
            cbs_valor: $data['cbs_valor'] ?? null,
            percentual_total_tributos: $data['percentual_total_tributos'] ?? null,
            fonte_total_tributos: $data['fonte_total_tributos'] ?? null,
        );
    }

    /** @return array<string, float|bool|string> */
    public function toArray(): array
    {
        return array_filter([
            'valor_servicos' => $this->valor_servicos,
            'iss_retido' => $this->iss_retido,
            'item_lista_servico' => $this->item_lista_servico,
            'discriminacao' => $this->discriminacao,
            'codigo_municipio' => $this->codigo_municipio,
            'aliquota' => $this->aliquota,
            'valor_deducoes' => $this->valor_deducoes,
            'valor_pis' => $this->valor_pis,
            'valor_cofins' => $this->valor_cofins,
            'valor_inss' => $this->valor_inss,
            'valor_ir' => $this->valor_ir,
            'valor_csll' => $this->valor_csll,
            'valor_iss' => $this->valor_iss,
            'valor_iss_retido' => $this->valor_iss_retido,
            'outras_retencoes' => $this->outras_retencoes,
            'base_calculo' => $this->base_calculo,
            'desconto_incondicionado' => $this->desconto_incondicionado,
            'desconto_condicionado' => $this->desconto_condicionado,
            'codigo_cnae' => $this->codigo_cnae,
            'codigo_tributario_municipio' => $this->codigo_tributario_municipio,
            'codigo_nbs' => $this->codigo_nbs,
            'codigo_indicador_operacao' => $this->codigo_indicador_operacao,
            'ibs_cbs_classificacao_tributaria' => $this->ibs_cbs_classificacao_tributaria,
            'ibs_cbs_situacao_tributaria' => $this->ibs_cbs_situacao_tributaria,
            'ibs_cbs_base_calculo' => $this->ibs_cbs_base_calculo,
            'ibs_uf_aliquota' => $this->ibs_uf_aliquota,
            'ibs_mun_aliquota' => $this->ibs_mun_aliquota,
            'cbs_aliquota' => $this->cbs_aliquota,
            'ibs_uf_valor' => $this->ibs_uf_valor,
            'ibs_mun_valor' => $this->ibs_mun_valor,
            'cbs_valor' => $this->cbs_valor,
            'percentual_total_tributos' => $this->percentual_total_tributos,
            'fonte_total_tributos' => $this->fonte_total_tributos,
        ], fn (mixed $v): bool => $v !== null);
    }
}
