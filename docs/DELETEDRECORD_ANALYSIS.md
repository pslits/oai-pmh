# DeletedRecord Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** DeletedRecord Value Object  
**File:** `src/Domain/ValueObject/DeletedRecord.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Identify Response](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 3.1.1.2 (Identify):

> **deletedRecord** - the manner in which the repository supports the notion of deleted records.
> Legitimate values are no, transient, persistent with meanings defined in the section on deletion.

From section 3.5 (Deleted Records):

> - **no**: the repository does not maintain information about deletions
> - **transient**: the repository maintains information about deletions, but does not guarantee that a list of deletions is maintained persistently or complete
> - **persistent**: the repository maintains information about deletions with no time limit

### Key Requirements

- ✅ Must be one of three values: `no`, `transient`, or `persistent`
- ✅ Required in Identify response (exactly one)
- ✅ Indicates repository's deleted record policy
- ✅ Affects harvester behavior for record synchronization

### XML Example

```xml
<Identify>
  <repositoryName>DSpace at My University</repositoryName>
  <baseURL>http://www.example.org/oai</baseURL>
  <protocolVersion>2.0</protocolVersion>
  <adminEmail>admin@example.org</adminEmail>
  <earliestDatestamp>2000-01-01T00:00:00Z</earliestDatestamp>
  <deletedRecord>persistent</deletedRecord>
  <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
</Identify>
```

### Deleted Record Policy Impact

| Policy | Meaning | Harvester Impact | Common Use Case |
|--------|---------|------------------|-----------------|
| `no` | No deletion tracking | Cannot detect deletions | Simple repositories, append-only |
| `transient` | Temporary deletion info | Can detect recent deletions | Limited storage, rotating deletion log |
| `persistent` | Permanent deletion info | Full deletion tracking | Production systems, compliance requirements |

### OAI-PMH Compliance Notes

- Repositories **must** declare their policy accurately
- Harvesters **must** respect the declared policy
- Policy **cannot** change during a harvest session
- Policy **should** remain stable over time

---

## 2. User Story

**As a** repository administrator  
**When** I configure my OAI-PMH repository's deletion policy  
**Where** I need to specify how deleted records are handled  
**I want** to declare whether deletion information is tracked (and for how long)  
**Because** harvesters need to know if they can synchronize deletions with my repository  

### Acceptance Criteria

- [x] DeletedRecord must accept only three values: `no`, `transient`, `persistent`
- [x] Invalid values must be rejected with clear error messages
- [x] DeletedRecord value object must be immutable after creation
- [x] Two DeletedRecord instances with the same value must be equal
- [x] DeletedRecord must have a human-readable string representation
- [x] Constants must be provided for each allowed value

---

## 3. Implementation Details

### File Structure

```
src/Domain/ValueObject/DeletedRecord.php
tests/Domain/ValueObject/DeletedRecordTest.php
```

### Class Structure

```php
final class DeletedRecord
{
    public const NO = 'no';
    public const TRANSIENT = 'transient';
    public const PERSISTENT = 'persistent';
    private const ALLOWED = [self::NO, self::TRANSIENT, self::PERSISTENT];
    
    private string $value;
    
