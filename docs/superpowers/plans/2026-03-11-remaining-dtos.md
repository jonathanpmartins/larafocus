# Remaining Typed DTOs Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add typed DTOs for Companies (create/update), Hooks (create), and NFSe (cancel/email) so all request-body methods have type safety, IDE autocompletion, and early validation.

**Architecture:** Readonly DTO classes with snake_case properties matching API keys, `fromArray()`/`toArray()` for conversion. Each domain owns its DTOs under `Domain/DTO/`. No shared validation trait — each domain validates inline.

**Tech Stack:** PHP 8.2+ readonly classes, string/int-backed enums, Pest tests

**Spec:** `docs/superpowers/specs/2026-03-11-remaining-dtos-design.md`

**Important:** PSR-4 allows `src/Companies.php` (class `Larafocus\Companies`) and `src/Companies/DTO/*.php` (namespace `Larafocus\Companies\DTO\*`) to coexist. Same for `src/Hooks.php`. No files need to move.

---

## File Map

**Create:**
- `src/Nfse/DTO/NfseCancelRequest.php`
- `src/Nfse/DTO/NfseEmailRequest.php`
- `src/Hooks/DTO/Enums/WebhookEvent.php`
- `src/Hooks/DTO/WebhookRequest.php`
- `src/Companies/DTO/Enums/RegimeTributario.php`
- `src/Companies/DTO/Enums/OrientacaoDanfe.php`
- `src/Companies/DTO/Enums/SmtpAutenticacao.php`
- `src/Companies/DTO/Enums/SmtpVerificacaoOpenssl.php`
- `src/Companies/DTO/EmpresaRequest.php`
- `tests/Unit/Nfse/DTO/NfseCancelRequestTest.php`
- `tests/Unit/Nfse/DTO/NfseEmailRequestTest.php`
- `tests/Unit/Hooks/DTO/Enums/WebhookEventTest.php`
- `tests/Unit/Hooks/DTO/WebhookRequestTest.php`
- `tests/Unit/Companies/DTO/Enums/RegimeTributarioTest.php`
- `tests/Unit/Companies/DTO/Enums/OrientacaoDanfeTest.php`
- `tests/Unit/Companies/DTO/Enums/SmtpAutenticacaoTest.php`
- `tests/Unit/Companies/DTO/Enums/SmtpVerificacaoOpensslTest.php`
- `tests/Unit/Companies/DTO/EmpresaRequestTest.php`

**Modify:**
- `src/Nfse.php` — update `cancel()` and `email()` to accept DTO|array
- `src/Hooks.php` — update `create()` to accept `WebhookRequest|array`
- `src/Companies.php` — update `create()` and `update()` to accept `EmpresaRequest|array`
- `tests/Unit/NfseTest.php` — add tests for cancel/email with DTO and array
- `tests/Unit/HooksTest.php` — add tests for create with DTO and array
- `tests/Unit/CompaniesTest.php` — add tests for create/update with DTO and array

---

## Chunk 1: NFSe cancel/email DTOs

### Task 1: NfseCancelRequest DTO

**Files:**
- Create: `src/Nfse/DTO/NfseCancelRequest.php`
- Create: `tests/Unit/Nfse/DTO/NfseCancelRequestTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseCancelRequest::class);

test('constructs with valid justificativa', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    expect($request->justificativa)->toBe('Cancelamento solicitado pelo tomador');
});

test('toArray returns justificativa', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    expect($request->toArray())->toBe([
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);
});

test('fromArray creates instance', function () {
    $request = NfseCancelRequest::fromArray([
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);

    expect($request->justificativa)->toBe('Cancelamento solicitado pelo tomador');
});

test('validates justificativa minimum length', function () {
    new NfseCancelRequest(justificativa: 'short');
})->throws(InvalidDtoException::class);

test('validates justificativa at exactly 15 characters passes', function () {
    $request = new NfseCancelRequest(justificativa: str_repeat('A', 15));

    expect($request->justificativa)->toBe(str_repeat('A', 15));
});

test('validates justificativa at exactly 14 characters fails', function () {
    new NfseCancelRequest(justificativa: str_repeat('A', 14));
})->throws(InvalidDtoException::class);

test('validates justificativa maximum length', function () {
    new NfseCancelRequest(justificativa: str_repeat('A', 256));
})->throws(InvalidDtoException::class);

test('validates justificativa at exactly 255 characters passes', function () {
    $request = new NfseCancelRequest(justificativa: str_repeat('A', 255));

    expect($request->justificativa)->toBe(str_repeat('A', 255));
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/NfseCancelRequestTest.php`
Expected: FAIL — class not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Shared\InvalidDtoException;

readonly class NfseCancelRequest
{
    public function __construct(
        public string $justificativa,
    ) {
        $length = mb_strlen($this->justificativa);

        if ($length < 15 || $length > 255) {
            throw new InvalidDtoException('justificativa must be between 15 and 255 characters.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{justificativa: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            justificativa: $data['justificativa'],
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'justificativa' => $this->justificativa,
        ];
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/NfseCancelRequestTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Nfse/DTO/NfseCancelRequest.php tests/Unit/Nfse/DTO/NfseCancelRequestTest.php
git commit -m "add NfseCancelRequest DTO with length validation and tests"
```

---

### Task 2: NfseEmailRequest DTO

**Files:**
- Create: `src/Nfse/DTO/NfseEmailRequest.php`
- Create: `tests/Unit/Nfse/DTO/NfseEmailRequestTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseEmailRequest::class);

test('constructs with valid emails', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com', 'b@example.com']);

    expect($request->emails)->toBe(['a@example.com', 'b@example.com']);
});

test('toArray returns emails', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    expect($request->toArray())->toBe([
        'emails' => ['a@example.com'],
    ]);
});

test('fromArray creates instance', function () {
    $request = NfseEmailRequest::fromArray([
        'emails' => ['a@example.com', 'b@example.com'],
    ]);

    expect($request->emails)->toBe(['a@example.com', 'b@example.com']);
});

test('validates emails cannot be empty', function () {
    new NfseEmailRequest(emails: []);
})->throws(InvalidDtoException::class);

test('validates emails maximum count', function () {
    new NfseEmailRequest(emails: array_map(
        fn (int $i): string => "user{$i}@example.com",
        range(1, 11),
    ));
})->throws(InvalidDtoException::class);

test('validates emails at exactly 10 items passes', function () {
    $emails = array_map(
        fn (int $i): string => "user{$i}@example.com",
        range(1, 10),
    );
    $request = new NfseEmailRequest(emails: $emails);

    expect($request->emails)->toHaveCount(10);
});

