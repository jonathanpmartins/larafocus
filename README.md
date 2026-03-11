# Larafocus

Laravel package for integration with the [FocusNFe](https://focusnfe.com.br/) API.

Manage NFSe (electronic service invoices), companies, webhooks, and search municipalities — all through a clean, type-safe API with fully validated DTOs.

[![CI](https://github.com/jonathanpmartins/larafocus/actions/workflows/ci.yml/badge.svg)](https://github.com/jonathanpmartins/larafocus/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Extensions: `json`, `curl`

## Installation

```bash
composer require jonathanpmartins/larafocus
```

The service provider is auto-discovered.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="Larafocus\LarafocusServiceProvider"
```

Add these to your `.env`:

```dotenv
LARAFOCUS_ENVIRONMENT=sandbox
LARAFOCUS_SANDBOX_TOKEN=your-sandbox-token
LARAFOCUS_PRODUCTION_TOKEN=your-production-token
LARAFOCUS_MASTER_TOKEN=your-master-token
```

| Variable | Description | Default |
|---|---|---|
| `LARAFOCUS_ENVIRONMENT` | `sandbox` or `production` | `sandbox` |
| `LARAFOCUS_SANDBOX_TOKEN` | Token for sandbox API | — |
| `LARAFOCUS_PRODUCTION_TOKEN` | Token for production API | — |
| `LARAFOCUS_MASTER_TOKEN` | Master token for Companies API | — |
| `LARAFOCUS_PREFIX` | API path prefix | `/v2` |
| `LARAFOCUS_SANDBOX_ENDPOINT` | Sandbox base URL | `https://homologacao.focusnfe.com.br` |
| `LARAFOCUS_PRODUCTION_ENDPOINT` | Production base URL | `https://api.focusnfe.com.br` |

## Usage

All interactions go through the `Focus` facade, which provides four resources:

```php
use Larafocus\Focus;

Focus::nfse();       // NFSe operations
Focus::companies();  // Company management (requires master token)
Focus::hooks();      // Webhook management
Focus::search();     // Municipality, service, and tax code search
```

Every method returns a `FocusResponse` with:

```php
$response->statusCode; // int — HTTP status code
$response->success;    // bool — true if status < 400
$response->body;       // array — parsed JSON response
$response->errors;     // array — extracted errors (if any)
$response->response;   // Illuminate\Http\Client\Response — raw response
```

### NFSe

```php
use Larafocus\Focus;
use Larafocus\Nfse\DTO\NfseRequest;
use Larafocus\Nfse\DTO\NfseCancelRequest;
use Larafocus\Nfse\DTO\NfseEmailRequest;
use Larafocus\Nfse\DTO\Prestador;
use Larafocus\Nfse\DTO\Tomador;
use Larafocus\Nfse\DTO\Servico;
use Larafocus\Nfse\DTO\Enums\NaturezaOperacao;

// Create
$response = Focus::nfse()->create('ref-001', new NfseRequest(
    data_emissao: '2024-01-15T10:30:00-03:00',
    natureza_operacao: NaturezaOperacao::TributacaoMunicipio,
    optante_simples_nacional: true,
    prestador: new Prestador(
        cnpj: '12345678000195',
        inscricao_municipal: '12345',
    ),
    tomador: new Tomador(
        cnpj: '98765432000187',
        email: 'client@example.com',
    ),
    servico: new Servico(
        valor_servicos: 1500.00,
        iss_retido: false,
        item_lista_servico: '1.07',
        discriminacao: 'Desenvolvimento de software',
        codigo_municipio: '3550308',
    ),
));

// Get
$response = Focus::nfse()->get('ref-001');

// Cancel
$response = Focus::nfse()->cancel('ref-001', new NfseCancelRequest(
    justificativa: 'Cancelamento solicitado pelo tomador',
));

// Send by email
$response = Focus::nfse()->email('ref-001', new NfseEmailRequest(
    emails: ['recipient@example.com'],
));

// Trigger webhook
$response = Focus::nfse()->hook('ref-001');
```

Arrays are also accepted — they are converted to DTOs automatically:

```php
$response = Focus::nfse()->create('ref-002', [
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
```

### Companies

Requires `LARAFOCUS_MASTER_TOKEN`. Always hits the production endpoint.

```php
use Larafocus\Focus;
use Larafocus\Companies\DTO\EmpresaRequest;
use Larafocus\Companies\DTO\Enums\RegimeTributario;

// List (with optional offset for pagination)
$response = Focus::companies()->list();
$response = Focus::companies()->list(10);

// Get
$response = Focus::companies()->get('company-id');

// Create
$response = Focus::companies()->create(new EmpresaRequest(
    nome: 'Empresa LTDA',
    nome_fantasia: 'Empresa',
    cnpj: '12345678000195',
    regime_tributario: RegimeTributario::SimplesNacional,
    logradouro: 'Rua X',
    numero: 123,
    bairro: 'Centro',
    municipio: 'Sao Paulo',
    cep: 1310100,
    uf: 'SP',
    telefone: '1133334444',
    email: 'contact@empresa.com',
    habilita_nfse: true,
));

// Update
$response = Focus::companies()->update('company-id', new EmpresaRequest(
    nome: 'Novo Nome LTDA',
));

// Delete
$response = Focus::companies()->delete('company-id');
```

In sandbox environment, `create()` and `update()` automatically append `?dry_run=1`.

### Webhooks

```php
use Larafocus\Focus;
use Larafocus\Hooks\DTO\WebhookRequest;
use Larafocus\Hooks\DTO\Enums\WebhookEvent;

// List all
$response = Focus::hooks()->list();

// Create
$response = Focus::hooks()->create(new WebhookRequest(
    event: WebhookEvent::Nfse,
    url: 'https://example.com/webhook/nfse',
));

// Get
$response = Focus::hooks()->get('hook-id');

// Delete
$response = Focus::hooks()->delete('hook-id');
```

### Search

```php
use Larafocus\Focus;

// Municipalities
Focus::search()->cities()->list();
Focus::search()->cities()->get('3550308');

// Services for a city
Focus::search()->cities()->servicesFor('3550308')->list();
Focus::search()->cities()->servicesFor('3550308')->get('1.07');

// Tax codes for a city
Focus::search()->cities()->taxCodesFor('3550308')->list();
Focus::search()->cities()->taxCodesFor('3550308')->get('code-123');
```

### File URLs

The FocusNFe API returns relative paths for file downloads (XML, cancellation XML). Use `resolveFileUrl` to build the full URL:

```php
use Larafocus\Focus;

// Build download URL from a relative path
$xmlUrl = Focus::resolveFileUrl($response->body['caminho_xml_nota_fiscal']);
// => "https://homologacao.focusnfe.com.br/v2/nfse/abc123.xml"

$cancelXmlUrl = Focus::resolveFileUrl($response->body['caminho_xml_cancelamento']);
// => "https://homologacao.focusnfe.com.br/v2/nfse/abc123-cancelamento.xml"
```

The URL respects the current environment (sandbox or production).

### Switching Environments

```php
use Larafocus\Focus;
use Larafocus\Infrastructure\Environment;

// Temporary override (returns a new instance, does not affect the singleton)
Focus::using(
    environment: Environment::Production,
    token: 'custom-token',
    timeout: 30,
)->nfse()->get('ref-001');

// Persistent change (mutates the singleton)
Focus::config(
    environment: Environment::Production,
    token: 'production-token',
);
```

## Error Handling

DTOs validate their input on construction. Invalid data throws `InvalidDtoException`:

```php
use Larafocus\Shared\InvalidDtoException;

try {
    Focus::nfse()->cancel('ref', new NfseCancelRequest(
        justificativa: 'Too short', // must be 15-255 characters
    ));
} catch (InvalidDtoException $e) {
    echo $e->getMessage();
}
```

API errors are captured in `FocusResponse::$errors` without throwing:

```php
$response = Focus::nfse()->get('nonexistent-ref');

if (! $response->success) {
    foreach ($response->errors as $error) {
        // Handle API error
    }
}
```

## Testing

```bash
./vendor/bin/pest --coverage --min=100 --parallel
./vendor/bin/pest --mutate --min=100 --parallel
./vendor/bin/pest --type-coverage --min=100
./vendor/bin/phpstan analyse
./vendor/bin/psalm --taint-analysis
./vendor/bin/rector --dry-run
./vendor/bin/pint --test
```

## License

MIT. See [LICENSE](LICENSE) for details.
