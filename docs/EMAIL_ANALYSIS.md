# Email Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** Email Value Object  
**File:** `src/Domain/ValueObject/Email.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Identify Response](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 3.1.1.2 (Identify):

> **adminEmail** - the e-mail address of an administrator of the repository. 
> This element may be repeated. The value must be a valid email address.

### Key Requirements

- ✅ Must be a valid email address format
- ✅ Required in Identify response (at least one)
- ✅ Can have multiple admin emails (use EmailCollection)
- ✅ Standard RFC 5322 email validation

### XML Example

```xml
<Identify>
  <repositoryName>DSpace at My University</repositoryName>
  <baseURL>http://www.example.org/oai</baseURL>
  <protocolVersion>2.0</protocolVersion>
  <adminEmail>admin@example.org</adminEmail>
  <adminEmail>technical@example.org</adminEmail>
  <earliestDatestamp>2000-01-01T00:00:00Z</earliestDatestamp>
  <deletedRecord>no</deletedRecord>
  <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
</Identify>
```

### Common Patterns

| Pattern | Valid | Example |
|---------|-------|---------|
| Simple email | ✅ | `admin@example.org` |
| With subdomain | ✅ | `oai@lib.university.edu` |
| With plus addressing | ✅ | `admin+oai@example.org` |
| With numbers | ✅ | `admin123@example.org` |
| With hyphens | ✅ | `oai-admin@example.org` |
| Without @ symbol | ❌ | `adminexample.org` |
| Without domain | ❌ | `admin@` |
| With spaces | ❌ | `admin @example.org` |

---

## 2. User Story

**As a** repository administrator  
**When** I configure my OAI-PMH repository  
**Where** I need to specify administrative contact information  
**I want** to provide one or more valid email addresses  
**Because** harvesters need to contact administrators for technical issues or policy questions  

### Acceptance Criteria

- [x] Email must be validated using standard email validation rules
- [x] Invalid email addresses must be rejected with clear error messages
- [x] Email value object must be immutable after creation
- [x] Two emails with the same address must be considered equal
- [x] Email must have a human-readable string representation
- [x] Email must be usable in collections (EmailCollection)

---

## 3. Implementation Details

### File Structure

```
src/Domain/ValueObject/Email.php
tests/Domain/ValueObject/EmailTest.php
```

### Class Structure

```php
final class Email
{
    private string $email;
    
