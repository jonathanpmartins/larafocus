# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