    public function __construct(string $value)
    public function getValue(): string
    public function equals(self $other): bool
    public function __toString(): string
    private function validateDeletedRecord(string $value): void
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Immutability** | `final` class, `private` property | Required for value objects | ✅ |
| **Validation** | Whitelist of three allowed values | Spec defines exact values | ✅ |
| **Constants** | Public constants for each value | Type-safe usage | ✅ |
| **Value Equality** | `equals()` method compares strings | Domain requirement | ✅ |
| **Type Safety** | Strict types, no mixed | Modern PHP best practices | ✅ |
| **Error Handling** | `InvalidArgumentException` with allowed values | Clear feedback | ✅ |

### Validation Logic

```php
private const ALLOWED = [
    self::NO,
    self::TRANSIENT,
    self::PERSISTENT,
];

private function validateDeletedRecord(string $value): void
{
    if (!in_array($value, self::ALLOWED, true)) {
        throw new InvalidArgumentException(
            sprintf(
                'Invalid deletedRecord value: %s. Allowed values are: %s',
                $value,
                implode(', ', self::ALLOWED)
            )
        );
    }
}
```

**Validation Strategy:**
- Uses strict whitelist validation (`in_array` with `strict` flag)
- Only three possible values (enumeration pattern)
- Descriptive error messages show allowed values
- Fail-fast pattern (validate before assignment)

### Relationship to Other Components

```
DeletedRecord
  │
  └──> Part of Repository Identity (Identify response)
       └──> Required for OAI-PMH Identify verb
            └──> Informs harvesters about deletion tracking policy
```

---

## 4. Acceptance Criteria

### Functional Requirements

| Requirement | Test Coverage | Status |
|-------------|---------------|--------|
| Accept `no` value | `testCanInstantiateWithAllowedValues` | ✅ PASS |
| Accept `transient` value | `testCanInstantiateWithAllowedValues` | ✅ PASS |
| Accept `persistent` value | `testCanInstantiateWithAllowedValues` | ✅ PASS |
| Reject invalid values | `testThrowsExceptionForInvalidValue` | ✅ PASS |
| Value equality for same value | `testEqualsReturnsTrueForSameValue` | ✅ PASS |
| Value inequality for different values | `testEqualsReturnsFalseForDifferentValue` | ✅ PASS |
| String representation | `testToStringReturnsExpectedFormat` | ✅ PASS |
| Immutability | `testIsImmutable` | ✅ PASS |

### OAI-PMH Protocol Compliance

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Three allowed values | Constants + validation | ✅ PASS |
| Case-sensitive matching | Strict string comparison | ✅ PASS |
| Immutable value | No setters, private properties | ✅ PASS |
| String representation | `__toString()` method | ✅ PASS |
| Domain purity | No XML/HTTP concerns | ✅ PASS |

### Non-Functional Requirements

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Performance | O(1) validation via array lookup | ✅ PASS |
| Memory efficiency | Single string property | ✅ PASS |
| Type safety | Strict types, constants | ✅ PASS |
| Error clarity | Descriptive messages with allowed values | ✅ PASS |

---

## 5. Test Coverage Analysis

### Test Statistics

- **Total Tests:** 6
- **Assertions:** 11
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

### Test Categories

- ✅ **Constructor validation** (2 tests)
  - `testCanInstantiateWithAllowedValues` (tests all 3 values)
  - `testThrowsExceptionForInvalidValue`
  
- ✅ **Value equality** (2 tests)
  - `testEqualsReturnsTrueForSameValue`
  - `testEqualsReturnsFalseForDifferentValue`
  
- ✅ **String representation** (1 test)
  - `testToStringReturnsExpectedFormat`
  
- ✅ **Immutability** (1 test)
  - `testIsImmutable`

### Test Quality

**Strengths:**
- ✅ BDD-style Given-When-Then structure
- ✅ User story comments for context
- ✅ Tests all three allowed values
- ✅ Tests invalid value rejection
- ✅ Descriptive test method names
- ✅ Comprehensive assertions
- ✅ 100% code coverage

**Coverage Gaps:** None identified

---

## 6. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\DeletedRecord;

// ✅ Using constants (recommended)
$policy = new DeletedRecord(DeletedRecord::PERSISTENT);

// Get the policy value
echo $policy->getValue(); // persistent

// String representation
echo $policy; // DeletedRecord(value: persistent)
```

### All Allowed Values

```php
// ✅ No deletion tracking
$no = new DeletedRecord(DeletedRecord::NO);

// ✅ Transient deletion tracking
$transient = new DeletedRecord(DeletedRecord::TRANSIENT);

// ✅ Persistent deletion tracking
$persistent = new DeletedRecord(DeletedRecord::PERSISTENT);

// ❌ Invalid value (throws InvalidArgumentException)
$invalid = new DeletedRecord('maybe'); // Exception!
```

### Validation Examples

```php
use OaiPmh\Domain\ValueObject\DeletedRecord;

// ✅ Valid values (using constants)
new DeletedRecord(DeletedRecord::NO);
new DeletedRecord(DeletedRecord::TRANSIENT);
new DeletedRecord(DeletedRecord::PERSISTENT);

// ✅ Valid values (using strings)
new DeletedRecord('no');
new DeletedRecord('transient');
new DeletedRecord('persistent');

// ❌ Invalid values (throw InvalidArgumentException)
new DeletedRecord('yes');          // Not in allowed list
new DeletedRecord('No');           // Case-sensitive!
new DeletedRecord('PERSISTENT');   // Case-sensitive!
new DeletedRecord('');             // Empty string
new DeletedRecord('true');         // Not a policy value
```

### Equality Comparison

```php
$policy1 = new DeletedRecord(DeletedRecord::PERSISTENT);
$policy2 = new DeletedRecord(DeletedRecord::PERSISTENT);
$policy3 = new DeletedRecord(DeletedRecord::NO);

var_dump($policy1->equals($policy2)); // true (same value)
var_dump($policy1->equals($policy3)); // false (different values)
```

### Real-World Usage in Identify Response

```php
use OaiPmh\Domain\ValueObject\DeletedRecord;
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\RepositoryName;
use OaiPmh\Domain\ValueObject\Granularity;

// Configure repository with persistent deletion tracking
$identify = [
    'baseURL' => new BaseURL('https://repository.example.org/oai'),
    'repositoryName' => new RepositoryName('University Digital Archive'),
    'deletedRecord' => new DeletedRecord(DeletedRecord::PERSISTENT),
    'granularity' => new Granularity(Granularity::DATE_TIME_SECOND),
    // ... other fields
];

// Access the policy
echo $identify['deletedRecord']->getValue(); // persistent
```

---

## 7. Design Decisions

### Decision 1: Use String Constants Instead of Enum

**Context:** PHP 8.0 doesn't have native enums (added in PHP 8.1)

**Options Considered:**
1. String constants (chosen for PHP 8.0)
2. PHP 8.1+ enum (future)
3. Class constants with separate class per value

**Rationale:** String constants because:
- Compatible with PHP 8.0
- Simple and clear
- Type-safe when using constants
- Easy to migrate to enum in PHP 8.1+

**Trade-offs:**
- ✅ Simple implementation
- ✅ IDE autocompletion
- ✅ No external dependencies
- ⚠️ Can still pass raw strings (user error)
- ❌ Not as type-safe as enums

**Future Migration (PHP 8.1+):**
```php
// Current (PHP 8.0)
class DeletedRecord {
    public const NO = 'no';
    public const TRANSIENT = 'transient';
    public const PERSISTENT = 'persistent';
}

// Future (PHP 8.1+)
enum DeletedRecord: string {
    case NO = 'no';
    case TRANSIENT = 'transient';
    case PERSISTENT = 'persistent';
}
```

### Decision 2: Case-Sensitive Validation

**Context:** OAI-PMH specification uses lowercase values

**Options Considered:**
1. Case-sensitive matching (chosen)
2. Case-insensitive matching with normalization

**Rationale:**
- Specification explicitly defines lowercase values
- Strict validation prevents ambiguity
- Encourages use of constants
- Matches OAI-PMH XML expectations

**Trade-offs:**
- ✅ Specification compliance
- ✅ No ambiguity
- ✅ Encourages constant usage
- ❌ User must provide exact case
- ❌ 'No' or 'NO' will fail (by design)

### Decision 3: Private Validation Method

**Context:** Where to place validation logic

**Options Considered:**
1. Private method (chosen)
2. Static validation method
3. Separate validator class

**Rationale:**
- Encapsulation: validation is internal concern
- Simple: no need for separate class
- Consistent: matches other value objects
- Reusable: could be called from other methods if needed

**Trade-offs:**
- ✅ Simple and clear
- ✅ Encapsulated
- ✅ Consistent with library patterns
- ❌ Cannot validate without instantiating (acceptable)

---

## 8. Known Issues & Future Enhancements

### Current Known Issues

None

### Future Enhancements

- [ ] **Issue #8**: Migrate to PHP 8.2 `readonly` properties (Priority: Low)
  ```php
  // Current (PHP 8.0)
  private string $value;
  