test('validates emails with single item passes', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    expect($request->emails)->toHaveCount(1);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/NfseEmailRequestTest.php`
Expected: FAIL — class not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Nfse\DTO;

use Larafocus\Shared\InvalidDtoException;

readonly class NfseEmailRequest
{
    /**
     * @param  array<int, string>  $emails
     *
     * @phpstan-param non-empty-list<string> $emails
     */
    public function __construct(
        public array $emails,
    ) {
        $count = count($this->emails);

        if ($count === 0 || $count > 10) {
            throw new InvalidDtoException('emails must contain between 1 and 10 addresses.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{emails: non-empty-list<string>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            emails: $data['emails'],
        );
    }

    /** @return array<string, array<int, string>> */
    public function toArray(): array
    {
        return [
            'emails' => $this->emails,
        ];
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Nfse/DTO/NfseEmailRequestTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Nfse/DTO/NfseEmailRequest.php tests/Unit/Nfse/DTO/NfseEmailRequestTest.php
git commit -m "add NfseEmailRequest DTO with count validation and tests"
```

---

### Task 3: Update Nfse::cancel() and Nfse::email() to accept DTO|array

**Files:**
- Modify: `src/Nfse.php`
- Modify: `tests/Unit/NfseTest.php`

- [ ] **Step 1: Add tests for cancel with DTO and array**

Add to `tests/Unit/NfseTest.php`:

```php
test('cancel method accepts NfseCancelRequest DTO', function () {
    $request = new NfseCancelRequest(justificativa: 'Cancelamento solicitado pelo tomador');

    $response = Focus::nfse()->cancel('REF-001', $request);

    $this->assertRequest('DELETE', '/nfse/REF-001', $response);
});

test('cancel method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->cancel('REF-002', [
        'justificativa' => 'Cancelamento solicitado pelo tomador',
    ]);

    $this->assertRequest('DELETE', '/nfse/REF-002', $response);
});
```

Add imports at top of file:
```php
use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Nfse\DTO\NfseEmailRequest;
```

- [ ] **Step 2: Add tests for email with DTO and array**

Add to `tests/Unit/NfseTest.php`:

```php
test('email method accepts NfseEmailRequest DTO', function () {
    $request = new NfseEmailRequest(emails: ['a@example.com']);

    $response = Focus::nfse()->email('REF-001', $request);

    $this->assertRequest('POST', '/nfse/REF-001/email', $response);
});

test('email method accepts array and converts to DTO', function () {
    $response = Focus::nfse()->email('REF-002', [
        'emails' => ['a@example.com', 'b@example.com'],
    ]);

    $this->assertRequest('POST', '/nfse/REF-002/email', $response);
});
```

- [ ] **Step 3: Update Nfse::cancel() method**

In `src/Nfse.php`, update the `cancel()` method:

```php
/**
 * @param  NfseCancelRequest|array<string, mixed>  $parameters
 *
 * @phpstan-param NfseCancelRequest|array{justificativa: string} $parameters
 */
public function cancel(string $reference, NfseCancelRequest|array $parameters): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseCancelRequest::fromArray($parameters);
    }

    return $this->http->delete('/nfse/'.urlencode($reference), $parameters->toArray());
}
```

Add import: `use Larafocus\Nfse\DTO\NfseCancelRequest;`

- [ ] **Step 4: Update Nfse::email() method**

In `src/Nfse.php`, update the `email()` method:

```php
/**
 * @param  NfseEmailRequest|array<string, mixed>  $parameters
 *
 * @phpstan-param NfseEmailRequest|array{emails: non-empty-list<string>} $parameters
 */
public function email(string $reference, NfseEmailRequest|array $parameters): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseEmailRequest::fromArray($parameters);
    }

    return $this->http->post('/nfse/'.urlencode($reference).'/email', $parameters->toArray());
}
```

Add import: `use Larafocus\Nfse\DTO\NfseEmailRequest;`

- [ ] **Step 5: Update existing tests that call cancel/email without parameters**

The existing tests in `NfseTest.php` call `cancel('unique-reference')` and `email('unique-reference')` without parameters. Since the parameter is no longer optional (union type requires a value), update these tests:

For the `cancel method` test (line 38-42), change to pass a DTO:
```php
test('cancel method', function () {
    $response = Focus::nfse()->cancel('unique-reference', new NfseCancelRequest(
        justificativa: 'Cancelamento solicitado',
    ));

    $this->assertRequest('DELETE', '/nfse/unique-reference', $response);
});
```

For the `email method` test (line 44-48), change to pass a DTO:
```php
test('email method', function () {
    $response = Focus::nfse()->email('unique-reference', new NfseEmailRequest(
        emails: ['test@example.com'],
    ));

    $this->assertRequest('POST', '/nfse/unique-reference/email', $response);
});
```

- [ ] **Step 6: Run all NFSe tests**

Run: `./vendor/bin/pest tests/Unit/NfseTest.php tests/Unit/Nfse/ --parallel`
Expected: ALL PASS

- [ ] **Step 7: Commit**

```bash
git add src/Nfse.php tests/Unit/NfseTest.php
git commit -m "update Nfse::cancel() and email() to accept DTO or array"
```

---

## Chunk 2: Hooks DTOs

### Task 4: WebhookEvent Enum

**Files:**
- Create: `src/Hooks/DTO/Enums/WebhookEvent.php`
- Create: `tests/Unit/Hooks/DTO/Enums/WebhookEventTest.php`

- [ ] **Step 1: Write test**

```php
<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;

covers(WebhookEvent::class);

test('has all twelve cases with correct values', function () {
    expect(WebhookEvent::cases())->toHaveCount(12)
        ->and(WebhookEvent::Nfe->value)->toBe('nfe')
        ->and(WebhookEvent::Nfse->value)->toBe('nfse')
        ->and(WebhookEvent::Nfsen->value)->toBe('nfsen')
        ->and(WebhookEvent::NfceContingencia->value)->toBe('nfce_contingencia')
        ->and(WebhookEvent::NfeRecebida->value)->toBe('nfe_recebida')
        ->and(WebhookEvent::NfeRecebidaFalhaConsulta->value)->toBe('nfe_recebida_falha_consulta')
        ->and(WebhookEvent::NfseRecebida->value)->toBe('nfse_recebida')
        ->and(WebhookEvent::CteRecebida->value)->toBe('cte_recebida')
        ->and(WebhookEvent::Inutilizacao->value)->toBe('inutilizacao')
        ->and(WebhookEvent::Cte->value)->toBe('cte')
        ->and(WebhookEvent::Mdfe->value)->toBe('mdfe')
        ->and(WebhookEvent::Nfcom->value)->toBe('nfcom');
});

test('can be created from string value', function () {
    expect(WebhookEvent::from('nfse'))->toBe(WebhookEvent::Nfse);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/pest tests/Unit/Hooks/DTO/Enums/WebhookEventTest.php`
Expected: FAIL — enum not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Hooks\DTO\Enums;

enum WebhookEvent: string
{
    case Nfe = 'nfe';
    case Nfse = 'nfse';
    case Nfsen = 'nfsen';
    case NfceContingencia = 'nfce_contingencia';
    case NfeRecebida = 'nfe_recebida';
    case NfeRecebidaFalhaConsulta = 'nfe_recebida_falha_consulta';
    case NfseRecebida = 'nfse_recebida';
    case CteRecebida = 'cte_recebida';
    case Inutilizacao = 'inutilizacao';
    case Cte = 'cte';
    case Mdfe = 'mdfe';
    case Nfcom = 'nfcom';
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Hooks/DTO/Enums/WebhookEventTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Hooks/DTO/Enums/WebhookEvent.php tests/Unit/Hooks/DTO/Enums/WebhookEventTest.php
git commit -m "add WebhookEvent enum with 12 event types"
```

---

### Task 5: WebhookRequest DTO

**Files:**
- Create: `src/Hooks/DTO/WebhookRequest.php`
- Create: `tests/Unit/Hooks/DTO/WebhookRequestTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;

covers(WebhookRequest::class);

test('constructs with required fields', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    );

    expect($request->event)->toBe(WebhookEvent::Nfse)
        ->and($request->url)->toBe('https://example.com/webhook')
        ->and($request->cnpj)->toBeNull()
        ->and($request->cpf)->toBeNull()
        ->and($request->authorization)->toBeNull()
        ->and($request->authorization_header)->toBeNull();
});

