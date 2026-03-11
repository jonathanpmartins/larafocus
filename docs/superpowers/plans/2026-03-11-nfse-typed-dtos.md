# NFSe Typed DTOs Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add typed, validated DTOs for the NFSe create endpoint so consumers get type safety, IDE autocompletion, and early validation instead of raw arrays.

**Architecture:** Readonly DTO classes with snake_case properties matching API keys, constructor validation from OpenAPI spec rules, `fromArray()`/`toArray()` for conversion. `Nfse::create()` accepts `NfseRequest|array` with auto-conversion.

**Tech Stack:** PHP 8.2+ readonly classes, string-backed enums, Pest tests

**Spec:** `docs/superpowers/specs/2026-03-11-nfse-typed-dtos-design.md`

**Important note on file layout:** PSR-4 allows both `src/Nfse.php` (class `Larafocus\Nfse`) and `src/Nfse/DTO/*.php` (namespace `Larafocus\Nfse\DTO\*`) to coexist. We do NOT need to move `src/Nfse.php`. It stays where it is.

---

## File Map

**Create:**
- `src/Shared/InvalidDtoException.php` — domain exception for validation failures
- `src/Nfse/DTO/Enums/NaturezaOperacao.php` — enum for natureza_operacao field
- `src/Nfse/DTO/Enums/RegimeEspecialTributacao.php` — enum for regime_especial_tributacao field
- `src/Nfse/DTO/Enums/MotivoAusenciaNif.php` — enum for motivo_ausencia_nif field
- `src/Nfse/DTO/Concerns/ValidatesConstraints.php` — shared validation trait
- `src/Nfse/DTO/Endereco.php` — address DTO (leaf, no nested DTOs)
- `src/Nfse/DTO/Prestador.php` — service provider DTO (leaf)
- `src/Nfse/DTO/Servico.php` — service details DTO (leaf)
- `src/Nfse/DTO/Tomador.php` — customer DTO (contains Endereco)
- `src/Nfse/DTO/Intermediario.php` — intermediary DTO (leaf)
- `src/Nfse/DTO/NfseRequest.php` — root DTO composing all above
- `tests/Unit/Nfse/DTO/Enums/NaturezaOperacaoTest.php`
- `tests/Unit/Nfse/DTO/Enums/RegimeEspecialTributacaoTest.php`
- `tests/Unit/Nfse/DTO/Enums/MotivoAusenciaNifTest.php`
- `tests/Unit/Nfse/DTO/EnderecoTest.php`
- `tests/Unit/Nfse/DTO/PrestadorTest.php`
- `tests/Unit/Nfse/DTO/ServicoTest.php`
- `tests/Unit/Nfse/DTO/TomadorTest.php`
- `tests/Unit/Nfse/DTO/IntermediarioTest.php`
- `tests/Unit/Nfse/DTO/NfseRequestTest.php`
- `tests/Unit/Shared/InvalidDtoExceptionTest.php`

**Modify:**
- `src/Nfse.php` — update `create()` to accept `NfseRequest|array`
- `tests/Unit/NfseTest.php` — add tests for DTO and array input

---

## Chunk 1: Foundation

### Task 1: InvalidDtoException

**Files:**
- Create: `src/Shared/InvalidDtoException.php`
- Create: `tests/Unit/Shared/InvalidDtoExceptionTest.php`

- [ ] **Step 1: Write the test**

```php
<?php

use Larafocus\Shared\InvalidDtoException;

covers(InvalidDtoException::class);

test('it extends InvalidArgumentException', function () {
    $exception = new InvalidDtoException('test message');

    expect($exception)
        ->toBeInstanceOf(InvalidArgumentException::class)
        ->and($exception->getMessage())->toBe('test message');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/pest tests/Unit/Shared/InvalidDtoExceptionTest.php`
Expected: FAIL — class not found

- [ ] **Step 3: Write the implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Shared;

use InvalidArgumentException;

