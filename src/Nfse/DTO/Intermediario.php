<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;
use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;

readonly class Intermediario
{
    use ValidatesConstraints;

    public function __construct(
        public ?string $cpf = null,
        public ?string $cnpj = null,
        public ?string $nif = null,
        public ?MotivoAusenciaNif $motivo_ausencia_nif = null,
        public ?string $razao_social = null,
        public ?string $inscricao_municipal = null,
    ) {
        if ($this->cpf !== null) {
            self::validatePattern('cpf', $this->cpf, '/^\d{11}$/');
        }

        if ($this->cnpj !== null) {
            self::validatePattern('cnpj', $this->cnpj, '/^\d{14}$/');
        }

        if ($this->razao_social !== null) {
            self::validateMaxLength('razao_social', $this->razao_social, 115);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{cpf?: string|null, cnpj?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, razao_social?: string|null, inscricao_municipal?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            cpf: $data['cpf'] ?? null,
            cnpj: $data['cnpj'] ?? null,
            nif: $data['nif'] ?? null,
            motivo_ausencia_nif: isset($data['motivo_ausencia_nif'])
                ? MotivoAusenciaNif::from($data['motivo_ausencia_nif'])
                : null,
            razao_social: $data['razao_social'] ?? null,
            inscricao_municipal: $data['inscricao_municipal'] ?? null,
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_filter([
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'nif' => $this->nif,
            'motivo_ausencia_nif' => $this->motivo_ausencia_nif?->value,
            'razao_social' => $this->razao_social,
            'inscricao_municipal' => $this->inscricao_municipal,
        ], fn (mixed $v): bool => $v !== null);
    }
}
