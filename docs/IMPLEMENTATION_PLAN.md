# OAI-PMH Repository Server — Software Implementation Plan

**Document Date:** February 18, 2026
**Based on:** [SOFTWARE_DESIGN.md](event-storming/SOFTWARE_DESIGN.md), [PROCESS_MODELING.md](event-storming/PROCESS_MODELING.md), [BIG_PICTURE.md](event-storming/BIG_PICTURE.md)
**Requirements Source:** [REPOSITORY_SERVER_REQUIREMENTS.md](REPOSITORY_SERVER_REQUIREMENTS.md)
**Status:** Ready for Implementation

---

## Executive Summary

This plan translates the Software Design Event Storming into a sequenced, phase-based implementation roadmap. The OAI-PMH Repository Server is composed of **7 Bounded Contexts** containing **20 Aggregates**. Implementation proceeds in dependency order — foundational contexts first, protocol and verb handling second, supporting contexts third — so that each phase produces working, testable software.

The existing `oai-pmh` library already provides a complete set of **Value Objects** (23 classes, fully tested). This plan builds on that foundation without re-implementing what already exists.

---

## Current State

### ✅ Already Implemented

| Layer | Components | Status |
|-------|-----------|--------|
| Value Objects | `BaseURL`, `RepositoryName`, `Email`, `EmailCollection`, `DeletedRecord`, `Granularity`, `ProtocolVersion`, `UTCdatetime`, `Description`, `DescriptionCollection`, `DescriptionFormat`, `MetadataFormat`, `MetadataPrefix`, `MetadataNamespace`, `MetadataNamespaceCollection`, `MetadataRootTag`, `NamespacePrefix`, `AnyUri`, `ContainerFormat`, `OaiVerb`, `RecordIdentifier`, `SetSpec` | ✅ Complete, tested, PHPStan Level 8 |
| Aggregates | `OaiRequest` (partial), `RepositoryIdentity` (partial) | 🔶 In progress |
| Documentation | Value Object analyses, Event Storming trilogy | ✅ Complete |

### 🔲 To Be Implemented

All seven bounded contexts' aggregates, interfaces, services, infrastructure adapters, middleware pipeline, and HTTP entry point.

---

## Architecture Overview

```
src/
├── Domain/
│   ├── ValueObject/          ← ✅ Complete
│   ├── Aggregate/            ← 🔶 In progress
│   ├── Entity/
│   └── Exception/
├── Protocol/                 ← Context 1
│   ├── Aggregate/
│   ├── Contract/             ← Interfaces exposed to other contexts
│   └── Service/
├── Repository/               ← Context 2
│   ├── Aggregate/
│   ├── Contract/
│   ├── Entity/
│   └── Service/
├── MetadataSerialization/    ← Context 3
│   ├── Aggregate/
│   ├── Contract/
│   └── Plugin/
├── AccessControl/            ← Context 4
│   ├── Aggregate/
│   ├── Contract/
│   └── Middleware/
├── FlowControl/              ← Context 5
│   ├── Aggregate/
│   └── Contract/
├── Observability/            ← Context 6
│   ├── Aggregate/
│   └── Contract/
├── Configuration/            ← Context 7
│   ├── Aggregate/
│   └── Contract/
└── Infrastructure/           ← Adapters for external systems
    ├── Database/
    ├── Cache/
    ├── Http/
    └── Logging/
```

---

## Implementation Phases

---

## Phase 0: Foundation & Cross-Cutting Infrastructure

**Goal:** Establish the project skeleton, shared interfaces, and infrastructure adapters that all contexts depend on.
**Duration:** ~1 week
**Dependency:** None (this is the prerequisite for all phases)

---

### 0.1 — Namespace & Directory Structure

Create the PHP namespace hierarchy for all seven contexts. No implementation yet — just the directories with placeholder files.

**Namespaces to register in `composer.json`:**

```
OaiPmh\Protocol\
OaiPmh\Repository\
OaiPmh\MetadataSerialization\
OaiPmh\AccessControl\
OaiPmh\FlowControl\
OaiPmh\Observability\
OaiPmh\Configuration\
OaiPmh\Infrastructure\
```

**Deliverables:**
- Updated `composer.json` autoload section
- Skeleton directory tree under `src/`
- `bin/oaipmh-server` entry script placeholder

---

### 0.2 — Shared Domain Contracts (Interfaces)