class InvalidDtoException extends InvalidArgumentException {}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Shared/InvalidDtoExceptionTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Shared/InvalidDtoException.php tests/Unit/Shared/InvalidDtoExceptionTest.php
git commit -m "add InvalidDtoException for DTO validation failures"
```

---

### Task 2: Enums

**Files:**
- Create: `src/Nfse/DTO/Enums/NaturezaOperacao.php`
- Create: `src/Nfse/DTO/Enums/RegimeEspecialTributacao.php`
- Create: `src/Nfse/DTO/Enums/MotivoAusenciaNif.php`
- Create: `tests/Unit/Nfse/DTO/Enums/NaturezaOperacaoTest.php`
- Create: `tests/Unit/Nfse/DTO/Enums/RegimeEspecialTributacaoTest.php`
- Create: `tests/Unit/Nfse/DTO/Enums/MotivoAusenciaNifTest.php`

- [ ] **Step 1: Write NaturezaOperacao test**

```php
<?php

use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;

covers(NaturezaOperacao::class);

test('has all six cases with correct values', function () {
    expect(NaturezaOperacao::cases())->toHaveCount(6)
        ->and(NaturezaOperacao::TributacaoMunicipio->value)->toBe('1')
        ->and(NaturezaOperacao::TributacaoForaMunicipio->value)->toBe('2')
        ->and(NaturezaOperacao::Isencao->value)->toBe('3')
        ->and(NaturezaOperacao::Imune->value)->toBe('4')
        ->and(NaturezaOperacao::ExigibilidadeSuspensaJudicial->value)->toBe('5')
        ->and(NaturezaOperacao::ExigibilidadeSuspensaAdministrativa->value)->toBe('6');
});

test('can be created from string value', function () {
    expect(NaturezaOperacao::from('1'))->toBe(NaturezaOperacao::TributacaoMunicipio);
});
```

- [ ] **Step 2: Write RegimeEspecialTributacao test**

```php
<?php

use Larafocus\Nfse\DTO\Enums\RegimeEspecialTributacao;

covers(RegimeEspecialTributacao::class);

test('has all six cases with correct values', function () {
    expect(RegimeEspecialTributacao::cases())->toHaveCount(6)
        ->and(RegimeEspecialTributacao::MicroempresaMunicipal->value)->toBe('1')
        ->and(RegimeEspecialTributacao::Estimativa->value)->toBe('2')
        ->and(RegimeEspecialTributacao::SociedadeProfissionais->value)->toBe('3')
        ->and(RegimeEspecialTributacao::Cooperativa->value)->toBe('4')
        ->and(RegimeEspecialTributacao::MeiSimplesNacional->value)->toBe('5')
        ->and(RegimeEspecialTributacao::MeEppSimplesNacional->value)->toBe('6');
});

test('can be created from string value', function () {
    expect(RegimeEspecialTributacao::from('3'))->toBe(RegimeEspecialTributacao::SociedadeProfissionais);
});
```

- [ ] **Step 3: Write MotivoAusenciaNif test**

```php
<?php

use Larafocus\Nfse\DTO\Enums\MotivoAusenciaNif;

covers(MotivoAusenciaNif::class);

test('has all three cases with correct values', function () {
    expect(MotivoAusenciaNif::cases())->toHaveCount(3)
        ->and(MotivoAusenciaNif::NaoInformado->value)->toBe('0')
        ->and(MotivoAusenciaNif::Dispensado->value)->toBe('1')
        ->and(MotivoAusenciaNif::NaoExigido->value)->toBe('2');
});

test('can be created from string value', function () {
    expect(MotivoAusenciaNif::from('1'))->toBe(MotivoAusenciaNif::Dispensado);
});
```

- [ ] **Step 4: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/Enums/`
Expected: FAIL — enums not found

- [ ] **Step 5: Write NaturezaOperacao enum**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum NaturezaOperacao: string
{
    case TributacaoMunicipio = '1';
    case TributacaoForaMunicipio = '2';
    case Isencao = '3';
    case Imune = '4';
    case ExigibilidadeSuspensaJudicial = '5';
    case ExigibilidadeSuspensaAdministrativa = '6';
}
```

- [ ] **Step 6: Write RegimeEspecialTributacao enum**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum RegimeEspecialTributacao: string
{
    case MicroempresaMunicipal = '1';
    case Estimativa = '2';
    case SociedadeProfissionais = '3';
    case Cooperativa = '4';
    case MeiSimplesNacional = '5';
    case MeEppSimplesNacional = '6';
}
```

