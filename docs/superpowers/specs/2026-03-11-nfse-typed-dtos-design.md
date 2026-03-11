# NFSe Typed DTOs Design

## Context

Larafocus currently accepts raw `array<string, mixed>` for all API payloads. This provides no type safety, no IDE autocompletion, and no early validation. The nfse-nacional sister project demonstrates a typed DTO pattern that we want to adopt, starting with the NFSe domain (the most complex). Once proven, the pattern extends to Companies, NFSen, Webhooks, and Municipalities.

## Decisions

- **Scope**: NFSe domain only (first iteration)
- **Input flexibility**: Accept `NfseRequest|array` — arrays auto-convert via `fromArray()`
- **Properties**: Snake_case matching API keys exactly (zero conversion ambiguity)
- **Enums**: PHP 8.1+ string-backed enums for constrained fields
- **Validation**: Constructor validation using OpenAPI spec rules, throws `InvalidDtoException`
- **Serialization**: Explicit `toArray()` on each DTO (handles nested DTOs, null omission, enum values)
- **Nesting**: Separate DTO class per nested API object
- **Location**: `src/Nfse/DTO/` for domain DTOs, `src/Shared/` for cross-domain types

## DTO Hierarchy

Source of truth: `openapi/nfse.yaml` schemas.

```
NfseRequest (root)
├── data_emissao: string (required, ISO 8601)
├── natureza_operacao: NaturezaOperacao (required, enum '1'-'6')
├── optante_simples_nacional: bool (required)
├── regime_especial_tributacao: ?RegimeEspecialTributacao (enum '1'-'6')
├── incentivador_cultural: ?bool
├── prestador: Prestador (required)
├── tomador: Tomador (required)
├── servico: Servico (required)
├── intermediario: ?Intermediario
├── codigo_obra: ?string (max 15)
├── art: ?string
├── numero_nfse_substituido: ?string
├── numero_rps_substituido: ?string
├── serie_rps_substituido: ?string
└── tipo_rps_substituido: ?string

Prestador
├── cnpj: string (required, pattern ^\d{14}$)
├── inscricao_municipal: string (required)
└── codigo_municipio: ?string (pattern ^[0-9]{7}$)

Tomador
├── cpf: ?string (pattern ^[0-9]{11}$, exclusive with cnpj)
├── cnpj: ?string (pattern ^[0-9]{14}$, exclusive with cpf)
├── razao_social: ?string (max 115)
├── nif: ?string
├── motivo_ausencia_nif: ?MotivoAusenciaNif (enum '0'-'2')
├── inscricao_municipal: ?string
├── email: ?string (max 80)
├── telefone: ?string (pattern ^[0-9]{10,11}$)
└── endereco: ?Endereco

Endereco
├── logradouro: ?string (max 125)
├── tipo_logradouro: ?string (max 3)
├── numero: ?string (max 10)
├── complemento: ?string (max 60)
├── bairro: ?string (max 60)
├── codigo_municipio: ?string (pattern ^[0-9]{7}$)
├── uf: ?string (2 chars)
└── cep: ?string (pattern ^[0-9]{8}$)

Servico
├── valor_servicos: float (required)
├── iss_retido: bool (required)
├── item_lista_servico: string (required)
├── discriminacao: string (required)
├── codigo_municipio: string (required, pattern ^[0-9]{7}$)
├── aliquota: ?float
├── valor_deducoes: ?float
├── valor_pis: ?float
├── valor_cofins: ?float
├── valor_inss: ?float
├── valor_ir: ?float
├── valor_csll: ?float
├── valor_iss: ?float
├── valor_iss_retido: ?float
├── outras_retencoes: ?float
├── base_calculo: ?float
├── desconto_incondicionado: ?float
├── desconto_condicionado: ?float
├── codigo_cnae: ?string
├── codigo_tributario_municipio: ?string
├── codigo_nbs: ?string
├── codigo_indicador_operacao: ?string
├── ibs_cbs_classificacao_tributaria: ?string
├── ibs_cbs_situacao_tributaria: ?string
├── ibs_cbs_base_calculo: ?float
├── ibs_uf_aliquota: ?float
├── ibs_mun_aliquota: ?float
├── cbs_aliquota: ?float
├── ibs_uf_valor: ?float
├── ibs_mun_valor: ?float
├── cbs_valor: ?float
├── percentual_total_tributos: ?float
└── fonte_total_tributos: ?string

Intermediario
├── cpf: ?string (pattern ^[0-9]{11}$)
├── cnpj: ?string (pattern ^[0-9]{14}$)
├── nif: ?string
├── motivo_ausencia_nif: ?MotivoAusenciaNif (enum '0'-'2')
├── razao_social: ?string (max 115)
└── inscricao_municipal: ?string
```

## Enums

```php
enum NaturezaOperacao: string
{
    case TributacaoMunicipio = '1';
    case TributacaoForaMunicipio = '2';
    case Isencao = '3';
    case Imune = '4';
    case ExigibilidadeSuspensaJudicial = '5';
    case ExigibilidadeSuspensaAdministrativa = '6';
}

enum RegimeEspecialTributacao: string
{
    case MicroempresaMunicipal = '1';
    case Estimativa = '2';
    case SociedadeProfissionais = '3';
    case Cooperativa = '4';
    case MeiSimplesNacional = '5';
    case MeEppSimplesNacional = '6';
}

enum MotivoAusenciaNif: string
{
    case NaoInformado = '0';
    case Dispensado = '1';
    case NaoExigido = '2';
}
```

