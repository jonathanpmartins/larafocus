# Typed DTOs for Companies, Hooks, and NFSe cancel/email

## Context

NFSe `create()` already has typed DTOs. The remaining methods across Companies, Hooks, and NFSe still accept raw `array<string, mixed>`. This spec covers adding typed DTOs for all remaining request bodies.

## Scope

Request bodies only — Search query filters (Cities, Services, TaxCodes) are excluded as they are simple query parameters.

## Decisions

- **Backward compatibility**: All modified methods accept `DTO|array` with auto-conversion via `fromArray()`
- **Properties**: Snake_case matching API keys exactly (same pattern as NFSe DTOs)
- **Serialization**: Explicit `toArray()` on each DTO (handles null omission, enum values)
- **Validation**: Each domain validates inline — no shared trait. `ValidatesConstraints` stays in `src/Nfse/DTO/Concerns/` and is NOT used by other domains.
- **Exception**: All DTOs throw `Larafocus\Shared\InvalidDtoException` for validation failures

---

## Companies Domain

### EmpresaRequest DTO

Single flat readonly class with all optional properties. Used by both `create()` and `update()`. The OpenAPI spec defines no required fields — the API handles validation server-side.

**Enums:**

```php
enum RegimeTributario: int
{
    case SimplesNacional = 1;
    case SimplesNacionalExcesso = 2;
    case RegimeNormal = 3;
    case SimplesNacionalMei = 4;
}

enum OrientacaoDanfe: string
{
    case Portrait = 'portrait';
    case Landscape = 'landscape';
}

enum SmtpAutenticacao: string
{
    case Plain = 'plain';
    case Login = 'login';
    case CramMd5 = 'cram_md5';
}

enum SmtpVerificacaoOpenssl: string
{
    case Peer = 'peer';
    case None = 'none';
}
```

**EmpresaRequest properties** (all optional, grouped by concern):

```
EmpresaRequest
├── Dados básicos
│   ├── nome: ?string
│   ├── nome_fantasia: ?string
│   ├── cnpj: ?string
│   ├── cpf: ?string
│   ├── inscricao_estadual: ?int
│   ├── inscricao_municipal: ?int
│   └── regime_tributario: ?RegimeTributario
│
├── Endereço
│   ├── logradouro: ?string
│   ├── numero: ?int
│   ├── complemento: ?string
│   ├── bairro: ?string
│   ├── municipio: ?string
│   ├── cep: ?int
│   └── uf: ?string
│
├── Contato
│   ├── telefone: ?string
│   └── email: ?string
│
├── Habilitações de documentos
│   ├── habilita_nfe: ?bool
│   ├── habilita_nfce: ?bool
│   ├── habilita_nfse: ?bool
│   ├── habilita_nfsen_producao: ?bool
│   ├── habilita_nfsen_homologacao: ?bool
│   ├── habilita_cte: ?bool
│   ├── habilita_mdfe: ?bool
│   ├── habilita_nfcom: ?bool
│   ├── habilita_manifestacao: ?bool
│   ├── habilita_manifestacao_cte: ?bool
│   ├── habilita_nfsen_recebidas_producao: ?bool
│   └── habilita_nfsen_recebidas_homologacao: ?bool
│
├── Comunicação
│   ├── enviar_email_destinatario: ?bool
│   ├── enviar_email_homologacao: ?bool
│   └── discrimina_impostos: ?bool
│
├── NFCe
│   ├── habilita_contingencia_offline_nfce: ?bool
│   ├── reaproveita_numero_nfce_contingencia: ?bool
│   ├── csc_nfce_producao: ?string
│   ├── id_token_nfce_producao: ?int
│   ├── csc_nfce_homologacao: ?string
│   └── id_token_nfce_homologacao: ?int
│
├── DANFe
│   ├── orientacao_danfe: ?OrientacaoDanfe
│   ├── recibo_danfe: ?bool
│   ├── exibe_sempre_ipi_danfe: ?bool
│   ├── exibe_issqn_danfe: ?bool
│   ├── exibe_impostos_adicionais_danfe: ?bool
│   ├── exibe_rastro_danfe: ?bool
│   ├── exibe_unidade_tributaria_danfe: ?bool
│   ├── exibe_sempre_volumes_danfe: ?bool
│   ├── exibe_composicao_carga_mdfe: ?bool
│   └── mostrar_danfse_badge: ?bool
│
├── Numeração de documentos
│   ├── proximo_numero_nfe_producao: ?string
│   ├── proximo_numero_nfe_homologacao: ?string
│   ├── serie_nfe_producao: ?string
│   ├── serie_nfe_homologacao: ?string
│   ├── proximo_numero_nfce_producao: ?string
│   ├── proximo_numero_nfce_homologacao: ?string
│   ├── serie_nfce_producao: ?string
│   ├── serie_nfce_homologacao: ?string
│   ├── proximo_numero_nfse_producao: ?string
│   ├── proximo_numero_nfse_homologacao: ?string
│   ├── serie_nfse_producao: ?string
│   ├── serie_nfse_homologacao: ?string
│   ├── proximo_numero_nfsen_producao: ?string
│   ├── proximo_numero_nfsen_homologacao: ?string
│   ├── serie_nfsen_producao: ?string
│   ├── serie_nfsen_homologacao: ?string
│   ├── proximo_numero_cte_producao: ?string
│   ├── proximo_numero_cte_homologacao: ?string
│   ├── serie_cte_producao: ?string
│   ├── serie_cte_homologacao: ?string
│   ├── proximo_numero_cte_os_producao: ?string
│   ├── proximo_numero_cte_os_homologacao: ?string
│   ├── serie_cte_os_producao: ?string
│   ├── serie_cte_os_homologacao: ?string
│   ├── proximo_numero_mdfe_producao: ?string
│   ├── proximo_numero_mdfe_homologacao: ?string
│   ├── serie_mdfe_producao: ?string
│   ├── serie_mdfe_homologacao: ?string
│   ├── proximo_numero_nfcom_producao: ?string
│   ├── proximo_numero_nfcom_homologacao: ?string
│   ├── serie_nfcom_producao: ?string
│   └── serie_nfcom_homologacao: ?string
│
├── Certificado digital
│   ├── arquivo_certificado_base64: ?string
│   └── senha_certificado: ?string
│
├── Logo
│   ├── arquivo_logo_base64: ?string
│   └── delete_logo: ?bool
│
├── Responsável
│   ├── nome_responsavel: ?string
│   ├── cpf_responsavel: ?string
│   ├── login_responsavel: ?string
│   ├── senha_responsavel: ?string
│   └── senha_responsavel_preenchida: ?bool
│
├── Contabilidade
│   ├── cpf_cnpj_contabilidade: ?string
│   ├── data_inicio_recebimento_nfe: ?string
│   └── data_inicio_recebimento_cte: ?string
│
├── SMTP
│   ├── smtp_endereco: ?string
│   ├── smtp_dominio: ?string
│   ├── smtp_porta: ?int
│   ├── smtp_autenticacao: ?SmtpAutenticacao
│   ├── smtp_login: ?string
│   ├── smtp_senha: ?string
│   ├── smtp_remetente: ?string
│   ├── smtp_responder_para: ?string
│   ├── smtp_modo_verificacao_openssl: ?SmtpVerificacaoOpenssl
│   ├── smtp_habilita_starttls: ?bool
│   ├── smtp_ssl: ?bool
│   └── smtp_tls: ?bool
│
└── Processamento síncrono
    ├── nfe_sincrono: ?bool
    ├── nfe_sincrono_homologacao: ?bool
    ├── mdfe_sincrono: ?bool
    └── mdfe_sincrono_homologacao: ?bool
```

