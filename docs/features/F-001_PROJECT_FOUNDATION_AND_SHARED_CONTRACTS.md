# Feature F-001: Project Foundation & Shared Contracts

**Feature ID:** F-001  
**Priority:** MVP (MUST HAVE)  
**Phase:** 0 — Foundation & Cross-Cutting Infrastructure  
**Bounded Context:** Cross-cutting (all contexts)  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Establish the project skeleton, namespace hierarchy, and shared PHP interfaces (contracts) that form the integration points between all seven bounded contexts. This is the prerequisite for all other features — no context-specific code can be written until the namespace structure and cross-context interfaces are defined.

## User Stories

**US-001.1:** As a **core developer**, I want a well-defined namespace structure for all seven bounded contexts so that I can begin implementing each context in isolation without namespace conflicts.

**US-001.2:** As a **plugin developer**, I want clearly defined PHP interfaces (contracts) so that I can implement adapters and plugins against stable contracts.

**US-001.3:** As a **contributor**, I want a consistent directory layout so that I can navigate the codebase intuitively and understand where new code belongs.

## Acceptance Criteria

- [ ] `composer.json` autoload section registers all 8 new namespaces (`OaiPmh\Protocol\`, `OaiPmh\Repository\`, `OaiPmh\MetadataSerialization\`, `OaiPmh\AccessControl\`, `OaiPmh\FlowControl\`, `OaiPmh\Observability\`, `OaiPmh\Configuration\`, `OaiPmh\Infrastructure\`)
- [ ] Skeleton directory tree created under `src/` for all contexts with placeholder files
- [ ] 9 PHP interface files defined with full docblocks:
  - `RecordInterface` (`OaiPmh\Repository\Contract`)
  - `MetadataFormatPluginInterface` (`OaiPmh\MetadataSerialization\Contract`)
  - `AuthenticationProviderInterface` (`OaiPmh\AccessControl\Contract`)
  - `StorageAdapterInterface` (`OaiPmh\Repository\Contract`)
  - `TokenStorageInterface` (`OaiPmh\FlowControl\Contract`)
  - `CacheInterface` (`OaiPmh\Infrastructure\Contract`)
  - `LoggerInterface` (`OaiPmh\Observability\Contract`) — PSR-3 compatible
  - `ConfigurationInterface` (`OaiPmh\Configuration\Contract`)
  - `MetricsCollectorInterface` (`OaiPmh\Observability\Contract`)
- [ ] All interfaces pass PHPStan Level 8
- [ ] `bin/oaipmh-server` entry script placeholder created
- [ ] Existing Value Object tests remain green (no regressions)
- [ ] Architecture Decision Records (ADR) directory created at `docs/adr/`

## Technical Design

### Namespace Hierarchy

```
src/
├── Domain/ValueObject/          ← Already complete (23 classes)
├── Domain/Aggregate/            ← In progress
├── Protocol/
│   ├── Aggregate/
│   ├── Contract/
│   ├── Service/
│   └── ValueObject/
├── Repository/
│   ├── Aggregate/
│   ├── Contract/
│   ├── Entity/
│   └── Service/
├── MetadataSerialization/
│   ├── Aggregate/
│   ├── Contract/
│   └── Plugin/
├── AccessControl/
│   ├── Aggregate/
│   ├── Contract/
│   └── Middleware/
├── FlowControl/
│   ├── Aggregate/
│   ├── Contract/
│   └── Service/
├── Observability/
│   ├── Aggregate/
│   └── Contract/
├── Configuration/
│   ├── Aggregate/
│   ├── Contract/
│   └── Exception/
└── Infrastructure/
    ├── Cache/
    ├── Database/
    ├── Http/
    ├── Logging/
    └── FlowControl/
```

### Interface Design Principle

Interfaces define the **only** cross-context coupling points. Each context communicates with others exclusively through these contracts, following the Dependency Inversion Principle.

## Dependencies

### Blocked By
- None — this is the foundational feature.

### Blocks
- **F-002** Configuration Management
- **F-003** Repository Identity
- **F-004** Record & RecordHeader Entities
- **F-005** Set Aggregate & Hierarchy
- **F-006** Database Schema Mapping
- **F-007** Database Adapters
- **F-008** OAI-PMH Request Parsing
- All other features (transitively)

## Files

```
composer.json                              (modified — autoload section)
bin/oaipmh-server                          (placeholder)
src/Protocol/Contract/                     (new directory)
src/Repository/Contract/RecordInterface.php
src/Repository/Contract/StorageAdapterInterface.php
src/MetadataSerialization/Contract/MetadataFormatPluginInterface.php
src/AccessControl/Contract/AuthenticationProviderInterface.php
src/FlowControl/Contract/TokenStorageInterface.php
src/Infrastructure/Contract/CacheInterface.php
src/Observability/Contract/LoggerInterface.php
src/Observability/Contract/MetricsCollectorInterface.php
src/Configuration/Contract/ConfigurationInterface.php
docs/adr/                                 (new directory)
```

## Testing Requirements

- [ ] All interfaces parse without errors (`php -l`)
- [ ] PHPStan Level 8 passes on all new files
- [ ] PHP_CodeSniffer PSR-12 compliance
- [ ] Existing test suite passes without regressions

## Notes

- New Value Objects identified during audit (Phase 0.3): `ResumptionTokenValue`, `PageSize`, `CursorPosition`, `HttpStatusCode`, `IpAddress` — to be implemented following existing VO patterns.
- ADR-001 (Token format) and ADR-006 (XML generation) should be written first as they affect Phases 3–5 significantly.
