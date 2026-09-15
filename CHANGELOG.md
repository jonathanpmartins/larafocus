# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Removed
- **Breaking:** Laravel 11 support. Every `laravel/framework` 11.x release carries unpatched security advisories, so Composer refuses to install it by default and the CI matrix could no longer exercise it. `illuminate/http` and `illuminate/validation` now require `^12.0|^13.0`.

### Changed
- **Breaking:** `EmpresaRequest::$inscricao_estadual`, `$inscricao_municipal`, `$numero` and `$cep` are now `?string` instead of `?int`. Focus stores these fields verbatim, so the `int` cast dropped leading zeros (CEP `01412000` became `1412000`, IM `00054648` became `54648`), truncated at hyphens (IM `7469103-1` became `7469103`) and rejected non-numeric street numbers (`SN`, `APT 310`). Arrays passed to `fromArray()`, `create()` or `update()` may still carry integers; they are converted to strings. Code that builds the DTO through the constructor must now pass strings, and reading these properties yields a string.

## [0.1.0] - 2026-03-11

### Added
- NFSe creation, cancellation, email sending, and querying
- Companies management (create, update, list, get)
- Webhook registration, listing, and deletion
- City and service search endpoints
- Typed DTOs for all API requests and responses
- `FocusResponse` DTO for structured API responses with error handling
- `Environment` and `ContentType` enums
- Immutable `FocusManager` with fluent configuration (`config()`, `using()`)
- HTTP macros: `Http::focus()`, `Http::focusXml()`, `Http::focusPdf()`
- Sandbox environment as default to prevent accidental production calls
- Basic Auth per FocusNFe API specification
- Publishable configuration file
- OpenAPI specs for NFSe, Companies, Webhooks, and Municipalities
- 100% code coverage, mutation testing, and type coverage
- PHPStan level 9, Psalm taint analysis, Rector, and Pint integration