test('constructs with all fields', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfe,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
        cpf: '12345678901',
        authorization: 'Bearer token',
        authorization_header: 'X-Custom-Auth',
    );

    expect($request->cnpj)->toBe('12345678000195')
        ->and($request->cpf)->toBe('12345678901')
        ->and($request->authorization)->toBe('Bearer token')
        ->and($request->authorization_header)->toBe('X-Custom-Auth');
});

test('toArray omits null values', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    );

    expect($request->toArray())->toBe([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
    ]);
});

test('toArray includes all fields when set', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfe,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
        cpf: '12345678901',
        authorization: 'Bearer token',
        authorization_header: 'X-Custom-Auth',
    );

    expect($request->toArray())->toBe([
        'event' => 'nfe',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
        'cpf' => '12345678901',
        'authorization' => 'Bearer token',
        'authorization_header' => 'X-Custom-Auth',
    ]);
});

test('fromArray creates instance with required fields', function () {
    $request = WebhookRequest::fromArray([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
    ]);

    expect($request->event)->toBe(WebhookEvent::Nfse)
        ->and($request->url)->toBe('https://example.com/webhook')
        ->and($request->cnpj)->toBeNull();
});

test('fromArray creates instance with all fields', function () {
    $request = WebhookRequest::fromArray([
        'event' => 'nfe',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
        'cpf' => '12345678901',
        'authorization' => 'Bearer token',
        'authorization_header' => 'X-Custom-Auth',
    ]);

    expect($request->event)->toBe(WebhookEvent::Nfe)
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->authorization)->toBe('Bearer token');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Hooks/DTO/WebhookRequestTest.php`
Expected: FAIL — class not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Hooks\DTO;

use Larafocus\Hooks\DTO\Enums\WebhookEvent;

readonly class WebhookRequest
{
    public function __construct(
        public WebhookEvent $event,
        public string $url,
        public ?string $cnpj = null,
        public ?string $cpf = null,
        public ?string $authorization = null,
        public ?string $authorization_header = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @phpstan-param array{event: string, url: string, cnpj?: string|null, cpf?: string|null, authorization?: string|null, authorization_header?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            event: WebhookEvent::from($data['event']),
            url: $data['url'],
            cnpj: $data['cnpj'] ?? null,
            cpf: $data['cpf'] ?? null,
            authorization: $data['authorization'] ?? null,
            authorization_header: $data['authorization_header'] ?? null,
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return array_filter([
            'event' => $this->event->value,
            'url' => $this->url,
            'cnpj' => $this->cnpj,
            'cpf' => $this->cpf,
            'authorization' => $this->authorization,
            'authorization_header' => $this->authorization_header,
        ], fn (mixed $v): bool => $v !== null);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Hooks/DTO/WebhookRequestTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Hooks/DTO/WebhookRequest.php tests/Unit/Hooks/DTO/WebhookRequestTest.php
git commit -m "add WebhookRequest DTO with WebhookEvent enum support"
```

---

### Task 6: Update Hooks::create() to accept DTO|array

**Files:**
- Modify: `src/Hooks.php`
- Modify: `tests/Unit/HooksTest.php`

- [ ] **Step 1: Add tests for create with DTO and array**

Add imports to `tests/Unit/HooksTest.php`:
```php
use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;
```

Add tests:
```php
test('create method accepts WebhookRequest DTO', function () {
    $request = new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
        cnpj: '12345678000195',
    );

    $response = Focus::hooks()->create($request);

    $this->assertRequest('POST', '/hooks', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::hooks()->create([
        'event' => 'nfse',
        'url' => 'https://example.com/webhook',
        'cnpj' => '12345678000195',
    ]);

    $this->assertRequest('POST', '/hooks', $response);
});
```

- [ ] **Step 2: Update Hooks::create() method**

In `src/Hooks.php`, update the `create()` method and add import:

```php
use Larafocus\Hooks\DTO\WebhookRequest;
```

```php
/**
 * @param  WebhookRequest|array<string, mixed>  $parameters
 *
 * @phpstan-param WebhookRequest|array{event: string, url: string, cnpj?: string|null, cpf?: string|null, authorization?: string|null, authorization_header?: string|null} $parameters
 */
public function create(WebhookRequest|array $parameters): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = WebhookRequest::fromArray($parameters);
    }

    return $this->http->post('/hooks', $parameters->toArray());
}
```

- [ ] **Step 3: Update existing create test that passes no parameters**

The existing test on line 14-17 calls `Focus::hooks()->create()` without parameters. Since the parameter is now required (union type), update it:

```php
test('create method', function () {
    $response = Focus::hooks()->create(new WebhookRequest(
        event: WebhookEvent::Nfse,
        url: 'https://example.com/webhook',
    ));

    $this->assertRequest('POST', '/hooks', $response);
});
```

- [ ] **Step 4: Run all Hooks tests**

Run: `./vendor/bin/pest tests/Unit/HooksTest.php tests/Unit/Hooks/ --parallel`
Expected: ALL PASS

- [ ] **Step 5: Commit**

```bash
git add src/Hooks.php tests/Unit/HooksTest.php
git commit -m "update Hooks::create() to accept WebhookRequest DTO or array"
```

---

## Chunk 3: Companies Enums

### Task 7: RegimeTributario Enum

**Files:**
- Create: `src/Companies/DTO/Enums/RegimeTributario.php`
- Create: `tests/Unit/Companies/DTO/Enums/RegimeTributarioTest.php`

- [ ] **Step 1: Write test**

```php
<?php

use Larafocus\Companies\DTO\Enums\RegimeTributario;

covers(RegimeTributario::class);

test('has all four cases with correct values', function () {
    expect(RegimeTributario::cases())->toHaveCount(4)
        ->and(RegimeTributario::SimplesNacional->value)->toBe(1)
        ->and(RegimeTributario::SimplesNacionalExcesso->value)->toBe(2)
        ->and(RegimeTributario::RegimeNormal->value)->toBe(3)
        ->and(RegimeTributario::SimplesNacionalMei->value)->toBe(4);
});

test('can be created from int value', function () {
    expect(RegimeTributario::from(1))->toBe(RegimeTributario::SimplesNacional);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/Enums/RegimeTributarioTest.php`
Expected: FAIL — enum not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum RegimeTributario: int
{
    case SimplesNacional = 1;
    case SimplesNacionalExcesso = 2;
    case RegimeNormal = 3;
    case SimplesNacionalMei = 4;
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/Enums/RegimeTributarioTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Companies/DTO/Enums/RegimeTributario.php tests/Unit/Companies/DTO/Enums/RegimeTributarioTest.php
git commit -m "add RegimeTributario enum for Companies"
```

---

### Task 8: OrientacaoDanfe Enum

**Files:**
- Create: `src/Companies/DTO/Enums/OrientacaoDanfe.php`
- Create: `tests/Unit/Companies/DTO/Enums/OrientacaoDanfeTest.php`

- [ ] **Step 1: Write test**

```php
<?php

use Larafocus\Companies\DTO\Enums\OrientacaoDanfe;

covers(OrientacaoDanfe::class);

test('has both cases with correct values', function () {
    expect(OrientacaoDanfe::cases())->toHaveCount(2)
        ->and(OrientacaoDanfe::Portrait->value)->toBe('portrait')
        ->and(OrientacaoDanfe::Landscape->value)->toBe('landscape');
});

test('can be created from string value', function () {
    expect(OrientacaoDanfe::from('landscape'))->toBe(OrientacaoDanfe::Landscape);
});
```

- [ ] **Step 2: Run test, verify fail, then implement**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum OrientacaoDanfe: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';
}
```

- [ ] **Step 3: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/Enums/OrientacaoDanfeTest.php`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add src/Companies/DTO/Enums/OrientacaoDanfe.php tests/Unit/Companies/DTO/Enums/OrientacaoDanfeTest.php
git commit -m "add OrientacaoDanfe enum for Companies"
```

---

### Task 9: SmtpAutenticacao Enum

**Files:**
- Create: `src/Companies/DTO/Enums/SmtpAutenticacao.php`
- Create: `tests/Unit/Companies/DTO/Enums/SmtpAutenticacaoTest.php`

- [ ] **Step 1: Write test**

```php
<?php

use Larafocus\Companies\DTO\Enums\SmtpAutenticacao;

covers(SmtpAutenticacao::class);

test('has all three cases with correct values', function () {
    expect(SmtpAutenticacao::cases())->toHaveCount(3)
        ->and(SmtpAutenticacao::Plain->value)->toBe('plain')
        ->and(SmtpAutenticacao::Login->value)->toBe('login')
        ->and(SmtpAutenticacao::CramMd5->value)->toBe('cram_md5');
});

test('can be created from string value', function () {
    expect(SmtpAutenticacao::from('login'))->toBe(SmtpAutenticacao::Login);
});
```

- [ ] **Step 2: Run test, verify fail, then implement**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum SmtpAutenticacao: string
{
    case Plain = 'plain';
    case Login = 'login';
    case CramMd5 = 'cram_md5';
}
```

- [ ] **Step 3: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/Enums/SmtpAutenticacaoTest.php`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add src/Companies/DTO/Enums/SmtpAutenticacao.php tests/Unit/Companies/DTO/Enums/SmtpAutenticacaoTest.php
git commit -m "add SmtpAutenticacao enum for Companies"
```

---

### Task 10: SmtpVerificacaoOpenssl Enum

**Files:**
- Create: `src/Companies/DTO/Enums/SmtpVerificacaoOpenssl.php`
- Create: `tests/Unit/Companies/DTO/Enums/SmtpVerificacaoOpensslTest.php`

- [ ] **Step 1: Write test**

```php
<?php

use Larafocus\Companies\DTO\Enums\SmtpVerificacaoOpenssl;

covers(SmtpVerificacaoOpenssl::class);

test('has both cases with correct values', function () {
    expect(SmtpVerificacaoOpenssl::cases())->toHaveCount(2)
        ->and(SmtpVerificacaoOpenssl::Peer->value)->toBe('peer')
        ->and(SmtpVerificacaoOpenssl::None->value)->toBe('none');
});

test('can be created from string value', function () {
    expect(SmtpVerificacaoOpenssl::from('peer'))->toBe(SmtpVerificacaoOpenssl::Peer);
});
```

- [ ] **Step 2: Run test, verify fail, then implement**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO\Enums;

enum SmtpVerificacaoOpenssl: string
{
    case Peer = 'peer';
    case None = 'none';
}
```

- [ ] **Step 3: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/Enums/SmtpVerificacaoOpensslTest.php`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add src/Companies/DTO/Enums/SmtpVerificacaoOpenssl.php tests/Unit/Companies/DTO/Enums/SmtpVerificacaoOpensslTest.php
git commit -m "add SmtpVerificacaoOpenssl enum for Companies"
```

---

## Chunk 4: EmpresaRequest DTO

### Task 11: EmpresaRequest DTO

**Files:**
- Create: `src/Companies/DTO/EmpresaRequest.php`
- Create: `tests/Unit/Companies/DTO/EmpresaRequestTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use Larafocus\Companies\DTO\Enums\OrientacaoDanfe;
use Larafocus\Companies\DTO\Enums\RegimeTributario;
use Larafocus\Companies\DTO\Enums\SmtpAutenticacao;
use Larafocus\Companies\DTO\Enums\SmtpVerificacaoOpenssl;
use Larafocus\Companies\DTO\EmpresaRequest;

covers(EmpresaRequest::class);

test('constructs with no fields', function () {
    $request = new EmpresaRequest();

    expect($request->nome)->toBeNull()
        ->and($request->cnpj)->toBeNull()
        ->and($request->regime_tributario)->toBeNull();
});

test('constructs with basic fields', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste Ltda',
        cnpj: '12345678000195',
        regime_tributario: RegimeTributario::SimplesNacional,
    );

    expect($request->nome)->toBe('Empresa Teste Ltda')
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->regime_tributario)->toBe(RegimeTributario::SimplesNacional);
});

test('constructs with all enum fields', function () {
    $request = new EmpresaRequest(
        regime_tributario: RegimeTributario::RegimeNormal,
        orientacao_danfe: OrientacaoDanfe::Landscape,
        smtp_autenticacao: SmtpAutenticacao::Login,
        smtp_modo_verificacao_openssl: SmtpVerificacaoOpenssl::Peer,
    );

    expect($request->regime_tributario)->toBe(RegimeTributario::RegimeNormal)
        ->and($request->orientacao_danfe)->toBe(OrientacaoDanfe::Landscape)
        ->and($request->smtp_autenticacao)->toBe(SmtpAutenticacao::Login)
        ->and($request->smtp_modo_verificacao_openssl)->toBe(SmtpVerificacaoOpenssl::Peer);
});

test('toArray omits null values', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste',
        habilita_nfe: true,
    );

    expect($request->toArray())->toBe([
        'nome' => 'Empresa Teste',
        'habilita_nfe' => true,
    ]);
});

test('toArray converts enums to values', function () {
    $request = new EmpresaRequest(
        regime_tributario: RegimeTributario::SimplesNacional,
        orientacao_danfe: OrientacaoDanfe::Portrait,
        smtp_autenticacao: SmtpAutenticacao::CramMd5,
        smtp_modo_verificacao_openssl: SmtpVerificacaoOpenssl::None,
    );

    $array = $request->toArray();

    expect($array['regime_tributario'])->toBe(1)
        ->and($array['orientacao_danfe'])->toBe('portrait')
        ->and($array['smtp_autenticacao'])->toBe('cram_md5')
        ->and($array['smtp_modo_verificacao_openssl'])->toBe('none');
});

test('toArray includes boolean false values', function () {
    $request = new EmpresaRequest(
        habilita_nfe: false,
        habilita_nfce: false,
    );

    expect($request->toArray())->toBe([
        'habilita_nfe' => false,
        'habilita_nfce' => false,
    ]);
});

test('toArray includes integer zero values', function () {
    $request = new EmpresaRequest(
        numero: 0,
        cep: 0,
    );

    expect($request->toArray())->toBe([
        'numero' => 0,
        'cep' => 0,
    ]);
});

test('fromArray creates instance with basic fields', function () {
    $request = EmpresaRequest::fromArray([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000195',
    ]);

    expect($request->nome)->toBe('Empresa Teste')
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->cpf)->toBeNull();
});

test('fromArray converts enum values', function () {
    $request = EmpresaRequest::fromArray([
        'regime_tributario' => 3,
        'orientacao_danfe' => 'landscape',
        'smtp_autenticacao' => 'login',
        'smtp_modo_verificacao_openssl' => 'peer',
    ]);

    expect($request->regime_tributario)->toBe(RegimeTributario::RegimeNormal)
        ->and($request->orientacao_danfe)->toBe(OrientacaoDanfe::Landscape)
        ->and($request->smtp_autenticacao)->toBe(SmtpAutenticacao::Login)
        ->and($request->smtp_modo_verificacao_openssl)->toBe(SmtpVerificacaoOpenssl::Peer);
});

test('fromArray handles missing optional fields', function () {
    $request = EmpresaRequest::fromArray([]);

    expect($request->nome)->toBeNull()
        ->and($request->regime_tributario)->toBeNull()
        ->and($request->habilita_nfe)->toBeNull();
});

test('round trip fromArray toArray preserves data', function () {
    $data = [
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000195',
        'regime_tributario' => 1,
        'logradouro' => 'Rua Teste',
        'numero' => 100,
        'uf' => 'SP',
        'habilita_nfe' => true,
        'habilita_nfse' => false,
        'orientacao_danfe' => 'portrait',
        'serie_nfe_producao' => '1',
        'smtp_autenticacao' => 'plain',
        'smtp_modo_verificacao_openssl' => 'peer',
        'nfe_sincrono' => true,
    ];

    $result = EmpresaRequest::fromArray($data)->toArray();

    expect($result)->toBe($data);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/EmpresaRequestTest.php`
Expected: FAIL — class not found

- [ ] **Step 3: Write implementation**

```php
<?php

declare(strict_types=1);

namespace Larafocus\Companies\DTO;

use Larafocus\Companies\DTO\Enums\OrientacaoDanfe;
use Larafocus\Companies\DTO\Enums\RegimeTributario;
use Larafocus\Companies\DTO\Enums\SmtpAutenticacao;
use Larafocus\Companies\DTO\Enums\SmtpVerificacaoOpenssl;

readonly class EmpresaRequest
{
    public function __construct(
        // Dados básicos
        public ?string $nome = null,
        public ?string $nome_fantasia = null,
        public ?string $cnpj = null,
        public ?string $cpf = null,
        public ?int $inscricao_estadual = null,
        public ?int $inscricao_municipal = null,
        public ?RegimeTributario $regime_tributario = null,

        // Endereço
        public ?string $logradouro = null,
        public ?int $numero = null,
        public ?string $complemento = null,
        public ?string $bairro = null,
        public ?string $municipio = null,
        public ?int $cep = null,
        public ?string $uf = null,

        // Contato
        public ?string $telefone = null,
        public ?string $email = null,

        // Habilitações de documentos
        public ?bool $habilita_nfe = null,
        public ?bool $habilita_nfce = null,
        public ?bool $habilita_nfse = null,
        public ?bool $habilita_nfsen_producao = null,
        public ?bool $habilita_nfsen_homologacao = null,
        public ?bool $habilita_cte = null,
        public ?bool $habilita_mdfe = null,
        public ?bool $habilita_nfcom = null,
        public ?bool $habilita_manifestacao = null,
        public ?bool $habilita_manifestacao_cte = null,
        public ?bool $habilita_nfsen_recebidas_producao = null,
        public ?bool $habilita_nfsen_recebidas_homologacao = null,

        // Comunicação
        public ?bool $enviar_email_destinatario = null,
        public ?bool $enviar_email_homologacao = null,
        public ?bool $discrimina_impostos = null,

        // NFCe
        public ?bool $habilita_contingencia_offline_nfce = null,
        public ?bool $reaproveita_numero_nfce_contingencia = null,
        public ?string $csc_nfce_producao = null,
        public ?int $id_token_nfce_producao = null,
        public ?string $csc_nfce_homologacao = null,
        public ?int $id_token_nfce_homologacao = null,

        // DANFe
        public ?OrientacaoDanfe $orientacao_danfe = null,
        public ?bool $recibo_danfe = null,
        public ?bool $exibe_sempre_ipi_danfe = null,
        public ?bool $exibe_issqn_danfe = null,
        public ?bool $exibe_impostos_adicionais_danfe = null,
        public ?bool $exibe_rastro_danfe = null,
        public ?bool $exibe_unidade_tributaria_danfe = null,
        public ?bool $exibe_sempre_volumes_danfe = null,
        public ?bool $exibe_composicao_carga_mdfe = null,
        public ?bool $mostrar_danfse_badge = null,

        // Numeração de documentos
        public ?string $proximo_numero_nfe_producao = null,
        public ?string $proximo_numero_nfe_homologacao = null,
        public ?string $serie_nfe_producao = null,
        public ?string $serie_nfe_homologacao = null,
        public ?string $proximo_numero_nfce_producao = null,
        public ?string $proximo_numero_nfce_homologacao = null,
        public ?string $serie_nfce_producao = null,
        public ?string $serie_nfce_homologacao = null,
        public ?string $proximo_numero_nfse_producao = null,
        public ?string $proximo_numero_nfse_homologacao = null,
        public ?string $serie_nfse_producao = null,
        public ?string $serie_nfse_homologacao = null,
        public ?string $proximo_numero_nfsen_producao = null,
        public ?string $proximo_numero_nfsen_homologacao = null,
        public ?string $serie_nfsen_producao = null,
        public ?string $serie_nfsen_homologacao = null,
        public ?string $proximo_numero_cte_producao = null,
        public ?string $proximo_numero_cte_homologacao = null,
        public ?string $serie_cte_producao = null,
        public ?string $serie_cte_homologacao = null,
        public ?string $proximo_numero_cte_os_producao = null,
        public ?string $proximo_numero_cte_os_homologacao = null,
        public ?string $serie_cte_os_producao = null,
        public ?string $serie_cte_os_homologacao = null,
        public ?string $proximo_numero_mdfe_producao = null,
        public ?string $proximo_numero_mdfe_homologacao = null,
        public ?string $serie_mdfe_producao = null,
        public ?string $serie_mdfe_homologacao = null,
        public ?string $proximo_numero_nfcom_producao = null,
        public ?string $proximo_numero_nfcom_homologacao = null,
        public ?string $serie_nfcom_producao = null,
        public ?string $serie_nfcom_homologacao = null,

        // Certificado digital
        public ?string $arquivo_certificado_base64 = null,
        public ?string $senha_certificado = null,

        // Logo
        public ?string $arquivo_logo_base64 = null,
        public ?bool $delete_logo = null,

        // Responsável
        public ?string $nome_responsavel = null,
        public ?string $cpf_responsavel = null,
        public ?string $login_responsavel = null,
        public ?string $senha_responsavel = null,
        public ?bool $senha_responsavel_preenchida = null,

        // Contabilidade
        public ?string $cpf_cnpj_contabilidade = null,
        public ?string $data_inicio_recebimento_nfe = null,
        public ?string $data_inicio_recebimento_cte = null,

        // SMTP
        public ?string $smtp_endereco = null,
        public ?string $smtp_dominio = null,
        public ?int $smtp_porta = null,
        public ?SmtpAutenticacao $smtp_autenticacao = null,
        public ?string $smtp_login = null,
        public ?string $smtp_senha = null,
        public ?string $smtp_remetente = null,
        public ?string $smtp_responder_para = null,
        public ?SmtpVerificacaoOpenssl $smtp_modo_verificacao_openssl = null,
        public ?bool $smtp_habilita_starttls = null,
        public ?bool $smtp_ssl = null,
        public ?bool $smtp_tls = null,

        // Processamento síncrono
        public ?bool $nfe_sincrono = null,
        public ?bool $nfe_sincrono_homologacao = null,
        public ?bool $mdfe_sincrono = null,
        public ?bool $mdfe_sincrono_homologacao = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            nome: $data['nome'] ?? null,
            nome_fantasia: $data['nome_fantasia'] ?? null,
            cnpj: $data['cnpj'] ?? null,
            cpf: $data['cpf'] ?? null,
            inscricao_estadual: $data['inscricao_estadual'] ?? null,
            inscricao_municipal: $data['inscricao_municipal'] ?? null,
            regime_tributario: isset($data['regime_tributario'])
                ? RegimeTributario::from($data['regime_tributario'])
                : null,
            logradouro: $data['logradouro'] ?? null,
            numero: $data['numero'] ?? null,
            complemento: $data['complemento'] ?? null,
            bairro: $data['bairro'] ?? null,
            municipio: $data['municipio'] ?? null,
            cep: $data['cep'] ?? null,
            uf: $data['uf'] ?? null,
            telefone: $data['telefone'] ?? null,
            email: $data['email'] ?? null,
            habilita_nfe: $data['habilita_nfe'] ?? null,
            habilita_nfce: $data['habilita_nfce'] ?? null,
            habilita_nfse: $data['habilita_nfse'] ?? null,
            habilita_nfsen_producao: $data['habilita_nfsen_producao'] ?? null,
            habilita_nfsen_homologacao: $data['habilita_nfsen_homologacao'] ?? null,
            habilita_cte: $data['habilita_cte'] ?? null,
            habilita_mdfe: $data['habilita_mdfe'] ?? null,
            habilita_nfcom: $data['habilita_nfcom'] ?? null,
            habilita_manifestacao: $data['habilita_manifestacao'] ?? null,
            habilita_manifestacao_cte: $data['habilita_manifestacao_cte'] ?? null,
            habilita_nfsen_recebidas_producao: $data['habilita_nfsen_recebidas_producao'] ?? null,
            habilita_nfsen_recebidas_homologacao: $data['habilita_nfsen_recebidas_homologacao'] ?? null,
            enviar_email_destinatario: $data['enviar_email_destinatario'] ?? null,
            enviar_email_homologacao: $data['enviar_email_homologacao'] ?? null,
            discrimina_impostos: $data['discrimina_impostos'] ?? null,
            habilita_contingencia_offline_nfce: $data['habilita_contingencia_offline_nfce'] ?? null,
            reaproveita_numero_nfce_contingencia: $data['reaproveita_numero_nfce_contingencia'] ?? null,
            csc_nfce_producao: $data['csc_nfce_producao'] ?? null,
            id_token_nfce_producao: $data['id_token_nfce_producao'] ?? null,
            csc_nfce_homologacao: $data['csc_nfce_homologacao'] ?? null,
            id_token_nfce_homologacao: $data['id_token_nfce_homologacao'] ?? null,
            orientacao_danfe: isset($data['orientacao_danfe'])
                ? OrientacaoDanfe::from($data['orientacao_danfe'])
                : null,
            recibo_danfe: $data['recibo_danfe'] ?? null,
            exibe_sempre_ipi_danfe: $data['exibe_sempre_ipi_danfe'] ?? null,
            exibe_issqn_danfe: $data['exibe_issqn_danfe'] ?? null,
            exibe_impostos_adicionais_danfe: $data['exibe_impostos_adicionais_danfe'] ?? null,
            exibe_rastro_danfe: $data['exibe_rastro_danfe'] ?? null,
            exibe_unidade_tributaria_danfe: $data['exibe_unidade_tributaria_danfe'] ?? null,
            exibe_sempre_volumes_danfe: $data['exibe_sempre_volumes_danfe'] ?? null,
            exibe_composicao_carga_mdfe: $data['exibe_composicao_carga_mdfe'] ?? null,
            mostrar_danfse_badge: $data['mostrar_danfse_badge'] ?? null,
            proximo_numero_nfe_producao: $data['proximo_numero_nfe_producao'] ?? null,
            proximo_numero_nfe_homologacao: $data['proximo_numero_nfe_homologacao'] ?? null,
            serie_nfe_producao: $data['serie_nfe_producao'] ?? null,
            serie_nfe_homologacao: $data['serie_nfe_homologacao'] ?? null,
            proximo_numero_nfce_producao: $data['proximo_numero_nfce_producao'] ?? null,
            proximo_numero_nfce_homologacao: $data['proximo_numero_nfce_homologacao'] ?? null,
            serie_nfce_producao: $data['serie_nfce_producao'] ?? null,
            serie_nfce_homologacao: $data['serie_nfce_homologacao'] ?? null,
            proximo_numero_nfse_producao: $data['proximo_numero_nfse_producao'] ?? null,
            proximo_numero_nfse_homologacao: $data['proximo_numero_nfse_homologacao'] ?? null,
            serie_nfse_producao: $data['serie_nfse_producao'] ?? null,
            serie_nfse_homologacao: $data['serie_nfse_homologacao'] ?? null,
            proximo_numero_nfsen_producao: $data['proximo_numero_nfsen_producao'] ?? null,
            proximo_numero_nfsen_homologacao: $data['proximo_numero_nfsen_homologacao'] ?? null,
            serie_nfsen_producao: $data['serie_nfsen_producao'] ?? null,
            serie_nfsen_homologacao: $data['serie_nfsen_homologacao'] ?? null,
            proximo_numero_cte_producao: $data['proximo_numero_cte_producao'] ?? null,
            proximo_numero_cte_homologacao: $data['proximo_numero_cte_homologacao'] ?? null,
            serie_cte_producao: $data['serie_cte_producao'] ?? null,
            serie_cte_homologacao: $data['serie_cte_homologacao'] ?? null,
            proximo_numero_cte_os_producao: $data['proximo_numero_cte_os_producao'] ?? null,
            proximo_numero_cte_os_homologacao: $data['proximo_numero_cte_os_homologacao'] ?? null,
            serie_cte_os_producao: $data['serie_cte_os_producao'] ?? null,
            serie_cte_os_homologacao: $data['serie_cte_os_homologacao'] ?? null,
            proximo_numero_mdfe_producao: $data['proximo_numero_mdfe_producao'] ?? null,
            proximo_numero_mdfe_homologacao: $data['proximo_numero_mdfe_homologacao'] ?? null,
            serie_mdfe_producao: $data['serie_mdfe_producao'] ?? null,
            serie_mdfe_homologacao: $data['serie_mdfe_homologacao'] ?? null,
            proximo_numero_nfcom_producao: $data['proximo_numero_nfcom_producao'] ?? null,
            proximo_numero_nfcom_homologacao: $data['proximo_numero_nfcom_homologacao'] ?? null,
            serie_nfcom_producao: $data['serie_nfcom_producao'] ?? null,
            serie_nfcom_homologacao: $data['serie_nfcom_homologacao'] ?? null,
            arquivo_certificado_base64: $data['arquivo_certificado_base64'] ?? null,
            senha_certificado: $data['senha_certificado'] ?? null,
            arquivo_logo_base64: $data['arquivo_logo_base64'] ?? null,
            delete_logo: $data['delete_logo'] ?? null,
            nome_responsavel: $data['nome_responsavel'] ?? null,
            cpf_responsavel: $data['cpf_responsavel'] ?? null,
            login_responsavel: $data['login_responsavel'] ?? null,
            senha_responsavel: $data['senha_responsavel'] ?? null,
            senha_responsavel_preenchida: $data['senha_responsavel_preenchida'] ?? null,
            cpf_cnpj_contabilidade: $data['cpf_cnpj_contabilidade'] ?? null,
            data_inicio_recebimento_nfe: $data['data_inicio_recebimento_nfe'] ?? null,
            data_inicio_recebimento_cte: $data['data_inicio_recebimento_cte'] ?? null,
            smtp_endereco: $data['smtp_endereco'] ?? null,
            smtp_dominio: $data['smtp_dominio'] ?? null,
            smtp_porta: $data['smtp_porta'] ?? null,
            smtp_autenticacao: isset($data['smtp_autenticacao'])
                ? SmtpAutenticacao::from($data['smtp_autenticacao'])
                : null,
            smtp_login: $data['smtp_login'] ?? null,
            smtp_senha: $data['smtp_senha'] ?? null,
            smtp_remetente: $data['smtp_remetente'] ?? null,
            smtp_responder_para: $data['smtp_responder_para'] ?? null,
            smtp_modo_verificacao_openssl: isset($data['smtp_modo_verificacao_openssl'])
                ? SmtpVerificacaoOpenssl::from($data['smtp_modo_verificacao_openssl'])
                : null,
            smtp_habilita_starttls: $data['smtp_habilita_starttls'] ?? null,
            smtp_ssl: $data['smtp_ssl'] ?? null,
            smtp_tls: $data['smtp_tls'] ?? null,
            nfe_sincrono: $data['nfe_sincrono'] ?? null,
            nfe_sincrono_homologacao: $data['nfe_sincrono_homologacao'] ?? null,
            mdfe_sincrono: $data['mdfe_sincrono'] ?? null,
            mdfe_sincrono_homologacao: $data['mdfe_sincrono_homologacao'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'nome' => $this->nome,
            'nome_fantasia' => $this->nome_fantasia,
            'cnpj' => $this->cnpj,
            'cpf' => $this->cpf,
            'inscricao_estadual' => $this->inscricao_estadual,
            'inscricao_municipal' => $this->inscricao_municipal,
            'regime_tributario' => $this->regime_tributario?->value,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'municipio' => $this->municipio,
            'cep' => $this->cep,
            'uf' => $this->uf,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'habilita_nfe' => $this->habilita_nfe,
            'habilita_nfce' => $this->habilita_nfce,
            'habilita_nfse' => $this->habilita_nfse,
            'habilita_nfsen_producao' => $this->habilita_nfsen_producao,
            'habilita_nfsen_homologacao' => $this->habilita_nfsen_homologacao,
            'habilita_cte' => $this->habilita_cte,
            'habilita_mdfe' => $this->habilita_mdfe,
            'habilita_nfcom' => $this->habilita_nfcom,
            'habilita_manifestacao' => $this->habilita_manifestacao,
            'habilita_manifestacao_cte' => $this->habilita_manifestacao_cte,
            'habilita_nfsen_recebidas_producao' => $this->habilita_nfsen_recebidas_producao,
            'habilita_nfsen_recebidas_homologacao' => $this->habilita_nfsen_recebidas_homologacao,
            'enviar_email_destinatario' => $this->enviar_email_destinatario,
            'enviar_email_homologacao' => $this->enviar_email_homologacao,
            'discrimina_impostos' => $this->discrimina_impostos,
            'habilita_contingencia_offline_nfce' => $this->habilita_contingencia_offline_nfce,
            'reaproveita_numero_nfce_contingencia' => $this->reaproveita_numero_nfce_contingencia,
            'csc_nfce_producao' => $this->csc_nfce_producao,
            'id_token_nfce_producao' => $this->id_token_nfce_producao,
            'csc_nfce_homologacao' => $this->csc_nfce_homologacao,
            'id_token_nfce_homologacao' => $this->id_token_nfce_homologacao,
            'orientacao_danfe' => $this->orientacao_danfe?->value,
            'recibo_danfe' => $this->recibo_danfe,
            'exibe_sempre_ipi_danfe' => $this->exibe_sempre_ipi_danfe,
            'exibe_issqn_danfe' => $this->exibe_issqn_danfe,
            'exibe_impostos_adicionais_danfe' => $this->exibe_impostos_adicionais_danfe,
            'exibe_rastro_danfe' => $this->exibe_rastro_danfe,
            'exibe_unidade_tributaria_danfe' => $this->exibe_unidade_tributaria_danfe,
            'exibe_sempre_volumes_danfe' => $this->exibe_sempre_volumes_danfe,
            'exibe_composicao_carga_mdfe' => $this->exibe_composicao_carga_mdfe,
            'mostrar_danfse_badge' => $this->mostrar_danfse_badge,
            'proximo_numero_nfe_producao' => $this->proximo_numero_nfe_producao,
            'proximo_numero_nfe_homologacao' => $this->proximo_numero_nfe_homologacao,
            'serie_nfe_producao' => $this->serie_nfe_producao,
            'serie_nfe_homologacao' => $this->serie_nfe_homologacao,
            'proximo_numero_nfce_producao' => $this->proximo_numero_nfce_producao,
            'proximo_numero_nfce_homologacao' => $this->proximo_numero_nfce_homologacao,
            'serie_nfce_producao' => $this->serie_nfce_producao,
            'serie_nfce_homologacao' => $this->serie_nfce_homologacao,
            'proximo_numero_nfse_producao' => $this->proximo_numero_nfse_producao,
            'proximo_numero_nfse_homologacao' => $this->proximo_numero_nfse_homologacao,
            'serie_nfse_producao' => $this->serie_nfse_producao,
            'serie_nfse_homologacao' => $this->serie_nfse_homologacao,
            'proximo_numero_nfsen_producao' => $this->proximo_numero_nfsen_producao,
            'proximo_numero_nfsen_homologacao' => $this->proximo_numero_nfsen_homologacao,
            'serie_nfsen_producao' => $this->serie_nfsen_producao,
            'serie_nfsen_homologacao' => $this->serie_nfsen_homologacao,
            'proximo_numero_cte_producao' => $this->proximo_numero_cte_producao,
            'proximo_numero_cte_homologacao' => $this->proximo_numero_cte_homologacao,
            'serie_cte_producao' => $this->serie_cte_producao,
            'serie_cte_homologacao' => $this->serie_cte_homologacao,
            'proximo_numero_cte_os_producao' => $this->proximo_numero_cte_os_producao,
            'proximo_numero_cte_os_homologacao' => $this->proximo_numero_cte_os_homologacao,
            'serie_cte_os_producao' => $this->serie_cte_os_producao,
            'serie_cte_os_homologacao' => $this->serie_cte_os_homologacao,
            'proximo_numero_mdfe_producao' => $this->proximo_numero_mdfe_producao,
            'proximo_numero_mdfe_homologacao' => $this->proximo_numero_mdfe_homologacao,
            'serie_mdfe_producao' => $this->serie_mdfe_producao,
            'serie_mdfe_homologacao' => $this->serie_mdfe_homologacao,
            'proximo_numero_nfcom_producao' => $this->proximo_numero_nfcom_producao,
            'proximo_numero_nfcom_homologacao' => $this->proximo_numero_nfcom_homologacao,
            'serie_nfcom_producao' => $this->serie_nfcom_producao,
            'serie_nfcom_homologacao' => $this->serie_nfcom_homologacao,
            'arquivo_certificado_base64' => $this->arquivo_certificado_base64,
            'senha_certificado' => $this->senha_certificado,
            'arquivo_logo_base64' => $this->arquivo_logo_base64,
            'delete_logo' => $this->delete_logo,
            'nome_responsavel' => $this->nome_responsavel,
            'cpf_responsavel' => $this->cpf_responsavel,
            'login_responsavel' => $this->login_responsavel,
            'senha_responsavel' => $this->senha_responsavel,
            'senha_responsavel_preenchida' => $this->senha_responsavel_preenchida,
            'cpf_cnpj_contabilidade' => $this->cpf_cnpj_contabilidade,
            'data_inicio_recebimento_nfe' => $this->data_inicio_recebimento_nfe,
            'data_inicio_recebimento_cte' => $this->data_inicio_recebimento_cte,
            'smtp_endereco' => $this->smtp_endereco,
            'smtp_dominio' => $this->smtp_dominio,
            'smtp_porta' => $this->smtp_porta,
            'smtp_autenticacao' => $this->smtp_autenticacao?->value,
            'smtp_login' => $this->smtp_login,
            'smtp_senha' => $this->smtp_senha,
            'smtp_remetente' => $this->smtp_remetente,
            'smtp_responder_para' => $this->smtp_responder_para,
            'smtp_modo_verificacao_openssl' => $this->smtp_modo_verificacao_openssl?->value,
            'smtp_habilita_starttls' => $this->smtp_habilita_starttls,
            'smtp_ssl' => $this->smtp_ssl,
            'smtp_tls' => $this->smtp_tls,
            'nfe_sincrono' => $this->nfe_sincrono,
            'nfe_sincrono_homologacao' => $this->nfe_sincrono_homologacao,
            'mdfe_sincrono' => $this->mdfe_sincrono,
            'mdfe_sincrono_homologacao' => $this->mdfe_sincrono_homologacao,
        ], fn (mixed $v): bool => $v !== null);
    }
}
```

**Important note on `toArray()` null filtering:** The `array_filter` with `$v !== null` correctly preserves `false` booleans and `0` integers — only `null` values are removed. This is critical because fields like `habilita_nfe: false` and `numero: 0` are valid API values that must be sent.

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Companies/DTO/EmpresaRequestTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/Companies/DTO/EmpresaRequest.php tests/Unit/Companies/DTO/EmpresaRequestTest.php
git commit -m "add EmpresaRequest DTO with enum support and tests"
```

---

### Task 12: Update Companies::create() and Companies::update() to accept DTO|array

**Files:**
- Modify: `src/Companies.php`
- Modify: `tests/Unit/CompaniesTest.php`

- [ ] **Step 1: Add tests for create and update with DTO and array**

Add imports to `tests/Unit/CompaniesTest.php`:
```php
use Larafocus\Companies\DTO\Enums\RegimeTributario;
use Larafocus\Companies\DTO\EmpresaRequest;
```

Add tests:
```php
test('create method accepts EmpresaRequest DTO', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste',
        cnpj: '12345678000195',
        regime_tributario: RegimeTributario::SimplesNacional,
    );

    $response = Focus::setup(environment: Environment::Production)->companies()->create($request);

    $this->assertRequest('POST', '/empresas', $response);
});

