# Design: `resolveFileUrl` on FocusManager

## Problem

The FocusNFe API returns relative paths for file downloads (e.g., `caminho_xml_nota_fiscal`, `caminho_xml_cancelamento`) that need to be combined with the base endpoint URL to form a complete download URL. The old `getEndpoint()` method that served this purpose was removed during a refactoring.

## Solution

Add `resolveFileUrl(string $relativePath): string` to `FocusManager`.

### Behavior

- Resolves the current environment (sandbox or production) using existing `resolveEnvironment()`
- Reads the endpoint from config: `larafocus.{environment}.endpoint`
- Concatenates endpoint + relative path
- Returns the full URL

### Signature

```php
public function resolveFileUrl(string $relativePath): string
{
    $environment = $this->resolveEnvironment();

    return config()->string('larafocus.'.$environment->value.'.endpoint').$relativePath;
}
```

### Facade

Exposed via `Focus::resolveFileUrl($path)`. The `Focus` facade mixin PHPDoc must be updated.

### Usage

```php
Focus::resolveFileUrl($json['caminho_xml_nota_fiscal']);
// => "https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml"

Focus::resolveFileUrl($json['caminho_xml_cancelamento']);
// => "https://homologacao.focusnfe.com.br/v2/nfse/abc123-cancelamento.xml"
```

### Design Decisions

- **No `/v2` prefix included**: The API already returns paths with the prefix included.
- **Uses config directly** (option A): Simple approach. Does not alter `Http` class or add internal state to `FocusManager`.
- **Respects `using()`/`config()`**: Environment overrides work because `resolveEnvironment()` is reused.

### Tests

- Returns correct URL for sandbox environment
- Returns correct URL for production environment
- Respects environment set via `using()`
- Respects environment set via `config()`