**Validation:** None in constructor. All fields are optional and the API validates server-side.

### Companies::create() and update() Change

```php
// Before:
public function create(array $parameters = []): FocusResponse
public function update(string $id, array $parameters = []): FocusResponse

// After:
public function create(EmpresaRequest|array $parameters = []): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = EmpresaRequest::fromArray($parameters);
    }
    // ...
}

public function update(string $id, EmpresaRequest|array $parameters = []): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = EmpresaRequest::fromArray($parameters);
    }
    // ...
}
```

### File Structure

```
src/Companies/DTO/
├── EmpresaRequest.php
└── Enums/
    ├── RegimeTributario.php
    ├── OrientacaoDanfe.php
    ├── SmtpAutenticacao.php
    └── SmtpVerificacaoOpenssl.php
```

---

## Hooks Domain

### WebhookRequest DTO

Readonly class with 2 required and 3 optional fields.

```
WebhookRequest
├── event: WebhookEvent (required)
├── url: string (required)
├── cnpj: ?string
├── cpf: ?string
├── authorization: ?string
└── authorization_header: ?string
```

### WebhookEvent Enum

```php
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

**Validation:** None in constructor beyond PHP type system.

### Hooks::create() Change

```php
// Before:
public function create(array $parameters = []): FocusResponse

// After:
public function create(WebhookRequest|array $parameters = []): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = WebhookRequest::fromArray($parameters);
    }
    return $this->http->post('/hooks', $parameters->toArray());
}
```

### File Structure

```
src/Hooks/DTO/
├── WebhookRequest.php
└── Enums/
    └── WebhookEvent.php
```

---

## NFSe Domain (cancel/email)

### NfseCancelRequest DTO

```
NfseCancelRequest
└── justificativa: string (required, 15-255 chars)
```

Constructor validates string length inline (no trait):
```php
if (mb_strlen($this->justificativa) < 15 || mb_strlen($this->justificativa) > 255) {
    throw new InvalidDtoException('justificativa must be between 15 and 255 characters.');
}
```

### NfseEmailRequest DTO

```
NfseEmailRequest
└── emails: array<int, string> (required, max 10 items)
```

Constructor validates array count inline:
```php
if (count($this->emails) === 0 || count($this->emails) > 10) {
    throw new InvalidDtoException('emails must contain between 1 and 10 addresses.');
}
```

### Nfse::cancel() and email() Change

```php
// Before:
public function cancel(string $reference, array $parameters = []): FocusResponse
public function email(string $reference, array $parameters = []): FocusResponse

// After:
public function cancel(string $reference, NfseCancelRequest|array $parameters): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseCancelRequest::fromArray($parameters);
    }
    return $this->http->delete('/nfse/'.urlencode($reference), $parameters->toArray());
}

public function email(string $reference, NfseEmailRequest|array $parameters): FocusResponse
{
    if (is_array($parameters)) {
        $parameters = NfseEmailRequest::fromArray($parameters);
    }
    return $this->http->post('/nfse/'.urlencode($reference).'/email', $parameters->toArray());
}
```

### New Files

```
src/Nfse/DTO/
├── NfseCancelRequest.php  (new)
└── NfseEmailRequest.php   (new)
```

---

## Testing Strategy

- Each DTO gets a test file mirroring the source structure
- Test `fromArray()` happy path and missing required keys
- Test `toArray()` output matches expected API payload, null omission works
- Test constructor validation where present (NfseCancelRequest, NfseEmailRequest)
- Test enum values match API spec
- Test domain methods accept both DTO and array
- Quality gates: 100% coverage, mutation, type coverage, rector, phpstan, psalm, pint

## Quality Constraints

All existing quality gates must remain at 100%:
- pest --coverage --min=100
- pest --mutate --min=100
- pest --type-coverage --min=100
- rector, phpstan, psalm, pint
