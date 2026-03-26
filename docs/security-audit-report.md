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
