<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;
use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;
use Larafocus\Nfse\DTO\Enums\RegimeEspecialTributacao;

/**
 * @phpstan-import-type PrestadorArray from Prestador
 * @phpstan-import-type TomadorArray from Tomador
 * @phpstan-import-type ServicoArray from Servico
 * @phpstan-import-type IntermediarioArray from Intermediario
 *
 * @phpstan-type NfseRequestArray array{data_emissao: string, natureza_operacao: string, optante_simples_nacional: bool, prestador: PrestadorArray, tomador: TomadorArray, servico: ServicoArray, regime_especial_tributacao?: string|null, incentivador_cultural?: bool|null, intermediario?: IntermediarioArray|null, codigo_obra?: string|null, art?: string|null, numero_nfse_substituido?: string|null, numero_rps_substituido?: string|null, serie_rps_substituido?: string|null, tipo_rps_substituido?: string|null}
 */
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

    /** @phpstan-param NfseRequestArray $data */
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
