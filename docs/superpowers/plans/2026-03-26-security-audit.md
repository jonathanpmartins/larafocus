# Security Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Proactively find and fix security vulnerabilities in Larafocus, then prevent regressions with automated tests.

**Architecture:** Three-phase approach — Phase A produces a written security audit report, Phase B+C interleave security tests (RED) with fixes (GREEN) per finding using TDD. Each finding gets its own commit.

**Tech Stack:** PHP 8.2+, Pest, PHPStan 9, Psalm taint analysis, Pint, Rector

---

## File Structure

**Files to create:**
- `docs/security-audit-report.md` — Phase A audit findings
- `tests/Unit/Security/ResolveFileUrlSecurityTest.php` — path traversal tests
- `tests/Unit/Security/WebhookUrlSecurityTest.php` — URL injection tests
- `tests/Unit/Security/EmailValidationSecurityTest.php` — email format tests
- `tests/Unit/Security/Base64SizeLimitSecurityTest.php` — file size tests
- `.env.example` — safe placeholder for developer onboarding

**Files to modify:**
- `src/Infrastructure/FocusManager.php:91-96` — add path validation to `resolveFileUrl()`
- `src/Hooks/DTO/WebhookRequest.php:12-19` — add URL validation to constructor
- `src/Nfse/DTO/NfseEmailRequest.php:13-21` — add email format validation to constructor
- `src/Companies/DTO/EmpresaRequest.php:16-151` — add base64 size limits to constructor

---

## Phase A: Security Audit Report

### Task 1: Write security audit report

**Files:**
- Create: `docs/security-audit-report.md`

- [ ] **Step 1: Create the audit report**

```markdown
# Larafocus Security Audit Report

**Date:** 2026-03-26
**Auditor:** AI-assisted (Claude Opus 4.6)
**Scope:** All source files in `src/`, `config/`, OpenAPI specs

---

## Executive Summary

Larafocus is a Laravel API client package with strong security fundamentals (PHPStan 9, Psalm taint analysis, 100% test coverage + mutation testing, immutable DTOs). The attack surface is limited — it is an outbound HTTP client with no incoming request handling, no database access, and no shell execution.

Five actionable findings were identified. None are remotely exploitable zero-days — all require the consuming application to pass untrusted input directly to the package without its own validation. However, defense-in-depth demands the package validates its own inputs.

---

## Findings

### FINDING-001: resolveFileUrl() accepts unvalidated paths [HIGH]

**Location:** `src/Infrastructure/FocusManager.php:91-96`
**Vector:** Path traversal via `..` segments or null bytes in `$relativePath`
**Current code:**
The method concatenates the base endpoint URL with `$relativePath` without validation.
**Exploitability:** LOW — requires consuming app to pass attacker-controlled input to `resolveFileUrl()`. In practice, paths come from API responses (trusted source). However, if a compromised API returns malicious paths, the constructed URL could point to unintended locations.
**Recommendation:** Reject paths containing `..` or null bytes. Require path starts with `/`.

### FINDING-002: WebhookRequest accepts any URL without validation [HIGH]

**Location:** `src/Hooks/DTO/WebhookRequest.php:12-19`
**Vector:** URL scheme injection (`javascript:`, `file://`, `ftp://`), malformed URLs
**Current code:**
The `$url` property is a plain string with no format or scheme validation.
**Exploitability:** MEDIUM — if a consuming app allows users to configure webhook URLs, an attacker could register webhooks with dangerous schemes. The FocusNFe API likely validates URLs server-side, but the package should not rely on external validation.
**Recommendation:** Validate URL format with `filter_var(FILTER_VALIDATE_URL)`. Restrict schemes to `https` and `http`.

### FINDING-003: NfseEmailRequest does not validate email format [MEDIUM]

**Location:** `src/Nfse/DTO/NfseEmailRequest.php:13-21`
**Vector:** Email header injection, malformed addresses
**Current code:**
Validates count (1-10) but not individual email format. Strings like `"not-an-email"` or `"user@\r\nBCC:attacker@evil.com"` pass through.
**Exploitability:** LOW — the FocusNFe API handles actual email delivery and likely validates format. But malformed emails waste API calls and could trigger unexpected API errors.
**Recommendation:** Validate each email with `filter_var(FILTER_VALIDATE_EMAIL)`.