- [ ] **Step 7: Write MotivoAusenciaNif enum**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Enums;

enum MotivoAusenciaNif: string
{
    case NaoInformado = '0';
    case Dispensado = '1';
    case NaoExigido = '2';
}
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/Enums/`
Expected: PASS (6 tests)

- [ ] **Step 9: Commit**

```bash
git add src/Nfse/DTO/Enums/ tests/Unit/Nfse/DTO/Enums/
git commit -m "add NFSe enums: NaturezaOperacao, RegimeEspecialTributacao, MotivoAusenciaNif"
```

---

### Task 3: ValidatesConstraints trait

**Files:**
- Create: `src/Nfse/DTO/Concerns/ValidatesConstraints.php`

This trait provides reusable validation helpers for DTO constructors. No separate test file — it is tested through the DTOs that use it.

- [ ] **Step 1: Write the trait**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO\Concerns;

use Larafocus\Shared\InvalidDtoException;

trait ValidatesConstraints
{
    private static function validatePattern(string $field, string $value, string $pattern): void
    {
        if (preg_match($pattern, $value) !== 1) {
            throw new InvalidDtoException("{$field} does not match pattern {$pattern}.");
        }
    }

    private static function validateMaxLength(string $field, string $value, int $max): void
    {
        if (mb_strlen($value) > $max) {
            throw new InvalidDtoException("{$field} must not exceed {$max} characters.");
        }
    }

    private static function validateExactLength(string $field, string $value, int $length): void
    {
        if (mb_strlen($value) !== $length) {
            throw new InvalidDtoException("{$field} must be exactly {$length} characters.");
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Nfse/DTO/Concerns/ValidatesConstraints.php
git commit -m "add ValidatesConstraints trait for DTO constructor validation"
```

---

## Chunk 2: Leaf DTOs (Endereco, Prestador, Servico, Intermediario)

### Task 4: Endereco DTO

**Files:**
- Create: `src/Nfse/DTO/Endereco.php`
- Create: `tests/Unit/Nfse/DTO/EnderecoTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Nfse\DTO\Endereco;
use Larafocus\Shared\InvalidDtoException;

covers(Endereco::class);

test('constructs with all fields', function () {
    $endereco = new Endereco(
        logradouro: 'Rua Exemplo',
        tipo_logradouro: 'Rua',
        numero: '100',
        complemento: 'Sala 1',
        bairro: 'Centro',
        codigo_municipio: '3550308',
        uf: 'SP',
        cep: '01001000',
    );

    expect($endereco->logradouro)->toBe('Rua Exemplo')
        ->and($endereco->cep)->toBe('01001000');
});

test('constructs with no fields', function () {
    $endereco = new Endereco();

    expect($endereco->logradouro)->toBeNull()
        ->and($endereco->cep)->toBeNull();
});

test('toArray omits null values', function () {
    $endereco = new Endereco(logradouro: 'Rua A', uf: 'SP');

    expect($endereco->toArray())->toBe([
        'logradouro' => 'Rua A',
        'uf' => 'SP',
    ]);
});

test('toArray returns all fields when set', function () {
    $endereco = new Endereco(
        logradouro: 'Rua B',
        tipo_logradouro: 'Rua',
        numero: '200',
        complemento: 'Apto 3',
        bairro: 'Vila',
        codigo_municipio: '1234567',
        uf: 'RJ',
        cep: '20000000',
    );

    expect($endereco->toArray())->toBe([
        'logradouro' => 'Rua B',
        'tipo_logradouro' => 'Rua',
        'numero' => '200',
        'complemento' => 'Apto 3',
        'bairro' => 'Vila',
        'codigo_municipio' => '1234567',
        'uf' => 'RJ',
        'cep' => '20000000',
    ]);
});

test('fromArray creates instance', function () {
    $endereco = Endereco::fromArray([
        'logradouro' => 'Rua C',
        'cep' => '30000000',
    ]);

    expect($endereco->logradouro)->toBe('Rua C')
        ->and($endereco->cep)->toBe('30000000')
        ->and($endereco->bairro)->toBeNull();
});

test('validates cep pattern', function () {
    new Endereco(cep: 'invalid');
})->throws(InvalidDtoException::class);

test('validates codigo_municipio pattern', function () {
    new Endereco(codigo_municipio: '123');
})->throws(InvalidDtoException::class);

test('validates uf exact length', function () {
    new Endereco(uf: 'SPP');
})->throws(InvalidDtoException::class);

test('validates logradouro max length', function () {
    new Endereco(logradouro: str_repeat('A', 126));
})->throws(InvalidDtoException::class);

test('validates tipo_logradouro max length', function () {
    new Endereco(tipo_logradouro: 'ABCD');
})->throws(InvalidDtoException::class);

test('validates numero max length', function () {
    new Endereco(numero: str_repeat('1', 11));
})->throws(InvalidDtoException::class);

test('validates complemento max length', function () {
    new Endereco(complemento: str_repeat('A', 61));
})->throws(InvalidDtoException::class);

test('validates bairro max length', function () {
    new Endereco(bairro: str_repeat('A', 61));
})->throws(InvalidDtoException::class);
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/EnderecoTest.php`
Expected: FAIL

