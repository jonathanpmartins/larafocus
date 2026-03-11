<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;

/** @phpstan-type PrestadorArray array{cnpj: string, inscricao_municipal: string, codigo_municipio?: string|null} */
readonly class Prestador
{
    use ValidatesConstraints;

    public function __construct(
        public string $cnpj,
        public string $inscricao_municipal,
        public ?string $codigo_municipio = null,
    ) {
        self::validatePattern('cnpj', $this->cnpj, '/^\d{14}$/');

        if ($this->codigo_municipio !== null) {
            self::validatePattern('codigo_municipio', $this->codigo_municipio, '/^\d{7}$/');
        }
    }

    /** @phpstan-param PrestadorArray $data */
    public static function fromArray(array $data): self
    {
        return new self(
            cnpj: $data['cnpj'],
            inscricao_municipal: $data['inscricao_municipal'],
            codigo_municipio: $data['codigo_municipio'] ?? null,
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_filter([
            'cnpj' => $this->cnpj,
            'inscricao_municipal' => $this->inscricao_municipal,
            'codigo_municipio' => $this->codigo_municipio,
        ], fn (mixed $v): bool => $v !== null);
    }
}