### FINDING-004: EmpresaRequest has no size limit on base64 fields [MEDIUM]

**Location:** `src/Companies/DTO/EmpresaRequest.php:113,117`
**Vector:** Memory exhaustion via oversized base64 strings for `arquivo_certificado_base64` and `arquivo_logo_base64`
**Current code:**
Both fields are nullable strings with no length constraint. A multi-gigabyte string would be accepted.
**Exploitability:** LOW — requires consuming app to pass unchecked file content directly. Most apps validate file uploads before passing to the package. However, defense-in-depth demands the DTO protect itself.
**Recommendation:** Limit `arquivo_certificado_base64` to 14,000,000 chars (~10MB decoded). Limit `arquivo_logo_base64` to 2,800,000 chars (~2MB decoded).

### FINDING-005: No .env.example for developer onboarding [LOW]

**Location:** Project root
**Vector:** New contributors may create `.env` files with real credentials that could be accidentally committed if `.gitignore` is modified.
**Current state:** `.env` is gitignored. No `.env.example` exists to guide setup.
**Exploitability:** N/A — process issue, not a code vulnerability.
**Recommendation:** Add `.env.example` with placeholder values.

---

## Non-Findings (Investigated, Not Vulnerable)

| Area | Why Not Vulnerable |
|---|---|
| SQL Injection | No database access in package |
| Command Injection | No shell execution in package |
| XSS | No HTML rendering; data flows API-to-API |
| XXE | No XML parsing (XML sent as raw string to API) |
| Deserialization | No `unserialize()` or dynamic class instantiation |
| CSRF | Package does not handle browser HTTP requests |
| Token in logs | Laravel HTTP client does not log auth headers by default |
| FocusResponse JSON parsing | Already validates structure with `isset()`, `is_array()`, `is_string()` checks |
| CNPJ/CPF injection | Strict regex validation (`/^\d{14}$/`, `/^\d{11}$/`) |
| Enum bypass | PHP `Enum::from()` throws `ValueError` for invalid values |
| Null byte in URL path params | `urlencode()` safely encodes null bytes |

---

## Risk Assessment

**Overall risk: LOW**

This package has a minimal attack surface and strong existing safeguards. The five findings are defense-in-depth improvements, not exploitable vulnerabilities in typical usage. The consuming application is the primary security boundary.
```

- [ ] **Step 2: Commit the audit report**

```bash
git add docs/security-audit-report.md
git commit -m "add security audit report with 5 findings and risk assessment"
```

---

## Phase B+C: Security Tests + Fixes

### Task 2: Harden resolveFileUrl() against path traversal

**Files:**
- Create: `tests/Unit/Security/ResolveFileUrlSecurityTest.php`
- Modify: `src/Infrastructure/FocusManager.php:91-96`

- [ ] **Step 1: Write failing security tests**

Create `tests/Unit/Security/ResolveFileUrlSecurityTest.php`:

```php
<?php

use Larafocus\Focus;
use Larafocus\Infrastructure\FocusManager;

covers(FocusManager::class);

test('resolveFileUrl rejects path traversal with double dots', function () {
    Focus::resolveFileUrl('/../../../etc/passwd');
})->throws(InvalidArgumentException::class, 'must not contain path traversal');

test('resolveFileUrl rejects path with embedded double dots', function () {
    Focus::resolveFileUrl('/v2/nfse/../../../admin');
})->throws(InvalidArgumentException::class, 'must not contain path traversal');

test('resolveFileUrl rejects path with null bytes', function () {
    Focus::resolveFileUrl("/v2/nfse/abc\0.xml");
})->throws(InvalidArgumentException::class, 'must not contain null bytes');

test('resolveFileUrl rejects path not starting with forward slash', function () {
    Focus::resolveFileUrl('v2/nfse/abc.xml');
})->throws(InvalidArgumentException::class, 'must start with');

test('resolveFileUrl rejects empty path', function () {
    Focus::resolveFileUrl('');
})->throws(InvalidArgumentException::class, 'must start with');