Define the PHP interfaces that form the integration contracts between bounded contexts. These are the only cross-context coupling points.

**Interfaces to define:**

| Interface | Namespace | Purpose |
|-----------|-----------|---------|
| `RecordInterface` | `OaiPmh\Repository\Contract` | Consumed by Metadata Serialization |
| `MetadataFormatPluginInterface` | `OaiPmh\MetadataSerialization\Contract` | Format plugin contract |
| `AuthenticationProviderInterface` | `OaiPmh\AccessControl\Contract` | Auth pluggability |
| `StorageAdapterInterface` | `OaiPmh\Repository\Contract` | Database abstraction |
| `TokenStorageInterface` | `OaiPmh\FlowControl\Contract` | Resumption token persistence |
| `CacheInterface` | `OaiPmh\Infrastructure\Contract` | Cache abstraction |
| `LoggerInterface` | `OaiPmh\Observability\Contract` | Logging (PSR-3 compatible) |
| `ConfigurationInterface` | `OaiPmh\Configuration\Contract` | Config access by section |
| `MetricsCollectorInterface` | `OaiPmh\Observability\Contract` | Metrics recording |

**Deliverables:**
- 9 PHP interface files, fully documented with docblocks
- PHPStan Level 8 passing

---

### 0.3 — Shared Value Objects Audit

Review all existing Value Objects against their usage in the new aggregates. Identify any missing value objects needed for the implementation (e.g., `ResumptionTokenValue`, `CursorPosition`, `PageSize`).

**New Value Objects to implement (if identified):**

- `ResumptionTokenValue` — the opaque token string
- `PageSize` — validated integer (positive, bounded)
- `CursorPosition` — non-negative integer offset
- `HttpStatusCode` — typed wrapper for HTTP status codes
- `IpAddress` — for rate limiting / logging

**Deliverables:**
- Audit report (in code comments or ADR)
- New value objects following the existing pattern (final, immutable, PHPStan Level 8, 100% tested)

---

### 0.4 — Architecture Decision Records (ADRs)

Document the key architectural decisions from the Software Design session before writing code.

**ADRs to write:**

| ADR # | Decision |
|-------|---------|
| ADR-001 | Token format: JWT vs. Redis UUID vs. encoded state |
| ADR-002 | Schema mapping: YAML DSL definition |
| ADR-003 | Database abstraction: Doctrine DBAL vs. PDO direct |
| ADR-004 | Cache backend: Redis as primary, file as fallback |
| ADR-005 | Middleware pipeline: PSR-15 middleware stack |
| ADR-006 | XML generation: DOMDocument vs. XMLWriter vs. SimpleXML |
| ADR-007 | Configuration: Symfony Config component vs. custom YAML loader |
| ADR-008 | PHP version minimum and dependency constraints |

**Location:** `docs/adr/ADR-00{N}-{slug}.md`

---

## Phase 1: Configuration Context

**Goal:** Load, validate, and expose configuration so all other contexts can read their settings.
**Duration:** ~1 week
**Dependency:** Phase 0

---

### 1.1 — `Configuration` Aggregate

```
src/Configuration/Aggregate/Configuration.php
src/Configuration/Aggregate/ConfigurationSection.php
```

**Behaviour:**

- Reads one or more YAML files (default, environment-override)
- Applies environment variable substitution: `${DB_PASSWORD}` → `getenv('DB_PASSWORD')`
- Merges with defaults for unspecified optional keys
- Validates required fields (fail-fast at startup)
- Exposes read-only sections via `getSection(string $name): array`
- Immutable after construction

**Configuration Sections to validate:**

| Section key | Required fields |
|------------|----------------|
| `repository` | `name`, `baseUrl`, `adminEmail`, `deletedRecord`, `granularity` |
| `database` | `driver`, `host`, `port`, `name`, `user`, `password` |
| `mapping` | `record_table`, `identifier_field`, `datestamp_field` |
| `metadata_formats` | At least one format with `prefix`, `plugin`, `enabled` |
| `resumption` | `page_size`, `token_lifetime` |
| `cache` | `driver` |
| `security` | `force_https` |
| `logging` | `level`, `path` |

**Exceptions:**

```
src/Configuration/Exception/ConfigurationLoadException.php
src/Configuration/Exception/ConfigurationValidationException.php
src/Configuration/Exception/EnvironmentVariableNotFoundException.php
```