- [ ] **Step 3: Write Endereco implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Nfse\DTO\Concerns\ValidatesConstraints;

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
            self::validatePattern('codigo_municipio', $this->codigo_municipio, '/^[0-9]{7}$/');
        }
        if ($this->uf !== null) {
            self::validateExactLength('uf', $this->uf, 2);
        }
        if ($this->cep !== null) {
            self::validatePattern('cep', $this->cep, '/^[0-9]{8}$/');
        }
    }

    /** @param array<string, mixed> $data */
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
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/EnderecoTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Nfse/DTO/Endereco.php tests/Unit/Nfse/DTO/EnderecoTest.php
git commit -m "add Endereco DTO with validation and tests"
```

---

### Task 5: Prestador DTO

**Files:**
- Create: `src/Nfse/DTO/Prestador.php`
- Create: `tests/Unit/Nfse/DTO/PrestadorTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Nfse\DTO\Prestador;
use Larafocus\Shared\InvalidDtoException;

covers(Prestador::class);

test('constructs with required fields', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345');

    expect($p->cnpj)->toBe('12345678000195')
        ->and($p->inscricao_municipal)->toBe('12345')
        ->and($p->codigo_municipio)->toBeNull();
});

test('constructs with all fields', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '3550308');

    expect($p->codigo_municipio)->toBe('3550308');
});

test('toArray omits null codigo_municipio', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345');

    expect($p->toArray())->toBe([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
    ]);
});

test('toArray includes codigo_municipio when set', function () {
    $p = new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '3550308');

    expect($p->toArray())->toHaveKey('codigo_municipio', '3550308');
});

test('fromArray creates instance', function () {
    $p = Prestador::fromArray([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
        'codigo_municipio' => '3550308',
    ]);

    expect($p->cnpj)->toBe('12345678000195')
        ->and($p->codigo_municipio)->toBe('3550308');
});

test('fromArray handles missing optional fields', function () {
    $p = Prestador::fromArray([
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '12345',
    ]);

    expect($p->codigo_municipio)->toBeNull();
});

test('validates cnpj must be 14 digits', function () {
    new Prestador(cnpj: '123', inscricao_municipal: '12345');
})->throws(InvalidDtoException::class);

test('validates cnpj rejects non-digits', function () {
    new Prestador(cnpj: '1234567800019A', inscricao_municipal: '12345');
})->throws(InvalidDtoException::class);