test('resolveFileUrl accepts valid path with single dots', function () {
    expect(Focus::resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Security/ResolveFileUrlSecurityTest.php`
Expected: 5 tests FAIL (no validation exists yet), 1 test PASSES (valid path)

- [ ] **Step 3: Implement path validation in resolveFileUrl()**

In `src/Infrastructure/FocusManager.php`, replace the `resolveFileUrl` method:

```php
public function resolveFileUrl(string $relativePath): string
{
    if (! str_starts_with($relativePath, '/')) {
        throw new \InvalidArgumentException('Relative path must start with /.');
    }

    if (str_contains($relativePath, '..')) {
        throw new \InvalidArgumentException('Relative path must not contain path traversal sequences (..).');
    }

    if (str_contains($relativePath, "\0")) {
        throw new \InvalidArgumentException('Relative path must not contain null bytes.');
    }

    $environment = $this->resolveEnvironment();

    return config()->string('larafocus.'.$environment->value.'.endpoint').$relativePath;
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Security/ResolveFileUrlSecurityTest.php`
Expected: All 6 PASS

- [ ] **Step 5: Run full test suite**

Run: `./vendor/bin/pest --parallel`
Expected: All tests PASS (existing tests in FocusTest.php use valid paths like `/v2/nfse/abc123.xml`)

- [ ] **Step 6: Commit**

```bash
git add tests/Unit/Security/ResolveFileUrlSecurityTest.php src/Infrastructure/FocusManager.php
git commit -m "fix: validate resolveFileUrl() path against traversal and null bytes"
```

---

### Task 3: Harden WebhookRequest URL validation

**Files:**
- Create: `tests/Unit/Security/WebhookUrlSecurityTest.php`
- Modify: `src/Hooks/DTO/WebhookRequest.php:12-19`

- [ ] **Step 1: Write failing security tests**

Create `tests/Unit/Security/WebhookUrlSecurityTest.php`:

```php
<?php

use Larafocus\Hooks\DTO\Enums\WebhookEvent;
use Larafocus\Hooks\DTO\WebhookRequest;
use Larafocus\Shared\InvalidDtoException;

covers(WebhookRequest::class);

test('rejects javascript scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'javascript:alert(1)');
})->throws(InvalidDtoException::class, 'valid URL');

test('rejects file scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'file:///etc/passwd');
})->throws(InvalidDtoException::class, 'must use https or http');

test('rejects ftp scheme URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'ftp://evil.com/payload');
})->throws(InvalidDtoException::class, 'must use https or http');

test('rejects malformed URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: 'not a url at all');
})->throws(InvalidDtoException::class, 'valid URL');

test('rejects empty URL', function () {
    new WebhookRequest(event: WebhookEvent::Nfse, url: '');
})->throws(InvalidDtoException::class, 'valid URL');

test('accepts https URL', function () {
    $request = new WebhookRequest(event: WebhookEvent::Nfse, url: 'https://example.com/webhook');

    expect($request->url)->toBe('https://example.com/webhook');
});

test('accepts http URL for sandbox use', function () {
    $request = new WebhookRequest(event: WebhookEvent::Nfse, url: 'http://localhost:8080/webhook');

    expect($request->url)->toBe('http://localhost:8080/webhook');
});

test('fromArray rejects invalid URL', function () {
    WebhookRequest::fromArray(['event' => 'nfse', 'url' => 'javascript:alert(1)']);
})->throws(InvalidDtoException::class, 'valid URL');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Security/WebhookUrlSecurityTest.php`
Expected: 5 tests FAIL, 3 tests PASS

- [ ] **Step 3: Implement URL validation in WebhookRequest constructor**

In `src/Hooks/DTO/WebhookRequest.php`, add import and constructor body:

Add import after line 7:
```php
use Larafocus\Shared\InvalidDtoException;
```

Replace the constructor (lines 12-19):
```php
    public function __construct(
        public WebhookEvent $event,
        public string $url,
        public ?string $cnpj = null,
        public ?string $cpf = null,
        public ?string $authorization = null,
        public ?string $authorization_header = null,
    ) {
        if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidDtoException('url must be a valid URL.');
        }

        $scheme = parse_url($this->url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['https', 'http'], true)) {
            throw new InvalidDtoException('url must use https or http scheme.');
        }
    }
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Security/WebhookUrlSecurityTest.php`
Expected: All 8 PASS

- [ ] **Step 5: Run full test suite**

Run: `./vendor/bin/pest --parallel`
Expected: All tests PASS (existing WebhookRequestTest uses `'https://example.com/webhook'` which is valid)

- [ ] **Step 6: Commit**

```bash
git add tests/Unit/Security/WebhookUrlSecurityTest.php src/Hooks/DTO/WebhookRequest.php
git commit -m "fix: validate WebhookRequest URL format and scheme"
```

---

### Task 4: Harden NfseEmailRequest email format validation

**Files:**
- Create: `tests/Unit/Security/EmailValidationSecurityTest.php`
- Modify: `src/Nfse/DTO/NfseEmailRequest.php:13-21`

- [ ] **Step 1: Write failing security tests**

Create `tests/Unit/Security/EmailValidationSecurityTest.php`:

```php
<?php