**Tests:**

```
tests/Configuration/Aggregate/ConfigurationTest.php
```

Test scenarios:
- Loads valid YAML without errors
- Merges default values for optional fields
- Substitutes environment variables correctly
- Throws `ConfigurationValidationException` with ALL errors listed (not fail-on-first)
- Is immutable after construction (attempting to modify throws exception)

---

### 1.2 — YAML Configuration Schema

Define a reference `config.yaml` with all supported keys, defaults, and inline comments:

```
config/config.yaml.example
config/default.yaml           ← defaults shipped with library
```

---

## Phase 2: Repository Context — Identity & Core Entities

**Goal:** Implement `RepositoryIdentity` (for Identify verb) and the `Record` / `Set` core entities.
**Duration:** ~1.5 weeks
**Dependency:** Phase 1

---

### 2.1 — `RepositoryIdentity` Aggregate (complete)

The partial implementation in `src/Domain/Aggregate/RepositoryIdentity.php` must be completed to:

- Load from `Configuration → repository` section
- Compute `earliestDatestamp` by querying the database (lazy, cached)
- Expose all properties required for the Identify XML response
- Be serializable to the Identify response format

```
src/Repository/Aggregate/RepositoryIdentity.php
```

**Properties:**
- `repositoryName: RepositoryName`
- `baseUrl: BaseURL`
- `protocolVersion: ProtocolVersion` (always `"2.0"`)
- `adminEmails: EmailCollection`
- `earliestDatestamp: UTCdatetime`
- `deletedRecord: DeletedRecord`
- `granularity: Granularity`
- `compressions: string[]` (optional: `gzip`, `deflate`)
- `descriptions: DescriptionCollection` (optional)

---

### 2.2 — `Record` Entity

```
src/Repository/Entity/Record.php
src/Repository/Entity/RecordHeader.php
```

**`Record` properties:**
- `identifier: RecordIdentifier`
- `datestamp: UTCdatetime`
- `setSpecs: SetSpec[]`
- `isDeleted: bool`
- `metadata: ?string` (raw XML fragment, null for deleted records)
- `accessLevel: string` (public / restricted)

**`RecordHeader` value:**
- Subset of Record used for ListIdentifiers verb (no metadata)

**Rules:**
- Deleted records carry header only — `metadata` is null
- Records belong to zero or more sets
- `metadata` is lazy-loaded: only populated when serialization is requested

---

### 2.3 — `Set` Aggregate

```
src/Repository/Aggregate/Set.php
src/Repository/Aggregate/SetCollection.php
```

**`Set` properties:**
- `setSpec: SetSpec`
- `setName: string`
- `setDescriptions: DescriptionCollection` (optional)

**`SetCollection`:**
- Typed, iterable collection of `Set` objects
- Implements `Countable`, `IteratorAggregate`

---

### 2.4 — `RecordCollection` Aggregate

```
src/Repository/Aggregate/RecordCollection.php
```

**Properties:**
- `items: Record[]` (the page)
- `totalCount: ?int` (completeListSize — may be null if estimation is disabled)
- `cursor: int` (current offset position)
- `hasMorePages: bool`

---

### 2.5 — `SchemaMapping` Aggregate

**Highest complexity component.** Maps YAML configuration to SQL generation.

```
src/Repository/Aggregate/SchemaMapping.php
src/Repository/Service/QueryBuilder.php
src/Repository/Service/RowMapper.php
```

**`SchemaMapping` responsibilities:**
- Parses `mapping` configuration section
- Validates that all referenced table/column names exist in the database (at startup)
- Provides `buildSelectQuery(array $filters): string` (parameterized SQL)
- Provides `mapRow(array $row): Record`

**Supported mapping modes:**

| Mode | Example |
|------|---------|
| Single table | `record_table: records` |
| View | `record_table: v_oai_records` |
| JOIN | `joins: [{table: sets, on: records.id = set_memberships.record_id}]` |

**SQL generation rules:**
- All queries must use **parameterized placeholders** (PDO or Doctrine DBAL bindings)
- Filters: date range (`datestamp BETWEEN ? AND ?`), set (`JOIN` condition), deletion status
- Pagination: `LIMIT :limit OFFSET :offset`
- Must support both MySQL and PostgreSQL dialects

---

## Phase 3: Protocol Context — Request Parsing & Response Assembly