test('create method accepts array and converts to DTO', function () {
    $response = Focus::setup(environment: Environment::Production)->companies()->create([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000195',
        'regime_tributario' => 1,
    ]);

    $this->assertRequest('POST', '/empresas', $response);
});

test('update method accepts EmpresaRequest DTO', function () {
    $request = new EmpresaRequest(nome: 'Novo Nome');

    $response = Focus::setup(environment: Environment::Production)
        ->companies()
        ->update('company-id', $request);

    $this->assertRequest('PUT', '/empresas/company-id', $response);
});

test('update method accepts array and converts to DTO', function () {
    $response = Focus::setup(environment: Environment::Production)
        ->companies()
        ->update('company-id', ['nome' => 'Novo Nome']);

    $this->assertRequest('PUT', '/empresas/company-id', $response);
});
```

- [ ] **Step 2: Update Companies::create() method**

In `src/Companies.php`, add import:
```php
use Larafocus\Companies\DTO\EmpresaRequest;
```

Update `create()`:
```php
/**
 * @param  EmpresaRequest|array<string, mixed>  $parameters
 */
public function create(EmpresaRequest|array $parameters = new EmpresaRequest()): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = EmpresaRequest::fromArray($parameters);
    }

    $dryRun = $this->environment === Environment::Sandbox ? '?dry_run=1' : '';

    return $this->http->post('/empresas'.$dryRun, $parameters->toArray());
}
```

- [ ] **Step 3: Update Companies::update() method**

```php
/**
 * @param  EmpresaRequest|array<string, mixed>  $parameters
 */