test('validates codigo_municipio pattern', function () {
    new Prestador(cnpj: '12345678000195', inscricao_municipal: '12345', codigo_municipio: '123');
})->throws(InvalidDtoException::class);
```

- [ ] **Step 2: Run tests, verify they fail, then implement**

Implementation follows the exact pattern from the design spec (see spec § DTO Pattern).

- [ ] **Step 3: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/PrestadorTest.php`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add src/Nfse/DTO/Prestador.php tests/Unit/Nfse/DTO/PrestadorTest.php
git commit -m "add Prestador DTO with validation and tests"
```

---

### Task 6: Servico DTO

**Files:**
- Create: `src/Nfse/DTO/Servico.php`
- Create: `tests/Unit/Nfse/DTO/ServicoTest.php`

- [ ] **Step 1: Write tests**

Test construction with required fields, all optional tax fields, `toArray()` null omission (critical — the 15+ optional float fields must be omitted when null), `fromArray()`, and validation of `codigo_municipio` pattern.

Key test cases:
- Construct with only required fields (5): `valor_servicos`, `iss_retido`, `item_lista_servico`, `discriminacao`, `codigo_municipio`
- `toArray()` with only required fields returns exactly 5 keys
- `toArray()` with optional fields includes them
- `fromArray()` handles missing optionals
- Validates `codigo_municipio` pattern `^[0-9]{7}$`

- [ ] **Step 2: Write implementation**

Readonly class with 5 required + 28 optional properties. Constructor validates `codigo_municipio`. `toArray()` uses `array_filter` to omit nulls. `fromArray()` maps all fields with `?? null` for optionals.

- [ ] **Step 3: Run tests, verify pass, commit**

```bash
git add src/Nfse/DTO/Servico.php tests/Unit/Nfse/DTO/ServicoTest.php
git commit -m "add Servico DTO with validation and tests"
```

---

### Task 7: Intermediario DTO

**Files:**
- Create: `src/Nfse/DTO/Intermediario.php`
- Create: `tests/Unit/Nfse/DTO/IntermediarioTest.php`

- [ ] **Step 1: Write tests**

Key test cases:
- Construct with cpf, with cnpj, with nif
- `toArray()` omits nulls, converts `motivo_ausencia_nif` enum via `->value`
- `fromArray()` converts `motivo_ausencia_nif` string to enum
- Validates `cpf` pattern `^[0-9]{11}$`, `cnpj` pattern `^[0-9]{14}$`
- Validates `razao_social` max 115

- [ ] **Step 2: Write implementation**

All fields optional. Validates patterns when values are non-null. Enum field uses `MotivoAusenciaNif::from()` in `fromArray()` and `->value` in `toArray()`.

- [ ] **Step 3: Run tests, verify pass, commit**

```bash
git add src/Nfse/DTO/Intermediario.php tests/Unit/Nfse/DTO/IntermediarioTest.php
git commit -m "add Intermediario DTO with validation and tests"
```

---

## Chunk 3: Tomador DTO (has nested Endereco)

### Task 8: Tomador DTO

**Files:**
- Create: `src/Nfse/DTO/Tomador.php`
- Create: `tests/Unit/Nfse/DTO/TomadorTest.php`

- [ ] **Step 1: Write tests**

Key test cases:
- Construct with cnpj, with cpf
- Construct with nested `Endereco` object
- `toArray()` omits nulls, converts `motivo_ausencia_nif` enum, recursively calls `endereco->toArray()`
- `fromArray()` converts nested `endereco` array via `Endereco::fromArray()`
- `fromArray()` without endereco key leaves it null
- Validates `cpf` pattern, `cnpj` pattern, `telefone` pattern `^[0-9]{10,11}$`
- Validates `razao_social` max 115, `email` max 80

- [ ] **Step 2: Write implementation**

Readonly class. All fields optional. Constructor validates patterns/lengths when non-null. `toArray()` handles nested `Endereco` by calling `$this->endereco->toArray()`. `fromArray()` detects `endereco` key and calls `Endereco::fromArray()`.

- [ ] **Step 3: Run tests, verify pass, commit**

```bash
git add src/Nfse/DTO/Tomador.php tests/Unit/Nfse/DTO/TomadorTest.php
git commit -m "add Tomador DTO with nested Endereco, validation and tests"
```

---

## Chunk 4: Root DTO + Integration

### Task 9: NfseRequest DTO

**Files:**
- Create: `src/Nfse/DTO/NfseRequest.php`
- Create: `tests/Unit/Nfse/DTO/NfseRequestTest.php`

- [ ] **Step 1: Write tests**

Key test cases:
- Construct with all required fields (typed enums + nested DTOs)
- `toArray()` converts enums via `->value`, nested DTOs via `->toArray()`, omits null optionals
- `toArray()` omits null `intermediario`, optional string fields
- `fromArray()` converts enum strings to enums, nested arrays to DTOs
- `fromArray()` handles missing optionals
- Validates `codigo_obra` max 15

- [ ] **Step 2: Write implementation**

```php
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

    /** @param array<string, mixed> $data */
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

    /** @return array<string, mixed> */
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
```

- [ ] **Step 3: Run tests, verify pass, commit**

```bash
git add src/Nfse/DTO/NfseRequest.php tests/Unit/Nfse/DTO/NfseRequestTest.php
git commit -m "add NfseRequest root DTO with nested DTOs and tests"
```

---

### Task 10: Update Nfse::create() to accept DTO|array

**Files:**
- Modify: `src/Nfse.php`
- Modify: `tests/Unit/NfseTest.php`

- [ ] **Step 1: Add tests for DTO and array input**

Add to `tests/Unit/NfseTest.php`:

```php
test('create method accepts NfseRequest DTO', function () {
    $request = new NfseRequest(
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
    );

    $response = Focus::nfse()->create('REF-001', $request);

    $this->assertRequest('POST', '/nfse?ref=REF-001', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->create('REF-002', [
        'data_emissao' => '2024-01-15T10:30:00-03:00',
        'natureza_operacao' => '1',
        'optante_simples_nacional' => true,
        'prestador' => ['cnpj' => '12345678000195', 'inscricao_municipal' => '12345'],
        'tomador' => ['cnpj' => '98765432000187'],
        'servico' => [
            'valor_servicos' => 1500.00,
            'iss_retido' => false,
            'item_lista_servico' => '1.07',
            'discriminacao' => 'Desenvolvimento de software',
            'codigo_municipio' => '3550308',
        ],
    ]);

    $this->assertRequest('POST', '/nfse?ref=REF-002', $response);
});
```

- [ ] **Step 2: Update Nfse::create()**

Change `src/Nfse.php` `create()` method:

```php
/** @param NfseRequest|array<string, mixed> $parameters */
public function create(string $reference, NfseRequest|array $parameters = []): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseRequest::fromArray($parameters);
    }

    return $this->http->post('/nfse?ref='.urlencode($reference), $parameters->toArray());
}
```

Add imports:
```php
use Larafocus\Nfse\DTO\NfseRequest;
```

- [ ] **Step 3: Run full test suite**

Run: `./vendor/bin/pest --parallel`
Expected: ALL PASS

- [ ] **Step 4: Commit**

```bash
git add src/Nfse.php tests/Unit/NfseTest.php
git commit -m "update Nfse::create() to accept NfseRequest DTO or array"
```

---

## Chunk 5: Quality Gates

### Task 11: Run full quality suite

- [ ] **Step 1: Run all quality checks**

```bash
./vendor/bin/pest --coverage --min=100 --parallel
./vendor/bin/pest --mutate --min=100 --parallel
./vendor/bin/pest --type-coverage --min=100
./vendor/bin/rector --dry-run
./vendor/bin/phpstan analyse
./vendor/bin/psalm --taint-analysis
./vendor/bin/pint -p
```

- [ ] **Step 2: Fix any issues found**

Common issues to expect:
- Mutation testing may require additional edge case tests
- PHPStan may need more precise type annotations on `fromArray()` parameters
- Pint may reformat files (re-run full suite after)

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "fix quality gate issues for NFSe DTOs"
```
