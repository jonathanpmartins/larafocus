<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;

/** @phpstan-type EnderecoArray array{logradouro?: string|null, tipo_logradouro?: string|null, numero?: string|null, complemento?: string|null, bairro?: string|null, codigo_municipio?: string|null, uf?: string|null, cep?: string|null} */
readonly class Endereco
{
    use ValidatesConstraints;

    public function __construct(
        public ?string $logradouro = null,
        public ?string $tipo_logradouro = null,
        public ?string $numero = null,
        public ?string $complemento = null,
        public ?string $bairro = null,
        public ?string $codigo_municipio = null,
        public ?string $uf = null,
        public ?string $cep = null,
    ) {
        if ($this->logradouro !== null) {
            self::validateMaxLength('logradouro', $this->logradouro, 125);
        }

        if ($this->tipo_logradouro !== null) {
            self::validateMaxLength('tipo_logradouro', $this->tipo_logradouro, 3);
        }

        if ($this->numero !== null) {
            self::validateMaxLength('numero', $this->numero, 10);
        }

        if ($this->complemento !== null) {
            self::validateMaxLength('complemento', $this->complemento, 60);
        }

        if ($this->bairro !== null) {
            self::validateMaxLength('bairro', $this->bairro, 60);
        }

        if ($this->codigo_municipio !== null) {
            self::validatePattern('codigo_municipio', $this->codigo_municipio, '/^\d{7}$/');
        }

        if ($this->uf !== null) {
            self::validateExactLength('uf', $this->uf, 2);
        }

        if ($this->cep !== null) {
            self::validatePattern('cep', $this->cep, '/^\d{8}$/');
        }
    }

    /** @phpstan-param EnderecoArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            logradouro: $data['logradouro'] ?? null,
            tipo_logradouro: $data['tipo_logradouro'] ?? null,
            numero: $data['numero'] ?? null,
            complemento: $data['complemento'] ?? null,
            bairro: $data['bairro'] ?? null,
            codigo_municipio: $data['codigo_municipio'] ?? null,
            uf: $data['uf'] ?? null,
            cep: $data['cep'] ?? null,
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_filter([
            'logradouro' => $this->logradouro,
            'tipo_logradouro' => $this->tipo_logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'codigo_municipio' => $this->codigo_municipio,
            'uf' => $this->uf,
            'cep' => $this->cep,
        ], fn (mixed $v): bool => $v !== null);
    }
}
