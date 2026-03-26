# Security Audit Design — Larafocus

**Date:** 2026-03-26
**Goal:** Proactive security audit using AI-driven adversarial analysis, followed by automated security pipeline and vulnerability fixes.

---

## Context

Larafocus is a Laravel package (API client) that integrates with the FocusNFe API for Brazilian fiscal document processing. The package already has strong fundamentals: Psalm taint analysis, PHPStan level 9, immutable DTOs with validation, 100% test coverage + mutation testing.

The attack surface is relatively small (HTTP client library — no incoming requests, no database, no shell execution), but real risks exist in: token handling, URL construction, input validation gaps, and response parsing.

This audit is preventive — find and fix vulnerabilities before external actors do.

---

## Phase A: AI Security Audit

Systematic adversarial analysis, component by component.

### Scope

| Component | Attack Vectors |
|---|---|
| `Http.php` | Token leaking in logs/exceptions, Basic Auth over plain HTTP, timeout abuse |
| `FocusManager.php` | `resolveFileUrl()` path traversal, environment switching manipulation, config injection |
| `FocusResponse.php` | Malformed JSON parsing, response injection, large payload handling |
| DTOs (`NfseRequest`, `EmpresaRequest`, etc.) | Boundary values, type confusion, enum bypass, regex escape, null byte injection |
| `NfseEmailRequest` | Email injection, array overflow |
| `WebhookRequest` | URL scheme injection (`javascript:`, `file://`, etc.), SSRF via webhook URL |
| `EmpresaRequest` | Base64 bomb (`arquivo_certificado`), credential fields (SMTP password exposure) |
| Config (`larafocus.php`) | Default values, env override, endpoint manipulation |

### Deliverable

Report with each finding classified as CRITICAL/HIGH/MEDIUM/LOW with real exploitability assessment. Separate genuine vulnerabilities from false positives.

---

## Phase B: Automated Security Pipeline

Transform Phase A findings into automated tests + harden existing tools.

### Layers

| Layer | Purpose | Tool |
|---|---|---|
| Security Test Suite | Dedicated tests with malicious inputs for each vector from Phase A | Pest (`tests/Unit/Security/`) |
| Fuzz Testing on DTOs | Generate random/malicious inputs, ensure DTOs never pass dangerous data | Pest with massive data providers |
| Boundary Testing | Test validation logic with values just above/below limits, exotic unicode, null bytes (no huge allocations in test suite) | Pest |
| Psalm Taint Hardening | Verify all user→API data paths are covered by taint analysis | Psalm config + annotations |
| PHPStan Custom Rules | Custom rules for project-specific dangerous patterns (e.g., string concatenation in URLs) | PHPStan (if needed) |

### CI Integration

All new tests run within the existing CI pipeline — they are part of `pest --coverage` and Psalm taint analysis is already configured. Zero additional infrastructure overhead.

### Deliverable

Security test suite that runs automatically on every commit/PR.

---

## Phase C: Fixes + Hardening

Fix each vulnerability using TDD: write security test (RED) then implement fix (GREEN).

### Known Findings from Exploration

| Severity | Finding | Proposed Fix | Impact |
|---|---|---|---|
| **HIGH** | `resolveFileUrl()` no path validation | Validate path has no `..`, malicious schemes | Prevents URL manipulation |
| **HIGH** | `WebhookRequest` accepts any URL | Validate scheme (`https://` required; `http://` allowed in sandbox only), validate format | Prevents SSRF/injection |
| **MEDIUM** | Emails not individually validated in `NfseEmailRequest` | `filter_var(FILTER_VALIDATE_EMAIL)` per email | Prevents email injection |
| **MEDIUM** | Base64 no size limit in `EmpresaRequest` | Limit certificate to 10MB, logo to 2MB (configurable) | Prevents memory exhaustion |
| **LOW** | No `.env.example` for developer onboarding | Add `.env.example` with placeholder values (`.env` is already gitignored) | Prevents accidental credential exposure by new contributors |

### Execution Order

CRITICAL first, then HIGH, MEDIUM, LOW.

### Constraint

Every fix must maintain 100% coverage, 100% mutation score, 100% type coverage, and pass all quality checks defined in CLAUDE.md.

### Deliverable

Corrected codebase where every vulnerability is covered by an automated test that prevents regression.

---

## Execution Order

```
Phase A (Audit) → Phase B (Tests) → Phase C (Fixes)
```

Phase A discovers. Phase B writes tests. Phase C implements fixes. In practice, B and C interleave per finding following TDD: write test (RED) → implement fix (GREEN) → next finding.

---

## Success Criteria

1. All genuine vulnerabilities identified and classified
2. Each vulnerability covered by at least one automated security test
3. All fixes implemented and passing full quality suite
4. No regressions in existing functionality
5. 100% coverage, 100% mutation score, 100% type coverage maintained