    public function __construct(string $email)
    public function getValue(): string
    public function equals(self $other): bool
    public function __toString(): string
    private function validateEmail(string $email): void
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Immutability** | `final` class, `private` property | Required for value objects | ✅ |
| **Validation** | `filter_var($email, FILTER_VALIDATE_EMAIL)` | RFC 5322 compliance | ✅ |
| **Value Equality** | `equals()` method compares email strings | Domain requirement | ✅ |
| **Type Safety** | Strict types, no mixed | Modern PHP best practices | ✅ |
| **Error Handling** | `InvalidArgumentException` with context | Clear feedback | ✅ |

### Validation Logic

```php
private function validateEmail(string $email): void
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException(
            sprintf('Invalid email address: %s', $email)
        );
    }
}
```

**Validation Strategy:**
- Uses PHP's built-in `filter_var()` with `FILTER_VALIDATE_EMAIL`
- Validates against RFC 5322 standard
- Single point of validation (fail-fast pattern)
- Descriptive error messages

### Relationship to Other Components

```
Email
  │
  ├──> Used by EmailCollection (1..* emails)
  │
  └──> Part of Repository Identity (Identify response)
       └──> Required for OAI-PMH Identify verb
```

---

## 4. Acceptance Criteria

### Functional Requirements

| Requirement | Test Coverage | Status |
|-------------|---------------|--------|
| Accept valid email addresses | `testCanInstantiateWithValidEmail` | ✅ PASS |
| Reject invalid email formats | `testThrowsExceptionForInvalidEmail` | ✅ PASS |
| Value equality for same email | `testEqualsReturnsTrueForSameValue` | ✅ PASS |
| Value inequality for different emails | `testEqualsReturnsFalseForDifferentValue` | ✅ PASS |
| String representation | `testToStringReturnsExpectedFormat` | ✅ PASS |
| Immutability | `testIsImmutable` | ✅ PASS |

### OAI-PMH Protocol Compliance

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Valid email format | RFC 5322 via `filter_var()` | ✅ PASS |
| Immutable value | No setters, private properties | ✅ PASS |
| String representation | `__toString()` method | ✅ PASS |
| Domain purity | No XML/HTTP concerns | ✅ PASS |

### Non-Functional Requirements

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Performance | O(1) validation | ✅ PASS |
| Memory efficiency | Single string property | ✅ PASS |
| Type safety | Strict types throughout | ✅ PASS |
| Error clarity | Descriptive exception messages | ✅ PASS |

---

## 5. Test Coverage Analysis

### Test Statistics

- **Total Tests:** 6
- **Assertions:** 7
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

### Test Categories

- ✅ **Constructor validation** (2 tests)
  - `testCanInstantiateWithValidEmail`
  - `testThrowsExceptionForInvalidEmail`
  
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
- ✅ Descriptive test method names
- ✅ Comprehensive assertions
- ✅ 100% code coverage
- ✅ Tests both valid and invalid inputs

**Coverage Gaps:** None identified

---

## 6. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\Email;

// Create a valid email
$email = new Email('admin@example.org');

// Get the email address
echo $email->getValue(); // admin@example.org

// String representation
echo $email; // Email(email: admin@example.org)
```

### Validation Examples

```php
// ✅ Valid emails
new Email('admin@example.org');
new Email('oai-admin@lib.university.edu');
new Email('admin+oai@example.org');
new Email('admin123@example.org');

// ❌ Invalid emails (throw InvalidArgumentException)
new Email('not-an-email');        // Missing @
new Email('admin@');              // Missing domain
new Email('@example.org');        // Missing local part
new Email('admin @example.org');  // Contains spaces
```

### Equality Comparison

```php
$email1 = new Email('admin@example.org');
$email2 = new Email('admin@example.org');
$email3 = new Email('other@example.org');

var_dump($email1->equals($email2)); // true
var_dump($email1->equals($email3)); // false
```

### Integration with EmailCollection

```php
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;

// Create multiple admin emails
$emails = new EmailCollection(
    new Email('admin@example.org'),
    new Email('technical@example.org'),
    new Email('oai-support@example.org')
);

// Iterate over emails
foreach ($emails as $email) {
    echo $email->getValue() . PHP_EOL;
}
```

---

## 7. Design Decisions

### Decision 1: Use PHP's `filter_var()` for Validation

**Context:** Need to validate email addresses according to standards

**Options Considered:**
1. Regular expression validation
2. PHP's `filter_var(FILTER_VALIDATE_EMAIL)`
3. Third-party library (e.g., egulias/email-validator)

**Rationale:** Chose `filter_var()` because:
- Built into PHP (no external dependencies)
- Validates against RFC 5322 standard
- Well-tested and maintained
- Sufficient for OAI-PMH use case
- Simple and performant

**Trade-offs:**
- ✅ No external dependencies
- ✅ Standard PHP approach
- ✅ Good enough for 99% of cases
- ⚠️ Not 100% RFC-compliant (accepts some edge cases)
- ❌ Cannot validate deliverability (but that's not required)

**Code Example:**
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new InvalidArgumentException(
        sprintf('Invalid email address: %s', $email)
    );
}
```

### Decision 2: Immutable Value Object Pattern

**Context:** Email addresses should not change after creation

**Options Considered:**
1. Mutable object with setter methods
2. Immutable value object (chosen)

**Rationale:**
- Aligns with Domain-Driven Design principles
- Prevents accidental modification
- Thread-safe (if applicable)
- Consistent with other value objects in library
- Simplifies reasoning about code

**Trade-offs:**
- ✅ Prevents bugs from mutation
- ✅ Easier to reason about
- ✅ Can be used as array/map keys
- ❌ Must create new instance to "change" value (intended)

### Decision 3: Simple String Storage

**Context:** How to internally store the email address

**Options Considered:**
1. Store as simple string (chosen)
2. Parse into local-part and domain components
3. Store as object with parts

**Rationale:**
- Email is used as a whole in OAI-PMH
- No need to access parts separately
- Simpler implementation
- Lower memory footprint

**Trade-offs:**
- ✅ Simple and efficient
- ✅ Sufficient for use case
- ❌ Cannot access parts (not needed)

---

## 8. Known Issues & Future Enhancements

### Current Known Issues

None

### Future Enhancements

- [ ] **Issue #8**: Migrate to PHP 8.2 `readonly` properties (Priority: Low)
  ```php
  // Current (PHP 8.0)
  private string $email;
  
  // Future (PHP 8.2)
  public readonly string $email;
  ```

### Potential Improvements

- **Enhanced Validation** (Priority: Very Low)
  - Could use egulias/email-validator for stricter RFC compliance
  - Not needed unless specification requires it
  - Current validation is sufficient for OAI-PMH

- **Email Normalization** (Priority: Very Low)
  - Could normalize email addresses (lowercase domain)
  - Specification doesn't require it
  - Risk of changing user intent

---

## 9. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | Email | BaseURL | RepositoryName |
|--------|-------|---------|----------------|
| Validation | `filter_var()` | `filter_var()` + scheme check | `trim()` check |
| Immutability | ✅ Private property | ✅ Private property | ✅ Private property |
| Equality | String comparison | String comparison | String comparison |
| Error handling | `InvalidArgumentException` | `InvalidArgumentException` | `InvalidArgumentException` |
| String representation | `Email(email: ...)` | `BaseURL(url: ...)` | `RepositoryName(name: ...)` |

### Why Email vs. Reusing Existing?

**Q: Why not just use a string?**
- Type safety: `Email` is more specific than `string`
- Validation guarantee: Any `Email` instance is valid
- Domain clarity: Intent is clear in method signatures
- Consistent patterns: Matches other value objects

**Q: Why not use AnyUri?**
- Email is not a URI (mailto: would be)
- Different validation rules
- Different domain concept
- Email is atomic, not a resource locator

---

## 10. Recommendations

### For Developers Using Email VO

**DO:**
- ✅ Create Email instances early in your workflow
- ✅ Use EmailCollection for multiple emails
- ✅ Trust that any Email instance is valid
- ✅ Use value equality (`equals()`) for comparisons

```php
// ✅ Good: Validate early
public function __construct(string $emailString) {
    $this->adminEmail = new Email($emailString); // Throws if invalid
}
```

**DON'T:**
- ❌ Don't pass strings around and validate late
- ❌ Don't re-validate Email instances (already valid)
- ❌ Don't use identity (===) for equality checks

```php
// ❌ Bad: Late validation, error far from source
public function sendNotification(string $email) {
    // ... much later ...
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Too late!
        throw new Exception();
    }
}

// ✅ Good: Type-safe, validated
public function sendNotification(Email $email) {
    // Email is guaranteed valid
    mail($email->getValue(), ...);
}
```

### For Repository Administrators

- ✅ Provide valid, monitored email addresses
- ✅ Use role-based addresses (admin@, oai@) rather than personal
- ✅ Ensure email addresses are reachable
- ✅ Consider multiple emails for redundancy

### For Library Maintainers

- ✅ Keep validation simple (filter_var is sufficient)
- ✅ Maintain immutability pattern
- ✅ Document any validation changes
- ✅ Consider PHP 8.2 migration for `readonly` properties

---

## 11. References

### Specifications
- [OAI-PMH 2.0 Specification - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [RFC 5322 - Internet Message Format](https://www.rfc-editor.org/rfc/rfc5322)
- [PHP filter_var() Documentation](https://www.php.net/manual/en/function.filter-var.php)

### Related Analysis Documents
- [docs/EMAILCOLLECTION_ANALYSIS.md](EMAILCOLLECTION_ANALYSIS.md) - Collection of Email objects
- [docs/BASEURL_ANALYSIS.md](BASEURL_ANALYSIS.md) - Similar validation pattern
- [docs/REPOSITORYNAME_ANALYSIS.md](REPOSITORYNAME_ANALYSIS.md) - Part of Identify response

### Related GitHub Issues
- Issue #8: PHP 8.2 readonly property migration
- Issue #10: Define repository identity value object

---

## 12. Appendix

### Complete Test Output

```
Email (OaiPmh\Tests\Domain\ValueObject\Email)
 ✔ Can instantiate with valid email
 ✔ Throws exception for invalid email
 ✔ Equals returns true for same value
 ✔ Equals returns false for different value
 ✔ To string returns expected format
 ✔ Is immutable

Time: 00:00.114, Memory: 10.00 MB
OK (6 tests, 7 assertions)
```

### Code Coverage Report

```
Email.php
  Lines: 100.00% (14/14)
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

### Real-World Example

```php
// Example from a real repository configuration
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;

class RepositoryConfig
{
    private EmailCollection $adminEmails;
    
    public function __construct(array $emailStrings)
    {
        $emails = array_map(
            fn(string $email) => new Email($email),
            $emailStrings
        );
        
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
    'oai-support@library.university.edu',
    'technical.services@university.edu'
]);
```

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*  
*Author: Paul Slits <paul.slits@gmail.com>*
