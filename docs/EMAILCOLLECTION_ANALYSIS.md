# EmailCollection Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** EmailCollection Value Object  
**File:** `src/Domain/ValueObject/EmailCollection.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 3.1.1.2 (Identify):

> **adminEmail** - the e-mail address of an administrator of the repository.  
> **This element may be repeated.**

### Key Requirements

- ✅ At least one admin email required
- ✅ Multiple admin emails allowed
- ✅ Each email must be unique
- ✅ Order doesn't matter for equality (set semantics)
- ✅ Collection must be non-empty

### XML Example

```xml
<Identify>
  <repositoryName>DSpace at My University</repositoryName>
  <baseURL>http://www.example.org/oai</baseURL>
  <adminEmail>admin@example.org</adminEmail>
  <adminEmail>technical@example.org</adminEmail>
  <adminEmail>oai-support@example.org</adminEmail>
</Identify>
```

### Use Cases

| Scenario | Number of Emails | Purpose |
|----------|------------------|---------|
| Small repository | 1 | Single admin contact |
| Medium repository | 2-3 | Admin + technical support |
| Large repository | 3-5 | Multiple contacts for redundancy |
| Enterprise | 5+ | Different teams/departments |

---

## 2. User Story

**As a** repository administrator  
**When** I configure administrative contacts for my OAI-PMH repository  
**Where** multiple people may need to be contacted  
**I want** to provide a collection of valid, unique email addresses  
**Because** harvesters need reliable contact methods for technical issues  

### Acceptance Criteria

- [x] Collection must contain at least one email
- [x] All emails must be unique (no duplicates)
- [x] Collection must be immutable after creation
- [x] Must be iterable (foreach support)
- [x] Must be countable
- [x] Equality is order-insensitive (set semantics)
- [x] Can convert to array

---

## 3. Implementation Details

### File Structure
```
src/Domain/ValueObject/EmailCollection.php (102 lines)
tests/Domain/ValueObject/EmailCollectionTest.php
```

### Class Structure

```php
final class EmailCollection implements IteratorAggregate, Countable
{
    private array $emails = [];
    
    public function __construct(Email ...$emails)
    public function getIterator(): ArrayIterator
    public function count(): int
    public function toArray(): array
    public function equals(self $other): bool
    public function __toString(): string
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Non-empty** | Constructor validates at least 1 email | Spec requirement | ✅ |
| **Uniqueness** | Checks for duplicates on construction | Prevents redundancy | ✅ |
| **Immutability** | No add/remove methods | Value object pattern | ✅ |
| **Iterable** | Implements `IteratorAggregate` | Foreach support | ✅ |
| **Countable** | Implements `Countable` | count() support | ✅ |
| **Order-insensitive equality** | Set semantics | Logical equality | ✅ |

### Validation Logic

```php
public function __construct(Email ...$emails)
{
    if (empty($emails)) {
        throw new InvalidArgumentException('EmailCollection cannot be empty.');
    }

    foreach ($emails as $email) {
        if (in_array($email, $this->emails, true)) {
            throw new InvalidArgumentException(
                'EmailCollection cannot contain duplicate emails.'
            );
        }
        $this->emails[] = $email;
    }
}
```

**Validation Strategy:**
- Varadic parameter: accepts variable number of `Email` objects
- Empty check: at least one email required
- Duplicate check: uses strict comparison (`in_array` with `true`)
- Type-safe: only `Email` objects accepted (PHP type system)

### Relationship to Other Components

```
EmailCollection
  │
  ├──> Contains 1..* Email objects
  │
  └──> Used in Repository Identity
       └──> Required for OAI-PMH Identify response
            └──> adminEmail (repeatable element)
```

---

## 4. Test Coverage Analysis

### Test Statistics

- **Total Tests:** 9
- **Assertions:** 12
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

### Test Categories

- ✅ **Constructor validation** (3 tests)
  - Valid collection with emails
  - Empty collection rejection
  - Duplicate email rejection
  
- ✅ **Iteration** (1 test)
  - Foreach iteration support
  
- ✅ **Equality** (3 tests)
  - Same emails, same order
  - Same emails, different order (order-insensitive)
  - Different emails
  
- ✅ **String representation** (1 test)
  
- ✅ **Immutability** (1 test)

### Test Quality

**Strengths:**
- ✅ BDD-style Given-When-Then structure
- ✅ User story comments
- ✅ Tests both valid and invalid inputs
- ✅ Tests order-insensitive equality
- ✅ 100% code coverage

---

## 5. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;

// Create collection with multiple emails
$emails = new EmailCollection(
    new Email('admin@example.org'),
    new Email('technical@example.org'),
    new Email('support@example.org')
);

// Count emails
echo count($emails); // 3

// Iterate over emails
foreach ($emails as $email) {
    echo $email->getValue() . PHP_EOL;
}
```

### Validation Examples

```php
// ✅ Valid: Single email
$single = new EmailCollection(
    new Email('admin@example.org')
);

// ✅ Valid: Multiple unique emails
$multiple = new EmailCollection(
    new Email('admin@example.org'),
    new Email('tech@example.org')
);

// ❌ Invalid: Empty collection (throws exception)
$empty = new EmailCollection(); // Exception!

// ❌ Invalid: Duplicate emails (throws exception)
$adminEmail = new Email('admin@example.org');
$duplicates = new EmailCollection($adminEmail, $adminEmail); // Exception!
```

### Order-Insensitive Equality

```php
$collection1 = new EmailCollection(
    new Email('admin@example.org'),
    new Email('tech@example.org')
);

$collection2 = new EmailCollection(
    new Email('tech@example.org'),
    new Email('admin@example.org')
);

// Order doesn't matter for equality
var_dump($collection1->equals($collection2)); // true
```

### Real-World Integration

```php
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\RepositoryName;

// Repository configuration
class RepositoryConfig
{
    private EmailCollection $adminEmails;
    
