# Feature F-002: Configuration Management

**Feature ID:** F-002  
**Priority:** MVP (MUST HAVE)  
**Phase:** 1 — Configuration Context  
**Bounded Context:** Configuration  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the Configuration aggregate that loads, validates, and exposes server configuration from YAML files. The configuration system supports environment variable substitution, default value merging, and section-based read-only access. All other bounded contexts depend on this feature for their settings.

## User Stories

**US-002.1:** As a **repository administrator**, I want to configure the server using a human-readable YAML file so that I can set up the server without writing code.

**US-002.2:** As a **DevOps engineer**, I want to use environment variables for sensitive values (database passwords, API keys) so that secrets are not stored in configuration files.

**US-002.3:** As the **server application**, I want configuration to be validated at startup with clear error messages so that misconfigurations are caught before serving requests.

**US-002.4:** As a **developer**, I want a reference configuration file with all supported keys and defaults so that I know what options are available.

## Acceptance Criteria

- [ ] YAML configuration file loaded successfully at startup
- [ ] Environment variable substitution works (`${DB_PASSWORD}` → `getenv('DB_PASSWORD')`)
- [ ] Default values merged for optional fields not specified by user
- [ ] Required fields validated (fail-fast at startup):
  - `repository`: name, baseUrl, adminEmail, deletedRecord, granularity
  - `database`: driver, host, port, name, user, password
  - `mapping`: record_table, identifier_field, datestamp_field
  - `metadata_formats`: at least one format with prefix, plugin, enabled
  - `resumption`: page_size, token_lifetime
  - `cache`: driver
  - `security`: force_https
  - `logging`: level, path
- [ ] Validation reports ALL errors (not fail-on-first) in `ConfigurationValidationException`
- [ ] Configuration is immutable after construction (no setters, modification throws exception)
- [ ] Read-only section access via `getSection(string $name): array`
- [ ] Environment-specific config file precedence: `default.yaml < environment.yaml < env vars`
- [ ] Clear, descriptive error messages for missing required fields
- [ ] `EnvironmentVariableNotFoundException` thrown for undefined env vars
- [ ] Sample configuration file `config/config.yaml.example` provided
- [ ] Default configuration file `config/default.yaml` with all defaults
- [ ] PHPStan Level 8 passes
- [ ] PSR-12 compliant

## Technical Design

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

### Exceptions

- `ConfigurationLoadException` — YAML file not found or unreadable
- `ConfigurationValidationException` — validation failures (aggregated)
- `EnvironmentVariableNotFoundException` — referenced env var not set

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (namespace structure, `ConfigurationInterface`)

### Blocks
- **F-003** Repository Identity (reads `repository` section)
- **F-006** Database Schema Mapping (reads `mapping` section)
- **F-007** Database Adapters (reads `database` section)
- **F-017** Metadata Format Plugin System (reads `metadata_formats` section)
- **F-020** Resumption Tokens & Pagination (reads `resumption` section)
- **F-024** Response Caching (reads `cache` section)
- **F-025** HTTPS Enforcement (reads `security` section)
- **F-027** Authentication System (reads `security` section)
- **F-028** Rate Limiting (reads `security` section)
- **F-031** Structured Logging (reads `logging` section)
- All features that read configuration (transitively)

## Files

```
src/Configuration/Aggregate/Configuration.php
src/Configuration/Aggregate/ConfigurationSection.php
src/Configuration/Exception/ConfigurationLoadException.php
src/Configuration/Exception/ConfigurationValidationException.php
src/Configuration/Exception/EnvironmentVariableNotFoundException.php
config/config.yaml.example
config/default.yaml
tests/Configuration/Aggregate/ConfigurationTest.php
```

## Testing Requirements

- [ ] Loads valid YAML without errors
- [ ] Merges default values for optional fields
- [ ] Substitutes environment variables correctly
- [ ] Throws `ConfigurationValidationException` with ALL errors listed
- [ ] Is immutable after construction
- [ ] Returns correct section data via `getSection()`
- [ ] Throws `ConfigurationLoadException` for missing/unreadable YAML file
- [ ] Throws `EnvironmentVariableNotFoundException` for undefined env vars
- [ ] Edge cases: empty YAML, YAML with only comments, nested env vars

## Notes

- Configuration is the first aggregate to implement after the foundation phase.
- All other contexts should depend on `ConfigurationInterface`, not the concrete class.
