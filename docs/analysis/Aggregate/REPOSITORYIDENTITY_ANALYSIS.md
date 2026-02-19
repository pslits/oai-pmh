# RepositoryIdentity Aggregate Analysis

**Analysis Date:** February 18, 2026
**Component:** RepositoryIdentity Aggregate
**File:** `src/Domain/Aggregate/RepositoryIdentity.php`
**Tests:** `tests/Domain/Aggregate/RepositoryIdentityTest.php`
**OAI-PMH Version:** 2.0
**Specification:** [OAI-PMH 2.0 — Identify](https://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
**Status:** ✅ Completed

---

## Executive Summary

`RepositoryIdentity` is a DDD aggregate that owns and validates all data required for the OAI-PMH `Identify` response. It replaces the earlier `RepositoryIdentity` value object, promoting it to an aggregate because it coordinates multiple typed value objects and enforces repository-level invariants. It is created via a named constructor from a configuration array plus an injected `UTCdatetime` representing the earliest record datestamp.

---

## 1. OAI-PMH Requirement

### Specification Context

The OAI-PMH 2.0 specification (section 4.2) defines the `Identify` verb:

> A repository must respond to the `Identify` verb with the following elements:
>
> | Element             | Required | Description                                              |
> |---------------------|----------|----------------------------------------------------------|
> | `repositoryName`    | ✅       | Human-readable name of the repository                   |
> | `baseURL`           | ✅       | Base URL for all OAI-PMH requests                       |
> | `protocolVersion`   | ✅       | Always `"2.0"` for OAI-PMH 2.0                          |
> | `adminEmail`        | ✅       | One or more administrator email addresses (repeatable)   |
> | `earliestDatestamp` | ✅       | Lower bound on all datestamps in the repository          |
> | `deletedRecord`     | ✅       | Deleted-record support: `no`, `transient`, or `persistent` |
> | `granularity`       | ✅       | Finest harvesting granularity supported                  |
> | `compression`       | ⬜       | Optional: supported compression encodings               |
> | `description`       | ⬜       | Optional: extensible description containers (repeatable) |

### Example XML Response

```xml
<Identify>
  <repositoryName>My OAI Repository</repositoryName>
  <baseURL>https://example.org/oai</baseURL>
  <protocolVersion>2.0</protocolVersion>
  <adminEmail>admin@example.org</adminEmail>
  <earliestDatestamp>2000-01-01</earliestDatestamp>
  <deletedRecord>transient</deletedRecord>
  <granularity>YYYY-MM-DD</granularity>
  <compression>gzip</compression>
</Identify>
```

---

## 2. User Story

> **As a** repository server  
> **When** a harvester sends an `Identify` request  
> **I want** a single validated domain object that holds all required Identify data  
> **So that** the response layer can serialize it without performing any validation itself.

### Acceptance Criteria

- [x] `fromConfiguration()` builds a complete, validated aggregate from a config array
- [x] `earliestDatestamp` is injected, not queried inside the aggregate
- [x] `protocolVersion` is always `"2.0"` and is not configurable
- [x] All five required config keys are checked; missing keys throw `InvalidConfigurationException`
- [x] Invalid values for each field throw `InvalidConfigurationException` with the key name in the message
- [x] `compression` defaults to `[]` when absent from config
- [x] `descriptions` default to an empty `DescriptionCollection`; can be set via `withDescriptions()`
- [x] The aggregate is immutable after construction (no setters)
- [x] `withDescriptions()` returns a new instance without mutating the original

---

## 3. Implementation Details

### File Structure

```
src/Domain/Aggregate/RepositoryIdentity.php
src/Domain/Exception/InvalidConfigurationException.php
tests/Domain/Aggregate/RepositoryIdentityTest.php
```

### Class Structure

```php
final class RepositoryIdentity
{
    // Constants
    private const REQUIRED_KEYS = ['repository_name', 'base_url', 'admin_emails',
                                    'deleted_record', 'granularity'];

    // Properties
    private RepositoryName       $repositoryName;
    private BaseURL              $baseURL;
    private ProtocolVersion      $protocolVersion;
    private EmailCollection      $adminEmails;
    private UTCdatetime          $earliestDatestamp;
    private DeletedRecord        $deletedRecord;
    private Granularity          $granularity;
    private string[]             $compression;
    private DescriptionCollection $descriptions;

    // Named constructor (public)
    public static function fromConfiguration(array $config, UTCdatetime $earliestDatestamp): self

    // Getters (public)
    public function getRepositoryName(): RepositoryName
    public function getBaseURL(): BaseURL
    public function getProtocolVersion(): ProtocolVersion
    public function getAdminEmails(): EmailCollection
    public function getEarliestDatestamp(): UTCdatetime
    public function getDeletedRecord(): DeletedRecord
    public function getGranularity(): Granularity
    public function getCompression(): string[]
    public function getDescriptions(): DescriptionCollection
    public function __toString(): string

    // Wither (public)
    public function withDescriptions(DescriptionCollection $descriptions): self

    // Private factory helpers
    private static function assertRequiredKeys(array $config): void
    private static function buildRepositoryName(mixed $value): RepositoryName
    private static function buildBaseURL(mixed $value): BaseURL
    private static function buildEmailCollection(mixed $value): EmailCollection
    private static function buildDeletedRecord(mixed $value): DeletedRecord
    private static function buildGranularity(mixed $value): Granularity
    private static function buildCompression(mixed $value): string[]
    private static function buildDescriptionCollection(): DescriptionCollection
}
```

### Design Characteristics

| Aspect                  | Implementation                                              | Status |
|-------------------------|-------------------------------------------------------------|--------|
| Pattern                 | DDD Aggregate (not a value object)                          | ✅     |
| Instantiation           | Named constructor `fromConfiguration()` only               | ✅     |
| Immutability            | No setters; `withDescriptions()` returns a clone            | ✅     |
| Validation boundary     | All validation in private `build*` helpers                  | ✅     |
| Exception wrapping      | VO `InvalidArgumentException` → `InvalidConfigurationException` | ✅ |
| `protocolVersion`       | Hardcoded `"2.0"` — not configurable                        | ✅     |
| `earliestDatestamp`     | Injected — aggregate is DB-free                             | ✅     |
| `descriptions`          | Not loadable from flat config; use `withDescriptions()`     | ✅     |
| PHPStan Level 8         | 0 errors                                                    | ✅     |
| PSR-12                  | Compliant                                                   | ✅     |

### Dependency Graph

```
RepositoryIdentity (Aggregate)
├── RepositoryName         (ValueObject)
├── BaseURL                (ValueObject)
├── ProtocolVersion        (ValueObject)
├── EmailCollection        (ValueObject — collection)
│   └── Email              (ValueObject)
├── UTCdatetime            (ValueObject — injected)
├── DeletedRecord          (ValueObject)
├── Granularity            (ValueObject)
├── string[]               (compression — plain PHP)
└── DescriptionCollection  (ValueObject — collection, default empty)
```

---

## 4. Design Decisions

### Decision 1: Promote RepositoryIdentity from Value Object to Aggregate

**Context:** An earlier `RepositoryIdentity` value object existed in `src/Domain/ValueObject/`. It held only a single string value and was not the right model for the full Identify response.

**Decision:** Create a new `RepositoryIdentity` aggregate in `src/Domain/Aggregate/` that owns all Identify elements. Keep the old VO in place for backward compatibility; the aggregate supersedes it.

**Rationale:** An aggregate is appropriate here because `RepositoryIdentity` coordinates multiple value objects (`RepositoryName`, `BaseURL`, `EmailCollection`, etc.), enforces cross-field invariants (e.g. granularity and earliestDatestamp format must be compatible), and acts as the root for the Identify response domain model.

---

### Decision 2: Inject `earliestDatestamp` Rather Than Query It

**Context:** The OAI-PMH spec requires `earliestDatestamp`, which in practice comes from a database query (the minimum datestamp of all records in the repository). Querying the database inside a domain aggregate is an infrastructure concern leak.

**Decision:** Accept `UTCdatetime $earliestDatestamp` as the second parameter of `fromConfiguration()`.

**Rationale:** Keeps the aggregate pure — it remains testable in isolation without any database dependency. The calling application service is responsible for querying the DB and injecting the value.

```php
// Application layer
$earliest = new UTCdatetime($repository->findEarliestDatestamp(), $granularity);
$identity = RepositoryIdentity::fromConfiguration($config, $earliest);
```

---

### Decision 3: Hardcode `protocolVersion` as `"2.0"`

**Context:** The OAI-PMH protocol version is a constant of the protocol itself, not of any individual repository. It should never be `"1.0"` or any other value in a modern implementation.

**Decision:** `new ProtocolVersion('2.0')` is created directly in `fromConfiguration()` without reading from config.

**Rationale:** Removes a possible misconfiguration vector. If you are using this library, you are implementing OAI-PMH 2.0. There is no other valid value.

---

### Decision 4: Exclude `descriptions` from Flat Configuration Loading

**Context:** `Description` objects require a `DescriptionFormat` (itself a structured object) and `array $data`. They cannot be expressed as flat YAML strings.

**Decision:** `fromConfiguration()` always produces an aggregate with an empty `DescriptionCollection`. A `withDescriptions(DescriptionCollection $descriptions): self` wither method is provided to attach descriptions programmatically after construction.

**Rationale:** Avoids forcing a complex nested YAML structure onto the config file. Callers that need descriptions (e.g. an OAI-PMH Identity document) can build `Description` objects in code and pass them via `withDescriptions()`.

```php
$identity = RepositoryIdentity::fromConfiguration($config, $earliest)
    ->withDescriptions($myDescriptions);
```

---

### Decision 5: Wrap VO `InvalidArgumentException` as `InvalidConfigurationException`

**Context:** Value object constructors (e.g. `BaseURL`, `Email`) throw `\InvalidArgumentException` on bad input. If these escape the aggregate boundary they leak implementation details — a caller would not know which config key was responsible.

**Decision:** Every `build*` helper catches `InvalidArgumentException` and rethrows it as `InvalidConfigurationException::invalidValue($key, $message)`.

**Bug discovered during testing:** The catch blocks initially failed silently because the file is in `namespace OaiPmh\Domain\Aggregate` and `InvalidArgumentException` (an unqualified name) was resolving to the non-existent `OaiPmh\Domain\Aggregate\InvalidArgumentException` instead of `\InvalidArgumentException`. Fixed by adding `use InvalidArgumentException;`.

```php
// The missing import that caused 4 tests to fail
use InvalidArgumentException;
```

---

## 5. Test Coverage Analysis

### Statistics

| Metric           | Value |
|------------------|-------|
| Total tests      | 30    |
| Assertions       | 46    |
| PHPUnit version  | 9.6.23 |
| All passing      | ✅    |
| PHPStan errors   | 0     |

### Test Categories

| Category                                            | Tests |
|-----------------------------------------------------|-------|
| Happy path — returns correct instance               | 1     |
| Happy path — getter correctness                     | 8     |
| Optional: compression (absent + present)            | 2     |
| Optional: descriptions (default + wither)           | 2     |
| Missing required key (data provider, 5 keys)        | 5     |
| Invalid values (6 fields)                           | 6     |
| All three valid `deleted_record` values (provider)  | 3     |
| Both valid `granularity` values                     | 1     |
| `__toString` content                                | 1     |
| Immutability of `withDescriptions()`                | 1     |

### Key Test Patterns

```php
// Injection test — assertSame proves the exact object is returned
public function testFromConfiguration_EarliestDatestampIsInjected(): void
{
    $earliest = $this->earliestDatestamp();
    $identity = RepositoryIdentity::fromConfiguration($this->minimalConfig(), $earliest);
    $this->assertSame($earliest, $identity->getEarliestDatestamp());
}

// Parameterised missing-key tests
/** @dataProvider provideRequiredKeys */
public function testFromConfiguration_MissingRequiredKey_ThrowsInvalidConfigurationException(
    string $missingKey
): void {
    $config = $this->minimalConfig();
    unset($config[$missingKey]);
    $this->expectException(InvalidConfigurationException::class);
    $this->expectExceptionMessage($missingKey);
    RepositoryIdentity::fromConfiguration($config, $this->earliestDatestamp());
}

// Immutability / wither test
public function testWithDescriptions_ReturnsNewInstance_LeavingOriginalUnchanged(): void
{
    $original = RepositoryIdentity::fromConfiguration(...);
    $updated  = $original->withDescriptions(new DescriptionCollection());
    $this->assertNotSame($original, $updated);
    $this->assertCount(0, $original->getDescriptions()); // original unchanged
}
```

---

## 6. Issues Discovered and Fixed

### Bug: Missing `use InvalidArgumentException;` Import

**Symptom:** 4 tests failed at runtime with:

```
Failed asserting that exception of type "InvalidArgumentException" matches
expected exception "OaiPmh\Domain\Exception\InvalidConfigurationException".
```

**Root Cause:** PHP resolves unqualified class names relative to the current namespace. In `namespace OaiPmh\Domain\Aggregate`, writing `catch (InvalidArgumentException $e)` tried to catch `OaiPmh\Domain\Aggregate\InvalidArgumentException`, which does not exist. The catch clause was never triggered, so the raw `InvalidArgumentException` from the VO propagated up.

**Fix:** Added `use InvalidArgumentException;` to the import block in `RepositoryIdentity.php`.

**Affected catch blocks:** `buildRepositoryName`, `buildBaseURL`, `buildEmailCollection` (×2), `buildDeletedRecord`, `buildGranularity` — 6 catch blocks total.

---

## 7. Quality Metrics

| Metric          | Result     | Status |
|-----------------|------------|--------|
| Tests           | 30 / 30    | ✅     |
| Assertions      | 46         | ✅     |
| PHPStan Level 8 | 0 errors   | ✅     |
| PSR-12          | Compliant  | ✅     |
| PHP version     | 8.0+       | ✅     |

---

## 8. Usage Examples

### Basic Construction

```php
use OaiPmh\Domain\Aggregate\RepositoryIdentity;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\UTCdatetime;

$config = [
    'repository_name' => 'My Repository',
    'base_url'        => 'https://example.org/oai',
    'admin_emails'    => ['admin@example.org'],
    'deleted_record'  => 'transient',
    'granularity'     => 'YYYY-MM-DD',
    'compression'     => ['gzip'],
];

$granularity = new Granularity('YYYY-MM-DD');
$earliest    = new UTCdatetime('2000-01-01', $granularity);

$identity = RepositoryIdentity::fromConfiguration($config, $earliest);
```

### With Descriptions

```php
$identity = RepositoryIdentity::fromConfiguration($config, $earliest)
    ->withDescriptions($descriptionCollection);
```

### Accessing Elements for Serialization

```php
$identity->getRepositoryName()->getRepositoryName(); // 'My Repository'
$identity->getBaseURL()->getBaseUrl();               // 'https://example.org/oai'
$identity->getProtocolVersion()->getProtocolVersion(); // '2.0'
$identity->getAdminEmails();                         // EmailCollection (iterable)
$identity->getEarliestDatestamp();                   // UTCdatetime
$identity->getDeletedRecord()->getDeletedRecord();   // 'transient'
$identity->getGranularity()->getValue();             // 'YYYY-MM-DD'
$identity->getCompression();                         // ['gzip']
$identity->getDescriptions();                        // DescriptionCollection
```

---

## 9. Next Steps

The `RepositoryIdentity` aggregate feeds the `OaiResponse` aggregate, which is the third and final piece needed for the Identify verb. `OaiResponse` is responsible for serializing `RepositoryIdentity` data into a well-formed OAI-PMH XML envelope.

**Implementation order:**
1. ✅ `OaiRequest` — validates and parses incoming protocol requests
2. ✅ `RepositoryIdentity` — holds validated repository identity data
3. 🔴 `OaiResponse` — wraps identity data into the XML response envelope

---

## 10. References

- [OAI-PMH 2.0 Specification — Identify](https://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [OAI-PMH 2.0 Specification — Deleted Records](https://www.openarchives.org/OAI/openarchivesprotocol.html#DeletedRecords)
- [OAI-PMH 2.0 Specification — Datestamps](https://www.openarchives.org/OAI/openarchivesprotocol.html#Datestamps)
- Related analysis: [docs/analysis/ValueObject/REPOSITORYIDENTITY_ANALYSIS.md](../ValueObject/REPOSITORYIDENTITY_ANALYSIS.md) — earlier VO incarnation
- Related analysis: [docs/analysis/ValueObject/BASEURL_ANALYSIS.md](../ValueObject/BASEURL_ANALYSIS.md)
- Related analysis: [docs/analysis/ValueObject/EMAIL_ANALYSIS.md](../ValueObject/EMAIL_ANALYSIS.md)
- Related analysis: [docs/analysis/ValueObject/DELETEDRECORD_ANALYSIS.md](../ValueObject/DELETEDRECORD_ANALYSIS.md)
- Related analysis: [docs/analysis/ValueObject/GRANULARITY_ANALYSIS.md](../ValueObject/GRANULARITY_ANALYSIS.md)

---

*Analysis generated on February 18, 2026*
