# Stories: F-002 — Configuration Management

> Implement the Configuration aggregate that loads, validates, and exposes server configuration from YAML files with environment variable substitution, default value merging, and section-based read-only access.

---

## Story Map

| # | Story | Size | Dependencies | Status |
|---|---|---|---|---|
| 1 | [US-002.01 — Load YAML Configuration at Startup](US-002.01.md) | S | F-001 (US-001.07) | Draft |
| 2 | [US-002.02 — Merge Default Configuration Values](US-002.02.md) | S | US-002.01 | Draft |
| 3 | [US-002.03 — Substitute Environment Variables in Configuration](US-002.03.md) | S | US-002.01 | Draft |
| 4 | [US-002.04 — Validate Configuration at Startup](US-002.04.md) | M | US-002.01, US-002.02, US-002.03 | Draft |
| 5 | [US-002.05 — Immutable Configuration After Construction](US-002.05.md) | S | US-002.01 | Draft |
| 6 | [US-002.06 — Read-Only Section Access](US-002.06.md) | S | US-002.05 | Draft |
| 7 | [US-002.07 — Environment-Specific Configuration Precedence](US-002.07.md) | M | US-002.01, US-002.02, US-002.03 | Draft |
| 8 | [US-002.08 — Sample and Default Configuration Files](US-002.08.md) | S | US-002.04 | Draft |
| 9 | [US-002.09 — Quality Gate and Regression Verification](US-002.09.md) | S | US-002.01–08 | Draft |

**Total stories:** 9  
**Total estimated effort:** ~6–7 days (one developer)

---

## Suggested Implementation Order

1. **US-002.01 — Load YAML Configuration at Startup** — Core loading mechanism; unblocks all other stories.
2. **US-002.02 — Merge Default Configuration Values** — Defaults must be applied before validation; prerequisite for US-002.04.
3. **US-002.03 — Substitute Environment Variables** — Env var resolution must happen before validation; prerequisite for US-002.04.
4. **US-002.05 — Immutable Configuration After Construction** — Establishes the immutability guarantee early; unblocks US-002.06.
5. **US-002.06 — Read-Only Section Access** — Consuming contexts need section access; this is the primary read API.
6. **US-002.04 — Validate Configuration at Startup** — Requires defaults + env vars to be merged first; validates the complete configuration.
7. **US-002.07 — Environment-Specific Configuration Precedence** — Layered config loading; builds on top of loading, defaults, and env var stories.
8. **US-002.08 — Sample and Default Configuration Files** — Reference files; requires validation rules to be finalized.
9. **US-002.09 — Quality Gate and Regression Verification** — Final pass; runs after all code is in place.

---

## Dependency Graph

```
F-001/US-001.07 (Configuration Contract — ConfigurationInterface)
    │
    └── US-002.01 (YAML Loading)
            ├── US-002.02 (Default Merging)
            │       └── US-002.04 (Validation) ← also depends on US-002.03
            ├── US-002.03 (Env Var Substitution)
            │       └── US-002.04 (Validation)
            ├── US-002.05 (Immutability)
            │       └── US-002.06 (Section Access)
            └── US-002.07 (Config Precedence) ← depends on US-002.02, US-002.03

US-002.08 (Sample & Default Files) ← depends on US-002.04

US-002.09 (Quality Gate) — Depends on ALL above
```

---

## Coverage of Feature Acceptance Criteria

| Feature Acceptance Criterion | Covered By |
|---|---|
| YAML configuration file loaded successfully at startup | US-002.01 |
| Environment variable substitution (`${DB_PASSWORD}`) | US-002.03 |
| Default values merged for optional fields | US-002.02 |
| Required fields validated (fail-fast at startup) | US-002.04 |
| Validation reports ALL errors (not fail-on-first) | US-002.04 |
| Configuration is immutable after construction | US-002.05 |
| Read-only section access via `getSection()` | US-002.06 |
| Environment-specific config file precedence | US-002.07 |
| Clear, descriptive error messages for missing fields | US-002.04 |
| `EnvironmentVariableNotFoundException` for undefined env vars | US-002.03 |
| `ConfigurationLoadException` for missing/unreadable YAML | US-002.01 |
| `ConfigurationValidationException` with aggregated errors | US-002.04 |
| Sample configuration file `config/config.yaml.example` | US-002.08 |
| Default configuration file `config/default.yaml` | US-002.08 |
| PHPStan Level 8 passes | US-002.09 |
| PSR-12 compliant | US-002.09 |

---

## Roles Involved

| Role | Stories |
|---|---|
| Repository administrator | US-002.01, US-002.02, US-002.08 |
| DevOps engineer | US-002.03, US-002.07, US-002.08 |
| Server application (system) | US-002.04, US-002.05 |
| Core developer | US-002.06, US-002.09 |

---

## Technical Context

### Configuration Sections

| Section | Consumers | Required Fields |
|---------|-----------|-----------------|
| `repository` | Repository Context, Protocol Context | name, baseUrl, adminEmail, deletedRecord, granularity |
| `database` | Repository Context | driver, host, port, name, user, password |
| `mapping` | Repository Context | record_table, identifier_field, datestamp_field |
| `metadata_formats` | Metadata Serialization Context | prefix, plugin, enabled (at least one) |
| `resumption` | Flow Control Context | page_size, token_lifetime |
| `cache` | All Contexts | driver |
| `security` | Access Control Context | force_https |
| `logging` | Observability Context | level, path |
| `monitoring` | Observability Context | metrics_enabled (optional) |

### Exception Hierarchy

| Exception | Thrown By | Description |
|---|---|---|
| `ConfigurationLoadException` | US-002.01 | YAML file not found or unreadable |
| `EnvironmentVariableNotFoundException` | US-002.03 | Referenced env var not set |
| `ConfigurationValidationException` | US-002.04 | Validation failures (aggregated) |

---

*Story map created on February 18, 2026*