## File Structure

```
src/
├── Nfse/
│   ├── DTO/
│   │   ├── NfseRequest.php
│   │   ├── Prestador.php
│   │   ├── Tomador.php
│   │   ├── Endereco.php
│   │   ├── Servico.php
│   │   ├── Intermediario.php
│   │   ├── Enums/
│   │   │   ├── NaturezaOperacao.php
│   │   │   ├── RegimeEspecialTributacao.php
│   │   │   └── MotivoAusenciaNif.php
│   │   └── Concerns/
│   │       └── ValidatesConstraints.php
│   └── Nfse.php  (moved from src/Nfse.php)
├── Shared/
│   └── InvalidDtoException.php
```

Note: `src/Nfse.php` moves to `src/Nfse/Nfse.php` (namespace `Larafocus\Nfse`). FocusManager updated accordingly.

## DTO Pattern (example: Prestador)

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Shared\InvalidDtoException;

readonly class Prestador
{
    public function __construct(
        public string $cnpj,
        public string $inscricao_municipal,
        public ?string $codigo_municipio = null,
    ) {
        if (preg_match('/^\d{14}$/', $this->cnpj) !== 1) {
            throw new InvalidDtoException('cnpj must be exactly 14 digits.');
        }

        if ($this->codigo_municipio !== null && preg_match('/^[0-9]{7}$/', $this->codigo_municipio) !== 1) {
            throw new InvalidDtoException('codigo_municipio must be exactly 7 digits.');
        }
    }

    /** @phpstan-param array{cnpj: string, inscricao_municipal: string, codigo_municipio?: string|null} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            cnpj: $data['cnpj'],
            inscricao_municipal: $data['inscricao_municipal'],
            codigo_municipio: $data['codigo_municipio'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'cnpj' => $this->cnpj,
            'inscricao_municipal' => $this->inscricao_municipal,
            'codigo_municipio' => $this->codigo_municipio,
        ], fn (mixed $value): bool => $value !== null);
    }
}
```

## Nfse::create() Change

```php
// Before:
public function create(string $reference, array $parameters = []): FocusResponse

// After:
public function create(string $reference, NfseRequest|array $parameters = []): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseRequest::fromArray($parameters);
    }

    return $this->http->post('/nfse?ref='.urlencode($reference), $parameters->toArray());
}
```

Consumers can use either:
```php
// Typed DTO
Focus::nfse()->create('REF-001', new NfseRequest(
    data_emissao: '2024-01-15T10:30:00-03:00',
    natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
    optante_simples_nacional: true,
    prestador: new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345'),
    tomador: new Tomador(cnpj: '98765432000187'),
    servico: new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '3550308',
    ),
));

// Raw array (auto-converted)
Focus::nfse()->create('REF-001', [
    'data_emissao' => '2024-01-15T10:30:00-03:00',
    'natureza_operacao' => '1',
    ...
]);
```

## Validation Rules (from OpenAPI spec)

| DTO | Field | Rule |
|-----|-------|------|
| Prestador | cnpj | required, `^\d{14}$` |
| Prestador | codigo_municipio | optional, `^[0-9]{7}$` |
| Tomador | cpf | optional, `^[0-9]{11}$` |
| Tomador | cnpj | optional, `^[0-9]{14}$` |
| Tomador | telefone | optional, `^[0-9]{10,11}$` |
| Tomador | razao_social | optional, max 115 |
| Tomador | email | optional, max 80 |
| Endereco | cep | optional, `^[0-9]{8}$` |
| Endereco | codigo_municipio | optional, `^[0-9]{7}$` |
| Endereco | uf | optional, exactly 2 chars |
| Endereco | logradouro | optional, max 125 |
| Servico | codigo_municipio | required, `^[0-9]{7}$` |
| NfseRequest | codigo_obra | optional, max 15 |

Exclusive choices are not validated in DTOs because the FocusNFe API accepts both CPF and CNPJ as independently optional fields (unlike nfse-nacional where exactly one is required by the government schema).

## Exception

```php
<?php

declare(strict_types=1);

namespace Larafocus\Shared;

use InvalidArgumentException;

class InvalidDtoException extends InvalidArgumentException {}
```

## Testing Strategy

- Each DTO gets a test file: `tests/Unit/Nfse/DTO/PrestadorTest.php`, etc.
- Test `fromArray()` happy path and missing required keys
- Test `toArray()` output matches expected API payload, null omission works
- Test constructor validation (valid and invalid inputs)
- Test enum values match API spec
- Test `Nfse::create()` accepts both DTO and array
- Maintain 100% coverage, mutation, type coverage

## Quality Constraints

All existing quality gates must remain at 100%:
- pest --coverage --min=100
- pest --mutate --min=100
- pest --type-coverage --min=100
- rector, phpstan, psalm, pint