**Goal:** Implement OAI-PMH request parsing, verb dispatch, and XML response construction.
**Duration:** ~2 weeks
**Dependency:** Phase 2

---

### 3.1 — `OaiRequest` Aggregate (complete)

Complete the partial implementation in `src/Domain/Aggregate/OaiRequest.php`:

```
src/Protocol/Aggregate/OaiRequest.php
```

**Validation rules to implement:**

| Verb | Required | Optional | Exclusive |
|------|---------|---------|---------|
| Identify | — | — | — |
| ListMetadataFormats | — | `identifier` | — |
| ListSets | — | `resumptionToken` | yes |
| ListIdentifiers | `metadataPrefix` | `from`, `until`, `set`, `resumptionToken` | `resumptionToken` |
| ListRecords | `metadataPrefix` | `from`, `until`, `set`, `resumptionToken` | `resumptionToken` |
| GetRecord | `identifier`, `metadataPrefix` | — | — |

**Error detection:**
- `badVerb` — unknown/missing verb
- `badArgument` — missing required, illegal extra, repeated, or mutually exclusive args
- `from`/`until` granularity mismatch with repository granularity
- `from` later than `until`

---

### 3.2 — `OaiResponse` Aggregate

```
src/Protocol/Aggregate/OaiResponse.php
src/Protocol/Service/XmlSerializer.php
```

**Response builder API:**

```php
$response = OaiResponse::createEnvelope(new \DateTimeImmutable(), $request);
$response->setIdentifyContent($repositoryIdentity);
$response->setListRecordsContent($recordCollection, ?$resumptionToken);
$response->setGetRecordContent($record);
$response->addError(OaiErrorCode::BadArgument, 'metadataPrefix is required');
$xml = $response->serialize();  // → well-formed XML string
```

**XML generation strategy (per ADR-006):** Use `DOMDocument` for correctness and namespace handling. `XMLWriter` as alternative for streaming large responses.

**Rules:**
- `responseDate` always in UTC (ISO 8601 with `Z` suffix)
- Request echo includes all legal arguments
- Multiple `<error>` elements allowed in a single response
- No record content when errors are present

---

### 3.3 — `OaiError` Value Object / Enum

```
src/Protocol/ValueObject/OaiErrorCode.php   (PHP 8.1 backed enum)
src/Protocol/Aggregate/OaiError.php
```

**Error codes (PHP backed enum):**

```php
enum OaiErrorCode: string {
    case BadArgument = 'badArgument';
    case BadResumptionToken = 'badResumptionToken';
    case BadVerb = 'badVerb';
    case CannotDisseminateFormat = 'cannotDisseminateFormat';
    case IdDoesNotExist = 'idDoesNotExist';
    case NoRecordsMatch = 'noRecordsMatch';
    case NoMetadataFormats = 'noMetadataFormats';
    case NoSetHierarchy = 'noSetHierarchy';
}
```

---

### 3.4 — Verb Handlers (Services)

One handler per OAI-PMH verb, each implemented as a dedicated service class:

```
src/Protocol/Service/Handler/IdentifyHandler.php
src/Protocol/Service/Handler/ListRecordsHandler.php
src/Protocol/Service/Handler/ListIdentifiersHandler.php
src/Protocol/Service/Handler/GetRecordHandler.php
src/Protocol/Service/Handler/ListMetadataFormatsHandler.php
src/Protocol/Service/Handler/ListSetsHandler.php
```

**Handler contract:**

```php
interface VerbHandlerInterface
{
    public function handle(OaiRequest $request): OaiResponse;
}
```

**Handler responsibilities:**
- Check cache (return cached response if hit)
- Delegate to Repository Context for data retrieval
- Delegate to Metadata Serialization Context for XML fragments
- Check Flow Control Context for resumption tokens
- Assemble `OaiResponse`
- Store response in cache

---

### 3.5 — Request Dispatcher

```
src/Protocol/Service/RequestDispatcher.php
```

Routes a validated `OaiRequest` to the correct `VerbHandlerInterface` implementation.

---

## Phase 4: Metadata Serialization Context

**Goal:** Plugin architecture for transforming records to XML metadata fragments.
**Duration:** ~1 week
**Dependency:** Phase 2 (`Record` entity)

---

### 4.1 — `MetadataFormatPluginInterface`

```
src/MetadataSerialization/Contract/MetadataFormatPluginInterface.php
```