    public function __construct(array $emailStrings)
    {
        // Convert strings to Email objects
        $emails = array_map(
            fn(string $email) => new Email($email),
            $emailStrings
        );
        
        // Create collection (validates uniqueness and non-empty)
        $this->adminEmails = new EmailCollection(...$emails);
    }
    
    public function getAdminEmails(): EmailCollection
    {
        return $this->adminEmails;
    }
}

// Usage
$config = new RepositoryConfig([
    'admin@university.edu',
    'technical@university.edu',
    'oai-support@university.edu'
]);

// Build Identify response
$identify = [
    'baseURL' => new BaseURL('https://repository.university.edu/oai'),
    'repositoryName' => new RepositoryName('University Digital Library'),
    'adminEmail' => $config->getAdminEmails(),
    // ... other fields
];
```

### Converting to Array

```php
$emails = new EmailCollection(
    new Email('admin@example.org'),
    new Email('tech@example.org')
);

// Get array of Email objects
$array = $emails->toArray();
// $array = [Email(...), Email(...)]

// Get array of email strings
$strings = array_map(
    fn(Email $email) => $email->getValue(),
    $emails->toArray()
);
// $strings = ['admin@example.org', 'tech@example.org']
```

---

## 6. Design Decisions

### Decision 1: Order-Insensitive Equality (Set Semantics)

**Context:** How should two collections be compared?

**Options Considered:**
1. Order-sensitive (list semantics) - collections equal only if same order
2. Order-insensitive (set semantics) - chosen

**Rationale:**
- Email order has no semantic meaning in OAI-PMH
- Makes sense as a "set" of contacts
- More flexible for users
- Aligns with XML representation (order not guaranteed)

**Implementation:**
```php
public function equals(self $other): bool
{
    if ($this->count() !== $other->count()) {
        return false;
    }
    
    foreach ($this->emails as $email) {
        $found = false;
        foreach ($other->emails as $otherEmail) {
            if ($email->equals($otherEmail)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return false;
        }
    }
    return true;
}
```

**Trade-offs:**
- ✅ More logical for email sets
- ✅ User-friendly
- ⚠️ Slightly more complex equality check (O(n²))
- ✅ Performance acceptable for small collections

### Decision 2: Require At Least One Email

**Context:** Should empty collections be allowed?

**Options Considered:**
1. Allow empty collections
2. Require at least one email (chosen)

**Rationale:**
- OAI-PMH spec requires at least one adminEmail
- Fail-fast validation
- Prevents misconfiguration
- Type guarantee: any EmailCollection has emails

**Trade-offs:**
- ✅ Specification compliance
- ✅ Prevents errors
- ✅ Type safety
- ❌ Cannot represent "no emails" state (not needed)

### Decision 3: Prevent Duplicates

**Context:** Should duplicate emails be allowed?

**Options Considered:**
1. Allow duplicates
2. Silently de-duplicate
3. Reject duplicates with exception (chosen)

**Rationale:**
- Duplicates are likely user error
- Explicit error helps debugging
- Clear feedback about the problem
- No ambiguity about intent

**Trade-offs:**
- ✅ Clear error feedback
- ✅ Prevents mistakes
- ⚠️ User must ensure uniqueness
- ✅ Fail-fast pattern

### Decision 4: Use Variadic Constructor

**Context:** How to accept multiple emails?

**Options Considered:**
1. Array parameter: `__construct(array $emails)`
2. Variadic parameter: `__construct(Email ...$emails)` (chosen)

**Rationale:**
- Type-safe: only accepts `Email` objects
- Clean syntax: `new EmailCollection($email1, $email2)`
- IDE-friendly: better autocomplete
- No need for manual type checking

**Trade-offs:**
- ✅ Type-safe
- ✅ Clean syntax
- ⚠️ Need to use spread operator for arrays: `new EmailCollection(...$emailArray)`
- ✅ Worth it for type safety

---

## 7. Known Issues & Future Enhancements

### Current Known Issues

None

### Future Enhancements

- [ ] **Issue #8**: Migrate to PHP 8.2 `readonly` properties (Priority: Low)
  ```php
  public readonly array $emails;
  ```

- [ ] **Performance Optimization** (Priority: Very Low)
  - For very large collections, consider sorted array for O(n log n) equality
  - Not needed for typical use (2-5 emails)

- [ ] **Named Constructor** (Priority: Low)
  ```php
  public static function fromStrings(string ...$emails): self
  {
      return new self(...array_map(
          fn($e) => new Email($e),
          $emails
      ));
  }
  ```

---

## 8. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | EmailCollection | DescriptionCollection | MetadataNamespaceCollection |
|--------|-----------------|----------------------|----------------------------|
| Minimum size | 1 (non-empty) | 0 (can be empty) | 1 (non-empty) |
| Duplicates | ❌ Not allowed | ❌ Not allowed | ❌ Not allowed |
| Equality | Order-insensitive | Order-sensitive | Order-sensitive |
| Implements | Countable, IteratorAggregate | Countable, IteratorAggregate | Countable, IteratorAggregate |

### Why EmailCollection Specifically?

**Q: Why not just use array of Email objects?**
- Type safety: `EmailCollection` is more specific than `array`
- Validation: Guarantees non-empty and unique
- Domain clarity: Clear intent
- Methods: `count()`, `equals()`, iteration

**Q: Why different from DescriptionCollection?**
- Different domain rules:
  - Emails: Must have at least 1, order doesn't matter
  - Descriptions: Can be empty (0..* in spec), order matters
- Each collection reflects its domain requirements

---

## 9. Recommendations

### For Developers Using EmailCollection VO

**DO:**
- ✅ Create from validated Email objects
- ✅ Use variadic syntax for clean code
- ✅ Trust that any EmailCollection is valid and non-empty
- ✅ Use foreach for iteration

```php
// ✅ Good: Clean variadic syntax
$emails = new EmailCollection(
    new Email('admin@example.org'),
    new Email('tech@example.org')
);

// ✅ Good: From array with spread operator
$emailObjects = array_map(fn($e) => new Email($e), $strings);
$emails = new EmailCollection(...$emailObjects);
```

**DON'T:**
- ❌ Don't try to create empty collections
- ❌ Don't add duplicate emails
- ❌ Don't rely on email order

```php
// ❌ Bad: Empty collection
$emails = new EmailCollection(); // Exception!

// ❌ Bad: Duplicates
$email = new Email('admin@example.org');
$emails = new EmailCollection($email, $email); // Exception!
```

### For Repository Administrators

- ✅ Provide at least one admin email
- ✅ Use role-based addresses (admin@, oai@) not personal
- ✅ Include multiple contacts for redundancy
- ✅ Keep email addresses current and monitored
- ✅ Consider including:
  - Administrative contact
  - Technical support
  - OAI-specific contact

**Example Good Configuration:**
```php
new EmailCollection(
    new Email('oai-admin@university.edu'),      // Primary OAI contact
    new Email('library-tech@university.edu'),    // Technical support
    new Email('digital-library@university.edu')  // General admin
);
```

### For Library Maintainers

- ✅ Keep order-insensitive equality
- ✅ Maintain non-empty requirement
- ✅ Consider named constructor for convenience
- ✅ Document duplicate prevention clearly

---

## 10. References

### Specifications
- [OAI-PMH 2.0 - Identify Response](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

### Related Analysis Documents
- [docs/EMAIL_ANALYSIS.md](EMAIL_ANALYSIS.md) - Email value object
- [docs/DESCRIPTIONCOLLECTION_ANALYSIS.md](DESCRIPTIONCOLLECTION_ANALYSIS.md) - Similar collection pattern
- [docs/BASEURL_ANALYSIS.md](BASEURL_ANALYSIS.md) - Part of Identify response

### Related GitHub Issues
- Issue #8: PHP 8.2 readonly property migration
- Issue #10: Define repository identity value object

---

## 11. Appendix

### Test Output

```
Email Collection (OaiPmh\Tests\Domain\ValueObject\EmailCollection)
 ✔ Can instantiate with emails
 ✔ Throws exception for empty collection
 ✔ Throws exception for duplicate emails
 ✔ Can iterate over collection
 ✔ Equals returns true for same emails and order
 ✔ Equals returns true for different order
 ✔ Equals returns false for different emails
 ✔ To string returns expected format
 ✔ Collection is immutable

OK (9 tests, 12 assertions)
```

### Coverage Report

```
EmailCollection.php
  Lines: 100.00% (27/27)
  Methods: 100.00% (6/6)
  Classes: 100.00% (1/1)
```

### PHPStan & CodeSniffer

```
PHPStan Level: 8 (max)
Errors: 0

PSR-12 Compliance: 100%
Errors: 0
Warnings: 0
```

### OAI-PMH XML Example

```xml
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/">
  <responseDate>2026-02-07T12:00:00Z</responseDate>
  <request verb="Identify">http://repository.example.org/oai</request>
  <Identify>
    <repositoryName>University Digital Library</repositoryName>
    <baseURL>http://repository.example.org/oai</baseURL>
    <protocolVersion>2.0</protocolVersion>
    <adminEmail>oai-admin@university.edu</adminEmail>
    <adminEmail>library-tech@university.edu</adminEmail>
    <adminEmail>digital-library@university.edu</adminEmail>
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
