# UTCdatetime Value Object Analysis  

**Analysis Date:** February 7, 2026  
**Component:** UTCdatetime Value Object  
**File:** `src/Domain/ValueObject/UTCdatetime.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Datestamps](http://www.openarchives.org/OAI/openarchivesprotocol.html#Dates)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification:

> All date and time values in OAI-PMH requests and responses must be expressed in UTC (Coordinated Universal Time).
> The formats for date/time encoding are YYYY-MM-DD and YYYY-MM-DDThh:mm:ssZ respectively.

### Key Requirements

- ✅ Must be in UTC timezone
- ✅ Two formats: `YYYY-MM-DD` (date) or `YYYY-MM-DDThh:mm:ssZ` (datetime with seconds)
- ✅ Format determined by repository's granularity
- ✅ Used for earliestDatestamp, datestamp in records, from/until parameters

### XML Examples

```xml
<!-- Date granularity -->
<earliestDatestamp>2000-01-01</earliestDatestamp>

<!-- DateTime granularity -->
<earliestDatestamp>2000-01-01T00:00:00Z</earliestDatestamp>
<datestamp>2026-02-07T14:30:00Z</datestamp>
```

---

## 2. User Story

**As a** repository administrator  
**When** I need to specify dates and times in OAI-PMH responses  
**Where** temporal precision is critical for harvesting  
**I want** to ensure all datetimes are in UTC and properly formatted  
**Because** harvesters rely on consistent datetime formats for synchronization  

### Acceptance Criteria

- [x] Accept date format `YYYY-MM-DD` with DATE granularity
- [x] Accept datetime format `YYYY-MM-DDThh:mm:ssZ` with DATE_TIME_SECOND granularity
- [x] Reject invalid date/datetime formats
- [x] Store as `DateTimeImmutable` internally
- [x] Maintain granularity information
- [x] Immutable value object
- [x] Value equality based on datetime and granularity

---

## 3. Implementation Details

### File Structure
```
src/Domain/ValueObject/UTCdatetime.php (149 lines)
tests/Domain/ValueObject/UTCdatetimeTest.php
```

### Class Structure

```php
final class UTCdatetime
{
    private DateTimeImmutable $dateTime;
    private Granularity $granularity;
    
