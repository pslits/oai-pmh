# Repository Identity Value Objects Analysis

**Analysis Date:** February 7, 2026  
**Component:** BaseURL and RepositoryName  
**Branch:** `10-define-repository-identity-value-object`  
**Status:** ✅ Completed  

---

## Executive Summary

This analysis covers the implementation of **BaseURL** and **RepositoryName** value objects, which are essential components for the OAI-PMH `Identify` response. These value objects complete the Repository Identity domain model, allowing repositories to declare their base URL and human-readable name in a type-safe, validated manner.

---

## Value Object Overview

### Purpose

**BaseURL** and **RepositoryName** are foundational value objects that represent:
- **BaseURL**: The HTTP/HTTPS endpoint where the OAI-PMH repository accepts requests
- **RepositoryName**: A human-readable, descriptive name for the repository

Together with existing value objects (Email, UTCdatetime, DeletedRecord, Granularity, ProtocolVersion, Description), they complete the data needed for the `Identify` verb response in OAI-PMH 2.0.

### OAI-PMH Context

According to the [OAI-PMH 2.0 Specification](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify), the `Identify` verb requires:

| Element | Cardinality | OAI-PMH Definition | Implementation |
|---------|-------------|-------------------|----------------|
| baseURL | 1 | The base URL of the repository | **BaseURL** VO |
| repositoryName | 1 | A human-readable name for the repository | **RepositoryName** VO |
| adminEmail | 1..* | Email address(es) of repository administrators | Email + EmailCollection VOs |
| earliestDatestamp | 1 | Guaranteed lower limit on all datestamps | UTCdatetime VO |
| deletedRecord | 1 | Deleted record support policy | DeletedRecord VO |
| granularity | 1 | Finest temporal granularity supported | Granularity VO |
| protocolVersion | 1..* | OAI-PMH protocol version(s) | ProtocolVersion VO |
| description | 0..* | Optional container for repository descriptions | Description + DescriptionCollection VOs |

### Key Characteristics

