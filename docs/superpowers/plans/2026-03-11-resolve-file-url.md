# `resolveFileUrl` Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add `resolveFileUrl(string $relativePath): string` to `FocusManager` so consumers can build full download URLs from relative paths returned by the FocusNFe API.

**Architecture:** Single public method on `FocusManager`, exposed via `Focus` facade. Uses existing `resolveEnvironment()` and config to read the endpoint base URL (without `/v2` prefix).

**Tech Stack:** PHP 8.2+, Laravel, Pest

---

## Chunk 1: Implementation

### Task 1: Write failing tests

**Files:**
- Modify: `tests/Unit/FocusTest.php`

- [ ] **Step 1: Add tests for `resolveFileUrl`**

Append to `tests/Unit/FocusTest.php`:

```php
test('resolveFileUrl returns full url for sandbox environment', function () {
    expect(Focus::resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml');
});

test('resolveFileUrl returns full url for production environment', function () {
    Focus::config(environment: Environment::Production, token: 'test-token');

    expect(Focus::resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://api.focusnfe.com.br/v2/nfse/abc123.xml');
});

test('resolveFileUrl respects environment set via using', function () {
    $manager = Focus::using(environment: Environment::Production, token: 'test-token');

    expect($manager->resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://api.focusnfe.com.br/v2/nfse/abc123.xml');
});

test('resolveFileUrl via using does not mutate the singleton', function () {
    Focus::using(environment: Environment::Production, token: 'test-token')
        ->resolveFileUrl('/v2/nfse/abc123.xml');

    expect(Focus::resolveFileUrl('/v2/nfse/abc123.xml'))
        ->toBe('https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Unit/FocusTest.php --filter="resolveFileUrl"`
Expected: FAIL — method `resolveFileUrl` does not exist

### Task 2: Implement `resolveFileUrl`

**Files:**
- Modify: `src/Infrastructure/FocusManager.php:89` (add method before `buildHttp`)

- [ ] **Step 3: Add `resolveFileUrl` to `FocusManager`**

Add before the `private function buildHttp()` method:

```php
public function resolveFileUrl(string $relativePath): string
{
    $environment = $this->resolveEnvironment();

    return config()->string('larafocus.'.$environment->value.'.endpoint').$relativePath;
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Unit/FocusTest.php --filter="resolveFileUrl"`
Expected: PASS (4 tests)

### Task 3: Run full quality checks

- [ ] **Step 5: Run full quality suite**

```bash
./vendor/bin/pest --coverage --min=100 --parallel
./vendor/bin/pest --mutate --min=100 --parallel
./vendor/bin/pest --type-coverage --min=100
./vendor/bin/rector --dry-run
./vendor/bin/phpstan analyse
./vendor/bin/psalm --taint-analysis
./vendor/bin/pint -p
```

Fix any issues found. If pint or rector changed files, re-run the full test suite.

### Task 4: Commit

- [ ] **Step 6: Commit all changes**

```bash
git add src/Infrastructure/FocusManager.php tests/Unit/FocusTest.php docs/superpowers/specs/2026-03-11-resolve-file-url-design.md
git commit -m "add resolveFileUrl method to FocusManager for building download URLs from relative paths"
```
