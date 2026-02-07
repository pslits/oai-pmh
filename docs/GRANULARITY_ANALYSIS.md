# Granularity Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** Granularity Value Object  
**File:** `src/Domain/ValueObject/Granularity.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Granularity](http://www.openarchives.org/OAI/openarchivesprotocol.html#Granularity)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 3.3.1 (Granularity):

> **granularity** - the finest harvesting granularity supported by the repository.
> The legitimate values are `YYYY-MM-DD` and `YYYY-MM-DDThh:mm:ssZ` with meanings as defined in ISO8601.

### Key Requirements

- ✅ Must be one of two values: `YYYY-MM-DD` or `YYYY-MM-DDThh:mm:ssZ`
- ✅ Required in Identify response (exactly one)
- ✅ Indicates finest temporal granularity for datestamps
- ✅ All datestamps in repository must match this granularity

### XML Example

```xml
<Identify>
  <repositoryName>DSpace at My University</repositoryName>
  <baseURL>http://www.example.org/oai</baseURL>
  <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
</Identify>
```

### Granularity Levels

| Granularity | Format | Example | Precision | Use Case |
|-------------|--------|---------|-----------|----------|
| `YYYY-MM-DD` | Date only | `2026-02-07` | Day | Simple repositories, daily updates |
| `YYYY-MM-DDThh:mm:ssZ` | Date + Time | `2026-02-07T14:30:00Z` | Second | Real-time systems, fine-grained tracking |

---

## 2. User Story

**As a** repository administrator  
**When** I configure my OAI-PMH repository's temporal granularity  
**Where** I need to specify the precision of datestamps  
**I want** to declare whether datestamps include time or just date  
**Because** harvesters need to know the precision of temporal information  

### Acceptance Criteria

- [x] Granularity must accept only two values: `YYYY-MM-DD` or `YYYY-MM-DDThh:mm:ssZ`
- [x] Invalid granularities must be rejected with clear error messages
- [x] Granularity value object must be immutable after creation
- [x] Two Granularity instances with the same value must be equal
- [x] Granularity must have a human-readable string representation
- [x] Constants must be provided for each allowed value

---

## 3. Implementation Details

### File Structure
```
src/Domain/ValueObject/Granularity.php
tests/Domain/ValueObject/GranularityTest.php
```

### Class Structure
```php
final class Granularity
{
    public const DATE = 'YYYY-MM-DD';
    public const DATE_TIME_SECOND = 'YYYY-MM-DDThh:mm:ssZ';
    private const ALLOWED = [self::DATE, self::DATE_TIME_SECOND];
    private string $granularity;
    
    public function __construct(string $granularity)
    public function getValue(): string
    public function equals(self $other): bool
    public function __toString(): string
    private function validateGranularity(string $granularity): void
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Immutability** | `final` class, `private` property | Required for value objects | ✅ |
| **Validation** | Whitelist of two allowed values | Spec defines exact values | ✅ |
| **Constants** | Public constants for each value | Type-safe usage | ✅ |
| **Descriptive Names** | DATE, DATE_TIME_SECOND | Clear intent | ✅ |

### Test Coverage

- **Total Tests:** 6
- **Assertions:** 10  
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

---

## 4. Code Examples

### Basic Usage
```php
use OaiPmh\Domain\ValueObject\Granularity;

// Date-only granularity
$dateGranularity = new Granularity(Granularity::DATE);

// Date-time granularity (with seconds)
$datetimeGranularity = new Granularity(Granularity::DATE_TIME_SECOND);

echo $dateGranularity->getValue(); // YYYY-MM-DD
```

### Integration with UTCdatetime
```php
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\UTCdatetime;

$granularity = new Granularity(Granularity::DATE_TIME_SECOND);

// UTCdatetime will format according to granularity
$timestamp = new UTCdatetime('2026-02-07T14:30:00Z');
```

---

## 5. Recommendations

### For Repository Administrators

- **Use `DATE`** when:
  - ✅ Records updated daily or less frequently
  - ✅ Exact time of updates not important
  - ✅ Simpler system requirements

- **Use `DATE_TIME_SECOND`** when:
  - ✅ Real-time or frequent updates
  - ✅ Precise temporal tracking needed
  - ✅ Fine-grained harvesting required

---

## 6. References

- [OAI-PMH 2.0 Specification - Granularity](http://www.openarchives.org/OAI/openarchivesprotocol.html#Granularity)
- [ISO 8601 Date/Time Format](https://www.iso.org/iso-8601-date-and-time-format.html)
- [docs/UTCDATETIME_ANALYSIS.md](UTCDATETIME_ANALYSIS.md) - Related datetime handling
- [docs/DELETEDRECORD_ANALYSIS.md](DELETEDRECORD_ANALYSIS.md) - Similar enumeration pattern

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*
