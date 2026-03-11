<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;
use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;

/**
 * @phpstan-import-type EnderecoArray from Endereco
 *
 * @phpstan-type TomadorArray array{cpf?: string|null, cnpj?: string|null, razao_social?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, inscricao_municipal?: string|null, email?: string|null, telefone?: string|null, endereco?: EnderecoArray|null}
 */
readonly class Tomador
{
    use ValidatesConstraints;

    public function __construct(
        public ?string $cpf = null,
        public ?string $cnpj = null,
        public ?string $razao_social = null,
        public ?string $nif = null,
        public ?MotivoAusenciaNif $motivo_ausencia_nif = null,
        public ?string $inscricao_municipal = null,
        public ?string $email = null,
        public ?string $telefone = null,
        public ?Endereco $endereco = null,
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

        if ($this->email !== null) {
            self::validateMaxLength('email', $this->email, 80);
        }

        if ($this->telefone !== null) {
            self::validatePattern('telefone', $this->telefone, '/^\d{10,11}$/');
        }
    }

    /** @phpstan-param TomadorArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            cpf: $data['cpf'] ?? null,
            cnpj: $data['cnpj'] ?? null,
            razao_social: $data['razao_social'] ?? null,
            nif: $data['nif'] ?? null,
            motivo_ausencia_nif: isset($data['motivo_ausencia_nif'])
                ? MotivoAusenciaNif::from($data['motivo_ausencia_nif'])
                : null,
            inscricao_municipal: $data['inscricao_municipal'] ?? null,
            email: $data['email'] ?? null,
            telefone: $data['telefone'] ?? null,
            endereco: isset($data['endereco'])
                ? Endereco::fromArray($data['endereco'])
                : null,
        );
    }

    /** @return array<string, string|array<string, string>> */
    public function toArray(): array
    {
        return array_filter([
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'razao_social' => $this->razao_social,
            'nif' => $this->nif,
            'motivo_ausencia_nif' => $this->motivo_ausencia_nif?->value,
            'inscricao_municipal' => $this->inscricao_municipal,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'endereco' => $this->endereco?->toArray(),
        ], fn (mixed $v): bool => $v !== null);
    }
}