```php
interface MetadataFormatPluginInterface
{
    public function getPrefix(): MetadataPrefix;
    public function getNamespace(): MetadataNamespace;
    public function getSchema(): AnyUri;
    public function supports(RecordInterface $record): bool;
    public function serialize(RecordInterface $record): string;  // XML fragment
}
```

---

### 4.2 — `MetadataFormatRegistry` (the `MetadataFormat` aggregate)

```
src/MetadataSerialization/Aggregate/MetadataFormatRegistry.php
```

**Responsibilities:**
- Holds all registered format plugins (indexed by `MetadataPrefix`)
- Validates that each plugin implements `MetadataFormatPluginInterface`
- Provides `getPlugin(MetadataPrefix $prefix): MetadataFormatPluginInterface`
- Provides `listFormats(RecordInterface $record): MetadataFormatPlugin[]`
- Throws `CannotDisseminateFormatException` for unknown/unsupported prefixes

---

### 4.3 — `MetadataSerializer` Service

```
src/MetadataSerialization/Service/MetadataSerializer.php
```

Orchestrates: fetch plugin → call `serialize()` → return XML fragment with namespace declarations.

---

### 4.4 — Dublin Core Plugin (built-in)

Shipped as the default `oai_dc` format. Required by OAI-PMH 2.0 specification.

```
src/MetadataSerialization/Plugin/DublinCorePlugin.php
```

Maps the 15 Dublin Core elements from configured schema mapping columns.

---

## Phase 5: Flow Control Context

**Goal:** Resumption token creation, storage, decoding, and validation for paginated responses.
**Duration:** ~1 week
**Dependency:** Phase 1 (Configuration), Phase 3 (OaiResponse)

---

### 5.1 — `ResumptionToken` Aggregate

```
src/FlowControl/Aggregate/ResumptionToken.php
```

**Token state:**
- `tokenValue: string` (opaque string)
- `metadataPrefix: MetadataPrefix`
- `from: ?UTCdatetime`
- `until: ?UTCdatetime`
- `set: ?SetSpec`
- `cursor: int`
- `pageSize: int`
- `completeListSize: ?int`
- `expiresAt: \DateTimeImmutable`

**Token format decision (ADR-001):** JWT signed with HMAC-SHA256. Claims contain all query context. Stateless — no storage required for validation (only for early revocation).

**Fallback:** Redis UUID with stored state for repositories that cannot use JWT (e.g., shared hosting without JWT libraries).

---

### 5.2 — `Paginator` Service

```
src/FlowControl/Service/Paginator.php
```

```php
class Paginator
{
    public function calculatePage(int $cursor, int $pageSize): PageBounds;
    public function hasMorePages(int $fetchedCount, int $pageSize): bool;
    public function needsResumptionToken(bool $hasMore): bool;
}
```

---

### 5.3 — Token Storage Adapters

```
src/Infrastructure/FlowControl/JwtTokenAdapter.php
src/Infrastructure/FlowControl/RedisTokenAdapter.php
```

Both implement `TokenStorageInterface`. Chosen at startup via configuration (`resumption.token_storage: jwt|redis`).

---

## Phase 6: Access Control Context

**Goal:** Authentication, rate limiting, HTTPS enforcement, and record-level access filtering.
**Duration:** ~1 week
**Dependency:** Phase 1 (Configuration), Phase 3 (HTTP pipeline)

---

### 6.1 — PSR-15 Middleware Pipeline

```
src/Infrastructure/Http/MiddlewarePipeline.php
```

Processes each request through middleware in order:

1. `HttpsEnforcementMiddleware`
2. `RequestSizeMiddleware`
3. `AuthenticationMiddleware`
4. `RateLimitMiddleware`
5. `OaiRequestMiddleware` (parse + validate verb/args)
6. `VerbDispatchMiddleware` (route to handler)
7. `ResponseLoggerMiddleware` (log + metrics)

---

### 6.2 — `RequestGuard` (middleware)

```
src/AccessControl/Middleware/HttpsEnforcementMiddleware.php
src/AccessControl/Middleware/RequestSizeMiddleware.php
```

- HTTPS: if `security.force_https: true` and request is HTTP → 301 redirect or 400
- Request size: query string > 2KB → return `badArgument` OAI error

---

### 6.3 — `Authenticator` (middleware + aggregate)