**BaseURL:**
- Validates URL format using PHP's `filter_var` with `FILTER_VALIDATE_URL`
- Enforces HTTP or HTTPS protocol only (no FTP, file://, etc.)
- Supports query parameters, custom ports, and paths
- Immutable after creation
- Value-based equality

**RepositoryName:**
- Accepts any non-empty string after trimming whitespace
- Full Unicode support for international repository names
- Preserves original input (including leading/trailing spaces)
- Validation uses trimmed value to prevent whitespace-only names
- Immutable after creation
- Value-based equality

---

## Implementation

### File Structure

```
src/Domain/ValueObject/
├── BaseURL.php (113 lines)
└── RepositoryName.php (93 lines)

tests/Domain/ValueObject/
├── BaseURLTest.php (300 lines, 14 tests)
└── RepositoryNameTest.php (302 lines, 14 tests)
```

### BaseURL Class Design

- **Namespace:** `OaiPmh\Domain\ValueObject`
- **Type:** `final class`
- **Dependencies:** None (uses native PHP functions)

#### Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| `$url` | `string` | `private` | The validated base URL |

#### Methods

| Method | Parameters | Return | Purpose |
|--------|------------|--------|---------|
| `__construct()` | `string $url` | `void` | Initialize and validate URL |
| `getValue()` | none | `string` | Get the base URL |
| `equals()` | `BaseURL $other` | `bool` | Value equality comparison |
| `__toString()` | none | `string` | String representation |
| `validateUrl()` | `string $url` | `void` | Private validation method |

#### Validation Rules

1. **Non-empty**: URL cannot be an empty string
2. **Valid URL format**: Must pass `filter_var($url, FILTER_VALIDATE_URL)`
3. **HTTP/HTTPS only**: Scheme must be 'http' or 'https' (case-insensitive)
4. **Parseable scheme**: `parse_url()` must successfully extract scheme

### RepositoryName Class Design

- **Namespace:** `OaiPmh\Domain\ValueObject`
- **Type:** `final class`
- **Dependencies:** None (uses native PHP functions)

#### Properties

| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| `$name` | `string` | `private` | The repository name |

#### Methods

| Method | Parameters | Return | Purpose |
|--------|------------|--------|---------|
| `__construct()` | `string $name` | `void` | Initialize and validate name |
| `getValue()` | none | `string` | Get the repository name |
| `equals()` | `RepositoryName $other` | `bool` | Value equality comparison |
| `__toString()` | none | `string` | String representation |
| `validateName()` | `string $name` | `void` | Private validation method |

#### Validation Rules

1. **Non-empty after trim**: `trim($name)` cannot be empty
2. **Preserves original**: Original value (including spaces) is stored
3. **Unicode support**: No character restrictions, full UTF-8 support

---

## Design Decisions

### Decision 1: BaseURL Protocol Restriction

**Why:** Restrict to HTTP/HTTPS protocols only  
**Rationale:** 
- OAI-PMH is a web-based protocol requiring HTTP(S) transport
- Prevents misconfiguration with file://, ftp://, or other protocols
- Aligns with OAI-PMH specification expectations
- Simplifies client implementation (no protocol switching needed)

**Alternatives Considered:**
- Allow any valid URL → Rejected: Too permissive, allows non-web protocols
- Use `AnyUri` value object → Rejected: BaseURL has stricter requirements

**Trade-offs:**
- ✅ Prevents configuration errors
- ✅ Clear validation errors for users
- ❌ Slightly more complex validation logic

### Decision 2: RepositoryName Unicode Support

**Why:** Full Unicode character support without restrictions  
**Rationale:**
- OAI-PMH is international, used worldwide
- Repository names may contain accented characters, CJK characters, etc.
- No OAI-PMH specification restrictions on characters
- Modern PHP handles UTF-8 natively

**Alternatives Considered:**
- ASCII-only names → Rejected: Too restrictive for international use
- Pattern validation → Rejected: Unnecessary complexity, no specification requirement

**Trade-offs:**
- ✅ International repository support
- ✅ Flexibility for users
- ✅ Simple implementation
- ❌ Could contain unusual characters (but that's user's choice)

### Decision 3: Preserve Original Input (RepositoryName)

**Why:** Store original value including leading/trailing spaces  
**Rationale:**
- Validation uses trimmed value, but storage preserves original
- Respects user input
- Allows for intentional formatting if needed
- Consistent with principle of least surprise

**Alternatives Considered:**
- Store trimmed value → Rejected: Loses information, opinionated

**Trade-offs:**
- ✅ Preserves user intent
- ✅ No data loss
- ❌ Slightly more complex (validation vs. storage)

### Decision 4: BaseURL Defensive Check for parse_url

**Why:** Check if `parse_url()` returns `false` or `null`  
**Rationale:**
- Defense in depth: after `filter_var` validation, `parse_url` should succeed
- Explicit error for malformed URLs that pass filter_var
- Satisfies PHPStan level 8 type requirements
- Better error messages for debugging

**Alternatives Considered:**
- Trust filter_var completely → Rejected: Fails PHPStan analysis
- Use `@` suppression → Rejected: Hides potential issues

**Trade-offs:**
- ✅ Type-safe code (PHPStan compliant)
- ✅ Better error handling
- ❌ Unreachable code path in practice (similar to AnyUri issue #7)
- ❌ Affects code coverage (85% vs 100%)

---

## Code Examples

### Basic Usage - BaseURL

```php
use OaiPmh\Domain\ValueObject\BaseURL;

// Create a BaseURL for a repository
$baseUrl = new BaseURL('https://repository.example.org/oai-pmh');

// Get the URL value
echo $baseUrl->getValue(); 
// Output: https://repository.example.org/oai-pmh

// String representation
echo $baseUrl; 
// Output: BaseURL(url: https://repository.example.org/oai-pmh)

// Compare BaseURLs
$url1 = new BaseURL('https://example.org/oai');
$url2 = new BaseURL('https://example.org/oai');
$url3 = new BaseURL('https://other.org/oai');

var_dump($url1->equals($url2)); // true
var_dump($url1->equals($url3)); // false
```

### Basic Usage - RepositoryName

```php
use OaiPmh\Domain\ValueObject\RepositoryName;

// Create a repository name
$name = new RepositoryName('Digital Library Repository');

// Get the name value
echo $name->getValue();
// Output: Digital Library Repository

// String representation
echo $name;
// Output: RepositoryName(name: Digital Library Repository)

// Unicode support
$unicodeName = new RepositoryName('Bibliothèque Numérique 数字图书馆');
echo $unicodeName->getValue();
// Output: Bibliothèque Numérique 数字图书馆

// Compare names
$name1 = new RepositoryName('Digital Archive');
$name2 = new RepositoryName('Digital Archive');
$name3 = new RepositoryName('University Library');

var_dump($name1->equals($name2)); // true
var_dump($name1->equals($name3)); // false
```

### Advanced Usage - Identify Response Components

```php
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\RepositoryName;
use OaiPmh\Domain\ValueObject\Email;
use OaiPmh\Domain\ValueObject\EmailCollection;
use OaiPmh\Domain\ValueObject\UTCdatetime;
use OaiPmh\Domain\ValueObject\DeletedRecord;
use OaiPmh\Domain\ValueObject\Granularity;
use OaiPmh\Domain\ValueObject\ProtocolVersion;

// Build complete Identify response data
$identify = [
    'baseURL' => new BaseURL('https://repository.example.org/oai'),
    'repositoryName' => new RepositoryName('Example University Digital Archive'),
    'adminEmail' => new EmailCollection(
        new Email('admin@example.org'),
        new Email('oai-admin@example.org')
    ),
    'earliestDatestamp' => new UTCdatetime('2010-01-01'),
    'deletedRecord' => new DeletedRecord('persistent'),
    'granularity' => new Granularity('YYYY-MM-DDThh:mm:ssZ'),
    'protocolVersion' => new ProtocolVersion('2.0'),
];

// Access values
echo "Repository: " . $identify['repositoryName']->getValue();
echo "\nEndpoint: " . $identify['baseURL']->getValue();
echo "\nContact: " . $identify['adminEmail']->toArray()[0]->getValue();
echo "\nSince: " . $identify['earliestDatestamp']->getDateTime()->format('Y-m-d');
```

### Validation Examples

```php
use OaiPmh\Domain\ValueObject\BaseURL;
use OaiPmh\Domain\ValueObject\RepositoryName;

// BaseURL validation

// ✅ Valid URLs
new BaseURL('http://example.org/oai');
new BaseURL('https://example.org/oai-pmh');
new BaseURL('https://repository.example.org:8080/oai');
new BaseURL('https://example.org/oai?verb=Identify');
new BaseURL('https://example.org/repository/oai/');

// ❌ Invalid URLs (throw InvalidArgumentException)
new BaseURL('');                      // Empty URL
new BaseURL('not-a-url');            // Invalid format
new BaseURL('ftp://example.org');    // Wrong protocol
new BaseURL('file:///path/to/file'); // Wrong protocol

// RepositoryName validation

// ✅ Valid names
new RepositoryName('Digital Library');
new RepositoryName('University Archive - 2025');
new RepositoryName('Bibliothèque Numérique');
new RepositoryName('数字图书馆');
new RepositoryName('  Name with spaces  '); // Preserved as-is

// ❌ Invalid names (throw InvalidArgumentException)
new RepositoryName('');        // Empty
new RepositoryName('   ');     // Whitespace only
new RepositoryName("\t\n");    // Tabs/newlines only
```

---

## Test Coverage

### Statistics

**BaseURL:**
- **Total Tests:** 14
- **Assertions:** ~40
- **Coverage:** 85% lines, 80% methods
- **Status:** ✅ All passing

**RepositoryName:**
- **Total Tests:** 14
- **Assertions:** ~42
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

**Combined:**
- **Total Tests:** 28
- **Coverage:** Overall 96.93% (project-wide)
- **Status:** ✅ All passing, 0 errors, 0 warnings

### Test Categories - BaseURL

- ✅ Constructor validation - empty string (1 test)
- ✅ Constructor validation - invalid format (1 test)
- ✅ Constructor validation - wrong protocol (2 tests)
- ✅ Constructor success - HTTP (1 test)
- ✅ Constructor success - HTTPS (1 test)
- ✅ Constructor success - with parameters (3 tests)
- ✅ Value equality - same value (1 test)
- ✅ Value equality - different values (1 test)
- ✅ String representation (1 test)
- ✅ Immutability verification (1 test)

### Test Categories - RepositoryName

- ✅ Constructor validation - empty string (1 test)
- ✅ Constructor validation - whitespace only (2 tests)
- ✅ Constructor success - simple name (1 test)
- ✅ Constructor success - complex names (3 tests)
- ✅ Constructor success - Unicode (1 test)
- ✅ Constructor success - with spaces (1 test)
- ✅ Value equality - same value (1 test)
- ✅ Value equality - different values (1 test)
- ✅ String representation (1 test)
- ✅ Immutability verification (1 test)
- ✅ Edge cases - long name (1 test)
- ✅ Edge cases - with numbers (1 test)

### Test Quality

- ✅ BDD-style Given-When-Then structure
- ✅ User story comments for each test
- ✅ Descriptive test method names
- ✅ Comprehensive assertions
- ✅ Edge case coverage
- ✅ Validation boundary testing
- ✅ Immutability verification via reflection
- ✅ Equality semantics testing

---

## Quality Metrics

| Metric | BaseURL | RepositoryName | Status |
|--------|---------|----------------|--------|
| PHPStan Level 8 | 0 errors | 0 errors | ✅ |
| PSR-12 Compliance | 100% | 100% | ✅ |
| Code Coverage (Lines) | 85% | 100% | ✅/✅ |
| Code Coverage (Methods) | 80% | 100% | ✅/✅ |
| CRAP Index | 10.34 | Low | ✅ |
| Test Count | 14 | 14 | ✅ |
| Lines of Code | 113 | 93 | ✅ |

### Coverage Notes

**BaseURL** has 85% line coverage due to defensive `parse_url` check (lines 101-103):
- This code path is unreachable after `filter_var` validation succeeds
- Similar to the known AnyUri issue #7
- Trade-off: Type safety (PHPStan) vs. 100% coverage
- Decision: Prefer type safety and defensive programming

**RepositoryName** achieves 100% coverage due to simpler validation logic.

---

## Usage Guidelines

### When to Use BaseURL

✅ **Use BaseURL when:**
- Configuring an OAI-PMH repository endpoint
- Building an `Identify` response
- Validating repository configuration
- Storing repository metadata

❌ **Don't use BaseURL when:**
- You need to accept any URI (use `AnyUri` instead)
- You need non-HTTP protocols
- The URL is not for an OAI-PMH endpoint

### When to Use RepositoryName

✅ **Use RepositoryName when:**
- Defining a human-readable repository name
- Building an `Identify` response
- Displaying repository information to users
- Storing repository metadata

❌ **Don't use RepositoryName when:**
- You need a machine-readable identifier (use a different VO)
- You need a structured name with parts (create a composite VO)

### Best Practices

1. **BaseURL Configuration**
   ```php
   // ✅ Good: Use HTTPS for production
   $baseUrl = new BaseURL('https://repository.example.org/oai');
   
   // ⚠️ Acceptable: HTTP for development
   $baseUrl = new BaseURL('http://localhost:8080/oai');
   
   // ❌ Bad: Non-web protocol
   $baseUrl = new BaseURL('ftp://repository.example.org/oai');
   ```

2. **RepositoryName Descriptiveness**
   ```php
   // ✅ Good: Descriptive and clear
   $name = new RepositoryName('MIT Libraries Digital Collections');
   
   // ⚠️ Acceptable: Simple but clear
   $name = new RepositoryName('Digital Archive');
   
   // ❌ Bad: Too vague
   $name = new RepositoryName('Repository');
   ```

3. **International Support**
   ```php
   // ✅ Good: Native language names
   $name = new RepositoryName('Bibliothèque nationale de France');
   $name = new RepositoryName('北京大学图书馆');
   
   // ✅ Also good: English transliteration with native
   $name = new RepositoryName('Bibliothèque nationale de France (BnF)');
   ```

### Common Pitfalls

#### BaseURL

- ❌ **Don't** forget the protocol: `new BaseURL('example.org/oai')` → Invalid!
- ✅ **Do** include protocol: `new BaseURL('https://example.org/oai')`

- ❌ **Don't** use trailing `?` without parameters: `new BaseURL('https://example.org/oai?')`
- ✅ **Do** omit or include full query: `new BaseURL('https://example.org/oai')`

- ❌ **Don't** use port without number: `new BaseURL('https://example.org:/oai')`
- ✅ **Do** specify port number: `new BaseURL('https://example.org:8080/oai')`

#### RepositoryName

- ❌ **Don't** use only whitespace: `new RepositoryName('   ')` → Exception!
- ✅ **Do** use meaningful text: `new RepositoryName('Digital Library')`

- ❌ **Don't** assume trimming: Spaces are preserved
- ✅ **Do** trim beforehand if needed: `new RepositoryName(trim($userInput))`

---

## Future Enhancements

### Planned

- [ ] **Issue #8**: Migrate to PHP 8.2 `readonly` properties
  - Both BaseURL and RepositoryName are candidates
  - Will simplify immutability guarantees
  - Update tests to remove reflection-based immutability checks

### Known Issues

- **BaseURL Coverage Gap** (lines 101-103):
  - Defensive `parse_url` check is unreachable in practice
  - Similar to AnyUri issue #7
  - Trade-off accepted: Type safety > 100% coverage
  - Not blocking; will be addressed if actual URL parsing issues emerge

### Potential Improvements

- **BaseURL Normalization** (low priority):
  - Consider normalizing URLs (e.g., remove default ports)
  - Specification doesn't require it
  - Could be added if users request it

- **RepositoryName Length Limits** (low priority):
  - Currently no maximum length
  - Consider adding practical limit (e.g., 255 chars)
  - Not specified in OAI-PMH
  - Add if database integration requires it

### Migration Notes

**PHP 8.2 Readonly Properties:**
```php
// Current (PHP 8.0)
final class BaseURL
{
    private string $url;
    
    public function __construct(string $url)
    {
        $this->validateUrl($url);
        $this->url = $url;
    }
}

// Future (PHP 8.2+)
final class BaseURL
{
    public function __construct(
        private readonly string $url
    ) {
        $this->validateUrl($url);
    }
}
```

However, this won't work with current validation pattern. Alternative:
```php
final class BaseURL
{
    public readonly string $url;
    
    public function __construct(string $url)
    {
        $this->validateUrl($url);
        $this->url = $url; // Allowed in constructor
    }
}
```

---

## Integration with Other Value Objects

### Repository Identity Aggregate

BaseURL and RepositoryName are typically used together with other value objects to form a complete repository identity:

```php
class RepositoryIdentity
{
    public function __construct(
        private BaseURL $baseURL,
        private RepositoryName $repositoryName,
        private EmailCollection $adminEmails,
        private UTCdatetime $earliestDatestamp,
        private DeletedRecord $deletedRecord,
        private Granularity $granularity,
        private ProtocolVersion $protocolVersion,
        private ?DescriptionCollection $descriptions = null
    ) {}
    
    // Getters...
}
```

### Related Value Objects

- **Email / EmailCollection**: Administrative contact(s)
- **UTCdatetime**: Earliest datestamp in repository
- **DeletedRecord**: Deleted record support policy
- **Granularity**: Temporal granularity
- **ProtocolVersion**: OAI-PMH protocol version(s)
- **Description / DescriptionCollection**: Optional repository descriptions

---

## References

- [OAI-PMH 2.0 Specification - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [RFC 3986 - Uniform Resource Identifier (URI): Generic Syntax](https://www.rfc-editor.org/rfc/rfc3986)
- [PHP filter_var Documentation](https://www.php.net/manual/en/function.filter-var.php)
- [PHP parse_url Documentation](https://www.php.net/manual/en/function.parse-url.php)
- Related analysis: `DESCRIPTIONCOLLECTION_ANALYSIS.md`
- Related analysis: `REPOSITORY_ANALYSIS.md`
- GitHub Issue #7: AnyUri validation limitation
- GitHub Issue #8: PHP 8.2 readonly property migration
- GitHub Issue #10: Define repository identity value object (this branch)

---

## Appendix: Test Output

```
Base URL (OaiPmh\Tests\Domain\ValueObject\BaseURL)
 ✔ Can instantiate with valid http url
 ✔ Can instantiate with valid https url
 ✔ Throws exception for empty string
 ✔ Throws exception for invalid url format
 ✔ Throws exception for non http protocol
 ✔ Throws exception for file protocol
 ✔ Can instantiate with query parameters
 ✔ Can instantiate with custom port
 ✔ Equals returns true for same value
 ✔ Equals returns false for different value
 ✔ To string returns expected format
 ✔ Is immutable
 ✔ Can instantiate with path
 ✔ Can instantiate with trailing slash

Repository Name (OaiPmh\Tests\Domain\ValueObject\RepositoryName)
 ✔ Can instantiate with valid name
 ✔ Can instantiate with simple name
 ✔ Can instantiate with special characters
 ✔ Can instantiate with unicode characters
 ✔ Throws exception for empty string
 ✔ Throws exception for whitespace only
 ✔ Throws exception for tabs and newlines
 ✔ Can instantiate with leading and trailing spaces
 ✔ Equals returns true for same value
 ✔ Equals returns false for different value
 ✔ To string returns expected format
 ✔ Is immutable
 ✔ Can instantiate with long name
 ✔ Can instantiate with numbers

Tests: 153, Assertions: 229
Code Coverage: 96.93%
```

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*  
*Author: Paul Slits <paul.slits@gmail.com>*
