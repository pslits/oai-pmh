# Stories: F-001 — Project Foundation & Shared Contracts

> Establish the project skeleton, namespace hierarchy, and shared PHP interfaces (contracts) that form the integration points between all seven bounded contexts.

---

## Story Map

| # | Story | Size | Dependencies | Status |
|---|---|---|---|---|
| 1 | [US-001.01 — Project Namespace and Directory Scaffolding](US-001.01.md) | S | None | Draft |
| 2 | [US-001.02 — Repository Data Access Contracts](US-001.02.md) | S | US-001.01 | Draft |
| 3 | [US-001.03 — Metadata Format Plugin Contract](US-001.03.md) | S | US-001.01 | Draft |
| 4 | [US-001.04 — Authentication Provider Contract](US-001.04.md) | S | US-001.01 | Draft |
| 5 | [US-001.05 — Infrastructure Storage Contracts](US-001.05.md) | S | US-001.01 | Draft |
| 6 | [US-001.06 — Observability Contracts](US-001.06.md) | S | US-001.01 | Draft |
| 7 | [US-001.07 — Configuration Contract](US-001.07.md) | S | US-001.01 | Draft |
| 8 | [US-001.08 — Server Entry Point and ADR Foundation](US-001.08.md) | XS | None | Draft |
| 9 | [US-001.09 — Quality Gate and Regression Verification](US-001.09.md) | S | US-001.01–08 | Draft |

**Total stories:** 9  
**Total estimated effort:** ~5 days (one developer)

---

## Suggested Implementation Order

1. **US-001.01 — Namespace & Directory Scaffolding** — Unblocks all contract stories; foundational.
2. **US-001.08 — Entry Point & ADR Foundation** — Independent; can run in parallel with US-001.01.
3. **US-001.02 — Repository Data Access Contracts** — Core data model contracts; most other contexts interact with records.
4. **US-001.07 — Configuration Contract** — All contexts depend on configuration; define this early.
5. **US-001.03 — Metadata Format Plugin Contract** — References `RecordInterface` from US-001.02; enables plugin ecosystem.
6. **US-001.06 — Observability Contracts** — Cross-cutting; used by all contexts for logging and metrics.
7. **US-001.05 — Infrastructure Storage Contracts** — Token storage and caching; needed by Flow Control and all caching consumers.
8. **US-001.04 — Authentication Provider Contract** — Security concern; can be implemented later since MVP uses public access.
9. **US-001.09 — Quality Gate Verification** — Final pass; runs after all interfaces are in place.

---

## Dependency Graph

```
US-001.01 (Namespace Scaffolding)
    ├── US-001.02 (Repository Contracts)
    │       └── US-001.03 (Metadata Plugin Contract) [loose: uses RecordInterface type]
    ├── US-001.04 (Auth Provider Contract)
    ├── US-001.05 (Storage Contracts)
    ├── US-001.06 (Observability Contracts)
    └── US-001.07 (Configuration Contract)

US-001.08 (Entry Point & ADR) — Independent

US-001.09 (Quality Gate) — Depends on ALL above
```

---

## Coverage of Feature Acceptance Criteria

| Feature Acceptance Criterion | Covered By |
|---|---|
| `composer.json` registers all 8 namespaces | US-001.01 |
| Skeleton directory tree under `src/` | US-001.01 |
| `RecordInterface` | US-001.02 |
| `StorageAdapterInterface` | US-001.02 |
| `MetadataFormatPluginInterface` | US-001.03 |
| `AuthenticationProviderInterface` | US-001.04 |
| `TokenStorageInterface` | US-001.05 |
| `CacheInterface` | US-001.05 |
| `LoggerInterface` (PSR-3 compatible) | US-001.06 |
| `MetricsCollectorInterface` | US-001.06 |
| `ConfigurationInterface` | US-001.07 |
| `bin/oaipmh-server` placeholder | US-001.08 |
| `docs/adr/` directory | US-001.08 |
| All interfaces pass PHPStan Level 8 | US-001.09 |
| Existing tests remain green | US-001.09 |

---

## Roles Involved

| Role | Stories |
|---|---|
| Core developer | US-001.01, US-001.07, US-001.09 |
| Database adapter developer | US-001.02 |
| Plugin developer | US-001.03 |
| Security developer | US-001.04 |
| Infrastructure developer | US-001.05 |
| Platform engineer / DevOps | US-001.06 |
| Contributor | US-001.08 |

---

*Story map created on February 18, 2026*