```
src/AccessControl/Middleware/AuthenticationMiddleware.php
src/AccessControl/Aggregate/Authenticator.php
src/AccessControl/Contract/AuthenticationProviderInterface.php
```

**MVP:** Public access only (no credentials required). Middleware is present but passes all requests.

**Post-MVP extension points:**
- `BasicAuthProvider`
- `ApiKeyProvider`
- `OAuth2Provider`

---

### 6.4 — `RateLimiter` Aggregate

```
src/AccessControl/Aggregate/RateLimiter.php
src/AccessControl/Middleware/RateLimitMiddleware.php
src/Infrastructure/AccessControl/RedisRateLimitAdapter.php
```

**Algorithm:** Fixed window counter (simpler to implement; token bucket for post-MVP).

**HTTP headers returned:**
- `X-RateLimit-Limit: 1000`
- `X-RateLimit-Remaining: 994`
- `X-RateLimit-Reset: 1708350000`

When exceeded: HTTP 429 with `Retry-After` header.

---

### 6.5 — `AccessControl` (post-query filter)

```
src/AccessControl/Aggregate/AccessControl.php
src/AccessControl/Service/RecordAccessFilter.php
```

Filters `RecordCollection` by removing records the authenticated user cannot see.

- Anonymous user → public records only
- Authenticated user → public + permitted restricted records
- `GetRecord` on unauthorized record → `OaiErrorCode::IdDoesNotExist`

---

## Phase 7: Observability Context

**Goal:** Structured logging, Prometheus metrics, and health check endpoint.
**Duration:** ~0.5 weeks
**Dependency:** Phase 3 (HTTP pipeline established)

---

### 7.1 — `RequestLogger`

```
src/Observability/Aggregate/RequestLogger.php
src/Infrastructure/Observability/MonologLoggerAdapter.php
```

**Log format (JSON):**
```json
{
  "timestamp": "2026-02-18T12:00:00Z",
  "verb": "ListRecords",
  "metadataPrefix": "oai_dc",
  "response_time_ms": 42,
  "records_returned": 100,
  "status": "success",
  "ip": "192.168.1.xxx",
  "request_id": "uuid-v4"
}
```

GDPR IP anonymization: mask last octet (`192.168.1.0`).

---

### 7.2 — `MetricsCollector`

```
src/Observability/Aggregate/MetricsCollector.php
src/Infrastructure/Observability/PrometheusAdapter.php
```

**Exposed metrics:**

| Metric | Type | Labels |
|--------|------|--------|
| `oaipmh_requests_total` | Counter | `verb`, `status` |
| `oaipmh_response_duration_seconds` | Histogram | `verb` |
| `oaipmh_cache_hits_total` | Counter | — |
| `oaipmh_cache_misses_total` | Counter | — |
| `oaipmh_rate_limit_violations_total` | Counter | — |
| `oaipmh_records_served_total` | Counter | `format` |
| `oaipmh_database_query_duration_seconds` | Histogram | — |

Served at `/metrics` in Prometheus text format.

---

### 7.3 — `HealthChecker`

```
src/Observability/Aggregate/HealthChecker.php
```

Checks: database connectivity, cache connectivity, disk space.

Accessible at `/health`. Returns HTTP 200 (healthy), 200 with degraded body (degraded), or 503 (unhealthy).

---

## Phase 8: Infrastructure Adapters

**Goal:** Concrete implementations of all infrastructure interfaces (database, cache, HTTP).
**Duration:** ~1 week
**Dependency:** Phases 1–7 (all contracts must exist first)

---

### 8.1 — Database Adapter

```
src/Infrastructure/Database/DoctrineDbalAdapter.php
src/Infrastructure/Database/PdoAdapter.php
```

Implements `StorageAdapterInterface`. Uses Doctrine DBAL for query building and driver abstraction (MySQL + PostgreSQL). PDO adapter as lightweight alternative for simpler deployments.

---

### 8.2 — Cache Adapter

```
src/Infrastructure/Cache/RedisCacheAdapter.php
src/Infrastructure/Cache/FileCacheAdapter.php
src/Infrastructure/Cache/NullCacheAdapter.php
```

Implements `CacheInterface`. Redis (primary), file-based (development/fallback), null (testing — disables caching).

---

### 8.3 — HTTP Entry Point

```
public/index.php                      ← web root document
src/Infrastructure/Http/Kernel.php    ← bootstraps everything
src/Infrastructure/Http/Router.php    ← routes /oai to pipeline, /health, /metrics
```

