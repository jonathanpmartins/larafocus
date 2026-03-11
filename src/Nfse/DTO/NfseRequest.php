<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;
use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;
use Larafocus\Nfse\DTO\Enums\RegimeEspecialTributacao;

readonly class NfseRequest
{
    use ValidatesConstraints;

    public function __construct(
        public string $data_emissao,
        public NaturezaOperacao $natureza_operacao,
        public bool $optante_simples_nacional,
        public Prestador $prestador,
        public Tomador $tomador,
        public Servico $servico,
        public ?RegimeEspecialTributacao $regime_especial_tributacao = null,
        public ?bool $incentivador_cultural = null,
        public ?Intermediario $intermediario = null,
        public ?string $codigo_obra = null,
        public ?string $art = null,
        public ?string $numero_nfse_substituido = null,
        public ?string $numero_rps_substituido = null,
        public ?string $serie_rps_substituido = null,
        public ?string $tipo_rps_substituido = null,
    ) {
        if ($this->codigo_obra !== null) {
            self::validateMaxLength('codigo_obra', $this->codigo_obra, 15);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{data_emissao: string, natureza_operacao: string, optante_simples_nacional: bool, prestador: array{cnpj: string, inscricao_municipal: string, codigo_municipio?: string|null}, tomador: array{cpf?: string|null, cnpj?: string|null, razao_social?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, inscricao_municipal?: string|null, email?: string|null, telefone?: string|null, endereco?: array{logradouro?: string|null, tipo_logradouro?: string|null, numero?: string|null, complemento?: string|null, bairro?: string|null, codigo_municipio?: string|null, uf?: string|null, cep?: string|null}|null}, servico: array{valor_servicos: float, iss_retido: bool, item_lista_servico: string, discriminacao: string, codigo_municipio: string, aliquota?: float|null, valor_deducoes?: float|null, valor_pis?: float|null, valor_cofins?: float|null, valor_inss?: float|null, valor_ir?: float|null, valor_csll?: float|null, valor_iss?: float|null, valor_iss_retido?: float|null, outras_retencoes?: float|null, base_calculo?: float|null, desconto_incondicionado?: float|null, desconto_condicionado?: float|null, codigo_cnae?: string|null, codigo_tributario_municipio?: string|null, codigo_nbs?: string|null, codigo_indicador_operacao?: string|null, ibs_cbs_classificacao_tributaria?: string|null, ibs_cbs_situacao_tributaria?: string|null, ibs_cbs_base_calculo?: float|null, ibs_uf_aliquota?: float|null, ibs_mun_aliquota?: float|null, cbs_aliquota?: float|null, ibs_uf_valor?: float|null, ibs_mun_valor?: float|null, cbs_valor?: float|null, percentual_total_tributos?: float|null, fonte_total_tributos?: string|null}, regime_especial_tributacao?: string|null, incentivador_cultural?: bool|null, intermediario?: array{cpf?: string|null, cnpj?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, razao_social?: string|null, inscricao_municipal?: string|null}|null, codigo_obra?: string|null, art?: string|null, numero_nfse_substituido?: string|null, numero_rps_substituido?: string|null, serie_rps_substituido?: string|null, tipo_rps_substituido?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            data_emissao: $data['data_emissao'],
            natureza_operacao: NaturezaOperacao::from($data['natureza_operacao']),
            optante_simples_nacional: $data['optante_simples_nacional'],
            prestador: Prestador::fromArray($data['prestador']),
            tomador: Tomador::fromArray($data['tomador']),
            servico: Servico::fromArray($data['servico']),
            regime_especial_tributacao: isset($data['regime_especial_tributacao'])
                ? RegimeEspecialTributacao::from($data['regime_especial_tributacao'])
                : null,
            incentivador_cultural: $data['incentivador_cultural'] ?? null,
            intermediario: isset($data['intermediario'])
                ? Intermediario::fromArray($data['intermediario'])
                : null,
            codigo_obra: $data['codigo_obra'] ?? null,
            art: $data['art'] ?? null,
            numero_nfse_substituido: $data['numero_nfse_substituido'] ?? null,
            numero_rps_substituido: $data['numero_rps_substituido'] ?? null,
            serie_rps_substituido: $data['serie_rps_substituido'] ?? null,
            tipo_rps_substituido: $data['tipo_rps_substituido'] ?? null,
        );
    }

    /** @return array<string, string|bool|array<string, mixed>> */
    public function toArray(): array
    {
        return array_filter([
            'data_emissao' => $this->data_emissao,
            'natureza_operacao' => $this->natureza_operacao->value,
            'optante_simples_nacional' => $this->optante_simples_nacional,
            'regime_especial_tributacao' => $this->regime_especial_tributacao?->value,
            'incentivador_cultural' => $this->incentivador_cultural,
            'prestador' => $this->prestador->toArray(),
            'tomador' => $this->tomador->toArray(),
            'servico' => $this->servico->toArray(),
            'intermediario' => $this->intermediario?->toArray(),
            'codigo_obra' => $this->codigo_obra,
            'art' => $this->art,
            'numero_nfse_substituido' => $this->numero_nfse_substituido,
            'numero_rps_substituido' => $this->numero_rps_substituido,
            'serie_rps_substituido' => $this->serie_rps_substituido,
            'tipo_rps_substituido' => $this->tipo_rps_substituido,
        ], fn (mixed $v): bool => $v !== null);
    }
}