  // Future (PHP 8.2)
  public readonly string $value;
  ```

- [ ] **PHP 8.1 Enum Migration** (Priority: Low, when PHP 8.1 minimum)
  - Convert to native `enum` type
  - More type-safe than string constants
  - Better IDE support
  - Potential breaking change for string usage

### Migration Notes

**When upgrading to PHP 8.1+:**
```php
// Before (PHP 8.0)
$policy = new DeletedRecord(DeletedRecord::PERSISTENT);
$value = $policy->getValue(); // string

// After (PHP 8.1+ with enum)
$policy = DeletedRecord::PERSISTENT; // Already an enum case
$value = $policy->value; // enum property
```

---

## 9. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | DeletedRecord | Granularity | ProtocolVersion |
|--------|---------------|-------------|-----------------|
| Type | Enumeration (3 values) | Enumeration (2 values) | Single value ('2.0') |
| Validation | Whitelist | Whitelist | Exact match |
| Constants | ✅ YES | ✅ YES | ✅ YES |
| Immutability | ✅ Private property | ✅ Private property | ✅ Private property |
| Equality | String comparison | String comparison | String comparison |
| Error handling | Lists allowed values | Lists allowed values | Shows expected value |

### Why DeletedRecord vs. Simple String?

**Q: Why not just use a string?**
- Type safety: `DeletedRecord` is more specific than `string`
- Validation guarantee: Any `DeletedRecord` instance is valid
- Domain clarity: Intent is clear in method signatures
- Prevents typos: Constants guide usage
- Self-documenting: Shows allowed values

**Q: Why not use a boolean?**
- Three states, not two (no/transient/persistent)
- Semantic meaning is clearer with strings
- Matches OAI-PMH specification exactly
- Boolean would lose the "transient" concept

---

## 10. Recommendations

### For Developers Using DeletedRecord VO

**DO:**
- ✅ Use constants: `DeletedRecord::PERSISTENT`
- ✅ Validate early when receiving external input
- ✅ Trust that any DeletedRecord instance is valid
- ✅ Use value equality (`equals()`) for comparisons

```php
// ✅ Good: Using constants
public function __construct() {
    $this->policy = new DeletedRecord(DeletedRecord::PERSISTENT);
}

// ✅ Good: Early validation from config
public function loadFromConfig(array $config) {
    $this->policy = new DeletedRecord($config['deleted_record']); // Validates immediately
}
```

**DON'T:**
- ❌ Don't use raw strings without constants
- ❌ Don't re-validate DeletedRecord instances
- ❌ Don't use identity (===) for equality checks
- ❌ Don't assume case-insensitivity

```php
// ❌ Bad: Magic strings (typo-prone)
$policy = new DeletedRecord('persistant'); // Typo! Will throw exception

// ✅ Good: Constant (autocomplete, typo-safe)
$policy = new DeletedRecord(DeletedRecord::PERSISTENT);

// ❌ Bad: Case mismatch
$policy = new DeletedRecord('No'); // Exception!

// ✅ Good: Exact case or constant
$policy = new DeletedRecord(DeletedRecord::NO);
```

### For Repository Administrators

**Choosing the Right Policy:**

- **Use `NO`** when:
  - ✅ Repository is append-only
  - ✅ Records are never deleted
  - ✅ Simple use case, minimal harvester requirements

- **Use `TRANSIENT`** when:
  - ✅ Deletion info is maintained temporarily
  - ✅ Storage constraints limit deletion history
  - ✅ Harvesters should check periodically for deletions
  - ⚠️ No guarantee of complete deletion list

- **Use `PERSISTENT`** when:
  - ✅ Deletion tracking is critical
  - ✅ Harvesters need reliable synchronization
  - ✅ Compliance or audit requirements exist
  - ✅ Production system with proper infrastructure

**Best Practices:**
- ✅ Choose policy based on actual capabilities
- ✅ Don't over-promise (`persistent` requires infrastructure)
- ✅ Document policy in repository metadata
- ✅ Keep policy stable (don't change frequently)

### For Library Maintainers

- ✅ Keep validation strict (case-sensitive)
- ✅ Maintain constant naming consistency
- ✅ Consider PHP 8.1 enum migration path
- ✅ Document migration strategy for breaking changes

---

## 11. References

### Specifications
- [OAI-PMH 2.0 Specification - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [OAI-PMH 2.0 Specification - Deleted Records](http://www.openarchives.org/OAI/openarchivesprotocol.html#DeletedRecords)

### Related Analysis Documents
- [docs/GRANULARITY_ANALYSIS.md](GRANULARITY_ANALYSIS.md) - Similar enumeration pattern
- [docs/PROTOCOLVERSION_ANALYSIS.md](PROTOCOLVERSION_ANALYSIS.md) - Single-value enumeration
- [docs/BASEURL_ANALYSIS.md](BASEURL_ANALYSIS.md) - Part of Identify response

### Related GitHub Issues
- Issue #8: PHP 8.2 readonly property migration
- Issue #10: Define repository identity value object

### External Resources
- [PHP 8.1 Enumerations](https://www.php.net/manual/en/language.enumerations.php)

---

## 12. Appendix

### Complete Test Output

```
Deleted Record (OaiPmh\Tests\Domain\ValueObject\DeletedRecord)
 ✔ Can instantiate with allowed values
 ✔ Throws exception for invalid value
 ✔ Equals returns true for same value
 ✔ Equals returns false for different value
 ✔ To string returns expected format
 ✔ Is immutable

Time: 00:00.109, Memory: 10.00 MB
OK (6 tests, 11 assertions)
```

### Code Coverage Report

```
DeletedRecord.php
  Lines: 100.00% (17/17)
  Methods: 100.00% (5/5)
  Classes: 100.00% (1/1)
```

### PHPStan Analysis

```
PHPStan Level: 8 (max)
Errors: 0
```

### PHP CodeSniffer Results

```
PSR-12 Compliance: 100%
Errors: 0
Warnings: 0
```

### OAI-PMH Identify Response Example

```xml
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/">
  <responseDate>2026-02-07T12:00:00Z</responseDate>
  <request verb="Identify">http://repository.example.org/oai</request>
  <Identify>
    <repositoryName>University Digital Library</repositoryName>
    <baseURL>http://repository.example.org/oai</baseURL>
    <protocolVersion>2.0</protocolVersion>
    <adminEmail>admin@example.org</adminEmail>
    <earliestDatestamp>2000-01-01T00:00:00Z</earliestDatestamp>
    <deletedRecord>persistent</deletedRecord>
    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
  </Identify>
</OAI-PMH>
```

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*  
*Author: Paul Slits <paul.slits@gmail.com>*