use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Shared\InvalidDtoException;

covers(NfseEmailRequest::class);

test('rejects plaintext string that is not an email', function () {
    new NfseEmailRequest(emails: ['not-an-email']);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects email with header injection attempt', function () {
    new NfseEmailRequest(emails: ["user@example.com\r\nBCC:attacker@evil.com"]);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects empty string as email', function () {
    new NfseEmailRequest(emails: ['']);
})->throws(InvalidDtoException::class, 'not a valid email');

test('rejects mixed valid and invalid emails on the invalid one', function () {
    new NfseEmailRequest(emails: ['valid@example.com', 'invalid']);
})->throws(InvalidDtoException::class, '"invalid" is not a valid email');

test('accepts valid email addresses', function () {
    $request = new NfseEmailRequest(emails: ['user@example.com', 'admin@company.org']);

    expect($request->emails)->toBe(['user@example.com', 'admin@company.org']);
});

test('fromArray rejects invalid email', function () {
    NfseEmailRequest::fromArray(['emails' => ['bad-email']]);
})->throws(InvalidDtoException::class, 'not a valid email');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Security/EmailValidationSecurityTest.php`
Expected: 4 tests FAIL, 2 tests PASS

- [ ] **Step 3: Implement email format validation**

In `src/Nfse/DTO/NfseEmailRequest.php`, replace the constructor (lines 13-21):

```php
    /** @param array<int, string> $emails */
    public function __construct(
        public array $emails,
    ) {
        $count = count($this->emails);

        if ($count === 0 || $count > 10) {
            throw new InvalidDtoException('emails must contain between 1 and 10 addresses.');
        }

        foreach ($this->emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidDtoException(sprintf('"%s" is not a valid email address.', $email));
            }
        }
    }
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Security/EmailValidationSecurityTest.php`
Expected: All 6 PASS

- [ ] **Step 5: Run full test suite**

Run: `./vendor/bin/pest --parallel`
Expected: All tests PASS (existing NfseEmailRequestTest uses valid emails like `'a@example.com'`)

- [ ] **Step 6: Commit**

```bash
git add tests/Unit/Security/EmailValidationSecurityTest.php src/Nfse/DTO/NfseEmailRequest.php
git commit -m "fix: validate individual email format in NfseEmailRequest"
```

---

### Task 5: Add base64 size limits to EmpresaRequest

**Files:**
- Create: `tests/Unit/Security/Base64SizeLimitSecurityTest.php`
- Modify: `src/Companies/DTO/EmpresaRequest.php:16-151`

- [ ] **Step 1: Write failing security tests**

Create `tests/Unit/Security/Base64SizeLimitSecurityTest.php`:

```php
<?php

use Larafocus\Companies\DTO\EmpresaRequest;
use Larafocus\Shared\InvalidDtoException;

covers(EmpresaRequest::class);

test('rejects certificate base64 exceeding 10MB', function () {
    new EmpresaRequest(arquivo_certificado_base64: str_repeat('A', 14_000_001));
})->throws(InvalidDtoException::class, 'arquivo_certificado_base64');

test('accepts certificate base64 at exactly the limit', function () {
    $request = new EmpresaRequest(arquivo_certificado_base64: str_repeat('A', 14_000_000));

    expect($request->arquivo_certificado_base64)->toHaveLength(14_000_000);
});

test('rejects logo base64 exceeding 2MB', function () {
    new EmpresaRequest(arquivo_logo_base64: str_repeat('A', 2_800_001));
})->throws(InvalidDtoException::class, 'arquivo_logo_base64');

test('accepts logo base64 at exactly the limit', function () {
    $request = new EmpresaRequest(arquivo_logo_base64: str_repeat('A', 2_800_000));

    expect($request->arquivo_logo_base64)->toHaveLength(2_800_000);
});