**Bootstrap sequence:**

1. Load `composer autoload`
2. Load `Configuration` (from `config.yaml`)
3. Build `MetadataFormatRegistry` (register plugins from config)
4. Build `SchemaMapping` (validate against DB)
5. Wire infrastructure adapters (DB, cache)
6. Build middleware pipeline
7. Handle request → send response

---

### 8.4 — CLI Entry Point (optional, post-MVP)

```
bin/oaipmh                            ← Symfony Console-based CLI
src/Infrastructure/Cli/ValidateConfigCommand.php
src/Infrastructure/Cli/CheckHealthCommand.php
src/Infrastructure/Cli/PurgeCacheCommand.php
src/Infrastructure/Cli/PurgeTokensCommand.php
```

---

## Phase 9: Integration Testing & End-to-End Validation

**Goal:** Verify full request/response cycles against the OAI-PMH 2.0 specification.
**Duration:** ~1 week
**Dependency:** All phases 1–8 complete

---

### 9.1 — Integration Test Database

Provision a test SQLite/MySQL database with fixture data:
- 1,000 records across multiple sets
- Mix of active and deleted records
- Multiple metadata formats

```
tests/Fixtures/database.sql
tests/Fixtures/config-test.yaml
```

---

### 9.2 — OAI-PMH Compliance Tests

Full round-trip request → response tests for every verb:

| Test class | Verb |
|-----------|------|
| `IdentifyIntegrationTest` | Identify |
| `ListRecordsIntegrationTest` | ListRecords (with/without pagination) |
| `ListIdentifiersIntegrationTest` | ListIdentifiers |
| `GetRecordIntegrationTest` | GetRecord (found, deleted, not found) |
| `ListMetadataFormatsIntegrationTest` | ListMetadataFormats |
| `ListSetsIntegrationTest` | ListSets (present, absent) |
| `ResumptionTokenIntegrationTest` | Multi-page pagination cycle |
| `ErrorHandlingIntegrationTest` | All 8 OAI error codes |

**Validation against XSD:** Each response must validate against the official OAI-PMH 2.0 XSD schema.

---

### 9.3 — OAI-PMH Validator Tool Test

Run the official OCLC OAI-PMH validation service / equivalent local validator against the running test server.

---

### 9.4 — Performance Baseline

Establish baseline metrics at phase completion:
- Target: ≥ 1,000 records/second throughput for ListRecords
- Target: ≤ 100 ms response time at p95 for small result sets
- Target: Memory < 128 MB for largest result pages

---

## Phase 10: Hardening, Documentation & Release Preparation

**Goal:** Security audit, performance tuning, developer documentation, and Composer package preparation.
**Duration:** ~1 week
**Dependency:** Phase 9

---

### 10.1 — Security Audit

- [ ] Review SQL generation for injection vectors
- [ ] Review JWT token signing key handling
- [ ] Review rate limiter bypass opportunities
- [ ] Confirm no sensitive data in log output
- [ ] Review dependency vulnerabilities (`composer audit`)

---

### 10.2 — Performance Tuning

- [ ] Profile SchemaMapping SQL generation
- [ ] Optimize cache key computation
- [ ] Review XML serialization memory profile for large pages
- [ ] Benchmark cursor-based vs. OFFSET pagination

---

### 10.3 — Developer Documentation

```
docs/
├── QUICKSTART.md
├── CONFIGURATION_REFERENCE.md
├── WRITING_PLUGINS.md
├── SCHEMA_MAPPING_GUIDE.md
├── SECURITY_GUIDE.md
├── PERFORMANCE_GUIDE.md
└── UPGRADE_GUIDE.md
```

---

### 10.4 — Composer Package Preparation

- Finalize `composer.json` (description, keywords, type, require, require-dev, autoload)
- Add GitHub Action: `ci.yml` (PHPUnit + PHPStan + PHPCS on PHP 8.0, 8.1, 8.2, 8.3)
- Tag `v0.1.0` release
- Submit to Packagist

---

## Implementation Sequence Summary