    public function __construct(string $dateTime, Granularity $granularity)
    public function getValue(): string
    public function getDateTime(): DateTimeImmutable
    public function getGranularity(): Granularity
    public function equals(self $other): bool
    public function __toString(): string
    private function validateDateTime(string $dateTime, Granularity $granularity): void
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Immutability** | Uses `DateTimeImmutable` | Prevents accidental modification | ✅ |
| **Granularity-aware** | Stores granularity with datetime | Matches repository policy | ✅ |
| **UTC enforcement** | Parses with UTC timezone | Spec requirement | ✅ |
| **Format validation** | Validates against granularity | Prevents format mismatch | ✅ |
| **Type safety** | Strict types, no mixed | Modern PHP practices | ✅ |

### Validation Logic

```php
private function validateDateTime(string $dateTime, Granularity $granularity): void
{
    if ($granularity->getValue() === Granularity::DATE) {
        // Validate YYYY-MM-DD format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTime)) {
            throw new InvalidArgumentException(
                sprintf('Invalid date format: %s. Expected: YYYY-MM-DD', $dateTime)
            );
        }
    } else {
        // Validate YYYY-MM-DDThh:mm:ssZ format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $dateTime)) {
            throw new InvalidArgumentException(
                sprintf('Invalid date/time format: %s. Expected: YYYY-MM-DDThh:mm:ssZ', $dateTime)
            );
        }
    }
}
```

### Relationship to Other Components

```
UTCdatetime
  │
  ├──> Requires Granularity (composition)
  │
  └──> Used in:
       ├─> earliestDatestamp (Identify response)
       ├─> datestamp (record headers)
       ├─> from/until (request parameters)
       └─> responseDate (all responses)
```

---

## 4. Test Coverage Analysis

### Test Statistics

- **Total Tests:** 14
- **Assertions:** 18
- **Coverage:** 89.74% lines (known issue with unreachable defensive code)
- **Status:** ✅ All passing

### Test Categories

- ✅ **Constructor validation** (4 tests)
  - Valid date format
  - Valid datetime format
  - Invalid date format
  - Invalid datetime format
  
- ✅ **Getter methods** (2 tests)
  - `getDateTime()` returns `DateTimeImmutable`
  - `getGranularity()` returns `Granularity`
  
- ✅ **Value equality** (5 tests)
  - Same datetime value (both granularities)
  - Different datetime values (both granularities)
  - Different granularity (same datetime)
  
- ✅ **String representation** (2 tests)
  - Date format output
  - Datetime format output
  
- ✅ **Immutability** (1 test)

---

## 5. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\Granularity;

// Date-only format
$dateGranularity = new Granularity(Granularity::DATE);
$date = new UTCdatetime('2026-02-07', $dateGranularity);
echo $date->getValue(); // 2026-02-07

// DateTime format (with seconds)
$datetimeGranularity = new Granularity(Granularity::DATE_TIME_SECOND);
$datetime = new UTCdatetime('2026-02-07T14:30:00Z', $datetimeGranularity);
echo $datetime->getValue(); // 2026-02-07T14:30:00Z
```

### Validation Examples

```php
// ✅ Valid date
new UTCdatetime('2026-02-07', new Granularity(Granularity::DATE));

// ✅ Valid datetime
new UTCdatetime('2026-02-07T14:30:00Z', new Granularity(Granularity::DATE_TIME_SECOND));

// ❌ Invalid: Wrong format for granularity
new UTCdatetime('2026-02-07', new Granularity(Granularity::DATE_TIME_SECOND)); // Exception!

// ❌ Invalid: Missing T separator
new UTCdatetime('2026-02-07 14:30:00Z', new Granularity(Granularity::DATE_TIME_SECOND)); // Exception!

// ❌ Invalid: Missing Z suffix
new UTCdatetime('2026-02-07T14:30:00', new Granularity(Granularity::DATE_TIME_SECOND)); // Exception!
```

### Working with DateTimeImmutable

```php
$datetime = new UTCdatetime('2026-02-07T14:30:00Z', new Granularity(Granularity::DATE_TIME_SECOND));

// Access underlying DateTimeImmutable
$dt = $datetime->getDateTime();
echo $dt->format('l, F j, Y'); // Friday, February 7, 2026

// Compare dates
$laterDate = new UTCdatetime('2026-02-08T00:00:00Z', new Granularity(Granularity::DATE_TIME_SECOND));
if ($datetime->getDateTime() < $laterDate->getDateTime()) {
    echo "First datetime is earlier";
}
```

### Real-World Integration

```php
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\Granularity;

// Repository Identify response
$granularity = new Granularity(Granularity::DATE_TIME_SECOND);

$identify = [
    'earliestDatestamp' => new UTCdatetime('2010-01-01T00:00:00Z', $granularity),
    'granularity' => $granularity,
    // ... other fields
];

// All record datestamps must match this granularity
$recordDatestamps = [
    new UTCdatetime('2026-02-07T14:30:00Z', $granularity),
    new UTCdatetime('2026-02-06T10:15:30Z', $granularity),
];
```

---

## 6. Design Decisions

### Decision 1: Use `DateTimeImmutable` Internally

**Context:** How to store the parsed datetime value

**Options Considered:**
1. Store as string only
2. Store as `DateTime` object
3. Store as `DateTimeImmutable` object (chosen)

**Rationale:**
- Immutability aligns with value object pattern
- Enables date comparisons and manipulation
- Type-safe operations
- Prevents accidental modification
- Standard PHP approach

**Trade-offs:**
- ✅ Type-safe date operations
- ✅ Immutable (thread-safe)
- ✅ Rich API for date operations
- ⚠️ Slightly more memory than string
- ✅ Better than `DateTime` (mutable)

### Decision 2: Store Granularity with DateTime

**Context:** Should UTCdatetime know its own granularity?

**Options Considered:**
1. Store granularity with datetime (chosen)
2. External granularity management
3. Infer granularity from format

**Rationale:**
- Self-contained: datetime knows how to format itself
- Consistent: prevents granularity mismatches
- Clear: no ambiguity about intended precision
- Type-safe: granularity is a Granularity object

**Trade-offs:**
- ✅ Self-documenting
- ✅ Prevents mismatches
- ✅ Correct formatting guaranteed
- ⚠️ Slightly more memory
- ✅ Worth it for correctness

### Decision 3: Separate Constructor for Each Granularity vs. Single Constructor

**Context:** How to construct UTCdatetime instances

**Current Approach:** Single constructor with Granularity parameter

**Alternative Considered:** Named constructors
```php
UTCdatetime::fromDate('2026-02-07');
UTCdatetime::fromDateTime('2026-02-07T14:30:00Z');
```

**Rationale for Current Approach:**
- Explicit granularity parameter
- Clear validation requirements
- Consistent pattern
- Less code duplication

**Trade-offs:**
- ✅ Explicit granularity
- ✅ Clear requirements
- ⚠️ Slightly more verbose
- ✅ Could add named constructors later without breaking changes

---

## 7. Known Issues & Future Enhancements

### Current Known Issues

- **Coverage Gap** (89.74% instead of 100%)
  - Lines where `DateTimeImmutable::createFromFormat()` returns `false` are unreachable after validation
  - Similar to BaseURL issue with defensive checks
  - Acceptable trade-off for type safety

### Future Enhancements

- [ ] **Issue #8**: Migrate to PHP 8.2 `readonly` properties (Priority: Low)
  ```php
  public readonly DateTimeImmutable $dateTime;
  public readonly Granularity $granularity;
  ```

- [ ] **Named Constructors** (Priority: Low)
  ```php
  public static function fromDate(string $date): self
  {
      return new self($date, new Granularity(Granularity::DATE));
  }
  