test('accepts null certificate and logo', function () {
    $request = new EmpresaRequest;

    expect($request->arquivo_certificado_base64)->toBeNull()
        ->and($request->arquivo_logo_base64)->toBeNull();
});

test('fromArray rejects oversized certificate', function () {
    EmpresaRequest::fromArray(['arquivo_certificado_base64' => str_repeat('A', 14_000_001)]);
})->throws(InvalidDtoException::class, 'arquivo_certificado_base64');

test('fromArray rejects oversized logo', function () {
    EmpresaRequest::fromArray(['arquivo_logo_base64' => str_repeat('A', 2_800_001)]);
})->throws(InvalidDtoException::class, 'arquivo_logo_base64');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/Security/Base64SizeLimitSecurityTest.php`
Expected: 4 tests FAIL (rejection tests), 3 tests PASS (acceptance tests)

- [ ] **Step 3: Implement base64 size validation**

In `src/Companies/DTO/EmpresaRequest.php`, add import after line 11:

```php
use Larafocus\Shared\InvalidDtoException;
```

Replace the empty constructor body (line 151 `{}` becomes a block). The constructor currently ends at line 151 with `{}`. Change `151→    ) {}` to have a body:

```php
    ) {
        if ($this->arquivo_certificado_base64 !== null && mb_strlen($this->arquivo_certificado_base64) > 14_000_000) {
            throw new InvalidDtoException('arquivo_certificado_base64 must not exceed 14000000 characters (~10MB decoded).');
        }

        if ($this->arquivo_logo_base64 !== null && mb_strlen($this->arquivo_logo_base64) > 2_800_000) {
            throw new InvalidDtoException('arquivo_logo_base64 must not exceed 2800000 characters (~2MB decoded).');
        }
    }
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/Security/Base64SizeLimitSecurityTest.php`
Expected: All 7 PASS

- [ ] **Step 5: Run full test suite**

Run: `./vendor/bin/pest --parallel`
Expected: All tests PASS (existing EmpresaRequestTest uses `'base64cert=='` which is tiny)

- [ ] **Step 6: Commit**

```bash
git add tests/Unit/Security/Base64SizeLimitSecurityTest.php src/Companies/DTO/EmpresaRequest.php
git commit -m "fix: add base64 size limits for certificate and logo in EmpresaRequest"
```

---

### Task 6: Add .env.example for safe onboarding

**Files:**
- Create: `.env.example`

- [ ] **Step 1: Create .env.example with placeholder values**

Create `.env.example`:

```env
LARAFOCUS_ENVIRONMENT=sandbox
LARAFOCUS_SANDBOX_TOKEN=your-sandbox-token-here
LARAFOCUS_PRODUCTION_TOKEN=your-production-token-here
LARAFOCUS_MASTER_TOKEN=your-master-token-here
```

- [ ] **Step 2: Commit**

```bash
git add .env.example
git commit -m "add .env.example with placeholder values for safe onboarding"
```

---

### Task 7: Run full quality suite

- [ ] **Step 1: Run complete test suite with coverage**

Run: `./vendor/bin/pest --coverage --min=100 --parallel`
Expected: All tests PASS, 100% coverage

- [ ] **Step 2: Run mutation tests**

Run: `./vendor/bin/pest --mutate --min=100 --parallel`
Expected: 100% mutation score

- [ ] **Step 3: Run type coverage**

Run: `./vendor/bin/pest --type-coverage --min=100`
Expected: 100% type coverage

- [ ] **Step 4: Run Rector**

Run: `./vendor/bin/rector --dry-run`
Expected: No changes suggested

- [ ] **Step 5: Run PHPStan**

Run: `./vendor/bin/phpstan analyse`
Expected: No errors

- [ ] **Step 6: Run Psalm taint analysis**

Run: `./vendor/bin/psalm --taint-analysis`
Expected: No taint violations

- [ ] **Step 7: Run Pint formatter**

Run: `./vendor/bin/pint -p`
Expected: No changes OR apply changes

- [ ] **Step 8: If Pint changed files, re-run tests**

Run: `./vendor/bin/pest --coverage --min=100 --parallel`
Expected: All PASS

- [ ] **Step 9: Final commit if any formatting changes**

```bash
git add -A
git commit -m "style: apply pint formatting to security hardening changes"
```