| Phase | Name | Key Deliverable | Duration |
|-------|------|----------------|---------|
| 0 | Foundation | Namespaces, interfaces, ADRs | 1 week |
| 1 | Configuration | YAML loader, validator, sections | 1 week |
| 2 | Repository Core | RepositoryIdentity, Record, Set, SchemaMapping | 1.5 weeks |
| 3 | Protocol | OaiRequest, OaiResponse, Verb Handlers | 2 weeks |
| 4 | Metadata Serialization | Plugin registry, Dublin Core built-in | 1 week |
| 5 | Flow Control | ResumptionToken, Paginator | 1 week |
| 6 | Access Control | Middleware pipeline, auth, rate limit | 1 week |
| 7 | Observability | Logging, metrics, health check | 0.5 weeks |
| 8 | Infrastructure | DB/cache/HTTP adapters, entry point | 1 week |
| 9 | Integration Testing | Full verb coverage, XSD validation | 1 week |
| 10 | Hardening & Release | Security, performance, docs, Packagist | 1 week |
| **Total** | | | **~12 weeks** |

---

## Dependency Graph

```
Phase 0 (Foundation)
  └── Phase 1 (Configuration)
        └── Phase 2 (Repository Core)
              ├── Phase 3 (Protocol)            ← core request/response
              │     ├── Phase 4 (Metadata)
              │     ├── Phase 5 (Flow Control)
              │     ├── Phase 6 (Access Control)
              │     └── Phase 7 (Observability)
              │           └── Phase 8 (Infrastructure)
              │                 └── Phase 9 (Integration Tests)
              │                       └── Phase 10 (Hardening & Release)
              └── Phase 4 (Metadata)            ← parallel with Phase 3 possible
```

Phases 4, 5, 6, 7 can be developed in parallel once Phase 3's interfaces are stable.

---

## Quality Gates Per Phase

Every phase must satisfy the following before moving to the next:

| Gate | Requirement |
|------|------------|
| ✅ Tests pass | `vendor/bin/phpunit` — green |
| ✅ PHPStan Level 8 | `vendor/bin/phpstan analyse` — 0 errors |
| ✅ PSR-12 compliant | `vendor/bin/phpcs` — 0 violations |
| ✅ Docblocks complete | All public methods documented per project standard |
| ✅ Analysis documents | Each new aggregate gets an analysis doc in `docs/analysis/` |
| ✅ No regressions | Existing value object tests remain green |

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|-----------|
| SchemaMapping SQL complexity across MySQL/PostgreSQL dialects | High | High | Spike in Phase 0; use Doctrine DBAL platform abstraction |
| ResumptionToken state expiry race conditions under load | Medium | High | Stateless JWT tokens preferred; Redis fallback with distributed lock |
| Large XML responses (100K records page) causing memory exhaustion | Medium | High | XMLWriter streaming serialization as alternative to DOMDocument |
| OAI-PMH XSD validation failures on edge-case responses | Medium | Medium | Unit-test XML output against XSD in CI |
| Plugin system abuse (malicious third-party plugins) | Low | High | Document sandboxing recommendations; validate plugin interface at registration |
| PHP version incompatibility in deployment environments | Low | Medium | Test matrix: PHP 8.0, 8.1, 8.2, 8.3 in CI |

---

## File Naming & Class Conventions

All new code must follow the existing project conventions:

- **File header:** `<?php` + docblock with `@author`, `@copyright`, `@license`, `@link`, `@since`
- **Strict types:** `declare(strict_types=1);`
- **PSR-12** code style
- **Aggregates:** mutable if they have lifecycle; immutable if they are composed once
- **Value Objects:** always `final`, always immutable, always validated on construction
- **Exceptions:** named `{Concept}Exception.php`, extend `\RuntimeException` or `\InvalidArgumentException`
- **Tests:** `{ClassName}Test.php`, BDD-style method names, `@covers` annotations

---

## First Steps (Immediate Actions)

The following sequence starts implementation with zero ambiguity:

1. **Create `docs/adr/` directory** — write ADR-001 (token format) and ADR-006 (XML generation) first because these affect large portions of Phase 3-5
2. **Update `composer.json`** — register all 8 new namespaces under `autoload.psr-4`
3. **Create skeleton directories** — all 8 context folders under `src/`
4. **Define `ConfigurationInterface`** — first interface, needed by everything
5. **Implement `Configuration` aggregate** — with tests, before writing any other class
6. **Complete `RepositoryIdentity`** — the Identify verb is the simplest verb and validates the full pipeline

---

*Implementation plan generated February 18, 2026, based on Software Design Event Storming session.*