public function update(string $id, EmpresaRequest|array $parameters = new EmpresaRequest()): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = EmpresaRequest::fromArray($parameters);
    }

    $url = '/empresas/'.urlencode($id).($this->environment === Environment::Sandbox ? '?dry_run=1' : '');

    return $this->http->put($url, $parameters->toArray());
}
```

- [ ] **Step 4: Run all Companies tests**

Run: `./vendor/bin/pest tests/Unit/CompaniesTest.php tests/Unit/Companies/ --parallel`
Expected: ALL PASS

- [ ] **Step 5: Commit**

```bash
git add src/Companies.php tests/Unit/CompaniesTest.php
git commit -m "update Companies::create() and update() to accept EmpresaRequest DTO or array"
```

---

## Chunk 5: Quality Gates

### Task 13: Run full quality suite

- [ ] **Step 1: Run all tests**

```bash
./vendor/bin/pest --coverage --min=100 --parallel
```

- [ ] **Step 2: Run mutation tests**

```bash
./vendor/bin/pest --mutate --min=100 --parallel
```

- [ ] **Step 3: Run type coverage**

```bash
./vendor/bin/pest --type-coverage --min=100
```

- [ ] **Step 4: Run static analysis**

```bash
./vendor/bin/rector --dry-run
./vendor/bin/phpstan analyse
./vendor/bin/psalm --taint-analysis
./vendor/bin/pint -p
```

- [ ] **Step 5: Fix any issues found**

Common issues to expect:
- Mutation testing may require additional edge case tests (boundary values)
- PHPStan may need more precise `@phpstan-param` annotations on `fromArray()` for EmpresaRequest
- Pint may reformat files (re-run full suite after)
- Type coverage may flag missing return type annotations

- [ ] **Step 6: Re-run full suite if any files changed**

```bash
./vendor/bin/pest --coverage --min=100 --parallel
```

- [ ] **Step 7: Final commit**

```bash
git add -A
git commit -m "fix quality gate issues for remaining DTOs"
```