  public static function fromDateTime(string $datetime): self
  {
      return new self($datetime, new Granularity(Granularity::DATE_TIME_SECOND));
  }
  ```

- [ ] **Current Timestamp Factory** (Priority: Low)
  ```php
  public static function now(Granularity $granularity): self
  {
      $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
      return new self($now->format(...), $granularity);
  }
  ```

---

## 8. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | UTCdatetime | Granularity | DeletedRecord |
|--------|-------------|-------------|---------------|
| Complexity | High (date parsing) | Low (enum) | Low (enum) |
| Composition | Uses Granularity | Standalone | Standalone |
| Validation | Format + value | Whitelist | Whitelist |
| Internal storage | `DateTimeImmutable` + `Granularity` | `string` | `string` |
| Equality | DateTime + granularity | String | String |

### Why UTCdatetime vs. Simple String?

**Q: Why not just use strings for dates?**
- Type safety: `UTCdatetime` guarantees valid format
- Granularity enforcement: Can't mix date/datetime formats
- Date operations: Can compare, manipulate dates
- Validation: Catches errors early
- Self-documenting: Clear intent

**Q: Why not use plain `DateTimeImmutable`?**
- OAI-PMH specific: Needs granularity tracking
- Format guarantee: Always outputs correct OAI-PMH format
- Validation: OAI-PMH specific rules
- Domain clarity: Specific to OAI-PMH datestamps

---

## 9. Recommendations

### For Developers Using UTCdatetime VO

**DO:**
- ✅ Match granularity to repository policy
- ✅ Use constants from Granularity class
- ✅ Validate external input early
- ✅ Use `getDateTime()` for date operations

```php
// ✅ Good: Explicit granularity
$granularity = new Granularity(Granularity::DATE_TIME_SECOND);
$datetime = new UTCdatetime('2026-02-07T14:30:00Z', $granularity);

// ✅ Good: Date operations via getDateTime()
if ($datetime->getDateTime() > $otherDateTime->getDateTime()) {
    // Compare dates
}
```

**DON'T:**
- ❌ Don't mix granularities in same repository
- ❌ Don't manually format datetime strings
- ❌ Don't use non-UTC timezones
- ❌ Don't forget the 'Z' suffix for datetime format

```php
// ❌ Bad: Wrong timezone
new UTCdatetime('2026-02-07T14:30:00+01:00', $granularity); // Exception!

// ❌ Bad: Missing Z
new UTCdatetime('2026-02-07T14:30:00', $granularity); // Exception!

// ✅ Good: Always UTC with Z
new UTCdatetime('2026-02-07T14:30:00Z', $granularity);
```

### For Repository Administrators

- ✅ Choose granularity based on update frequency
- ✅ Be consistent across all records
- ✅ Ensure system clocks are synchronized (NTP)
- ✅ Always use UTC (no local time adjustments)

### For Library Maintainers

- ✅ Keep validation strict (OAI-PMH compliance)
- ✅ Consider named constructors for convenience
- ✅ Document timezone requirements clearly
- ✅ Plan for PHP 8.2 readonly migration

---

## 10. References

### Specifications
- [OAI-PMH 2.0 - Datestamps](http://www.openarchives.org/OAI/openarchivesprotocol.html#Dates)
- [ISO 8601 Date/Time Format](https://www.iso.org/iso-8601-date-and-time-format.html)
- [PHP DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php)

### Related Analysis Documents
- [docs/GRANULARITY_ANALYSIS.md](GRANULARITY_ANALYSIS.md) - Granularity value object
- [docs/EMAIL_ANALYSIS.md](EMAIL_ANALYSIS.md) - Similar validation pattern
- [docs/BASEURL_ANALYSIS.md](BASEURL_ANALYSIS.md) - Similar coverage issue

### Related GitHub Issues
- Issue #8: PHP 8.2 readonly property migration
- Issue #10: Define repository identity value object

---

## 11. Appendix

### Test Output

```
UTCdatetime (OaiPmh\Tests\Domain\ValueObject\UTCdatetime)
 ✔ Can instantiate with valid date
 ✔ Can instantiate with valid date time
 ✔ Throws exception for invalid date
 ✔ Throws exception for invalid date time
 ✔ Get date time returns date time immutable
 ✔ Get granularity returns granularity instance
 ✔ Equals returns true for same date time value
 ✔ Equals returns true for same date value
 ✔ Equals returns false for different date time value
 ✔ Equals returns false for different date value
 ✔ Equals returns false for different granularity
 ✔ To string returns expected date time format
 ✔ To string returns expected date format
 ✔ Is immutable

OK (14 tests, 18 assertions)
```

### Coverage Report

```
UTCdatetime.php
  Lines: 89.74% (35/39)
  Methods: 85.71% (6/7)
```

### Format Examples

| Format | Example | Granularity | Valid |
|--------|---------|-------------|-------|
| `YYYY-MM-DD` | `2026-02-07` | DATE | ✅ |
| `YYYY-MM-DDThh:mm:ssZ` | `2026-02-07T14:30:00Z` | DATE_TIME_SECOND | ✅ |
| `YYYY-MM-DD hh:mm:ss` | `2026-02-07 14:30:00` | - | ❌ Missing T |
| `YYYY-MM-DDThh:mm:ss` | `2026-02-07T14:30:00` | - | ❌ Missing Z |
| `DD-MM-YYYY` | `07-02-2026` | - | ❌ Wrong order |

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*  
*Author: Paul Slits <paul.slits@gmail.com>*
