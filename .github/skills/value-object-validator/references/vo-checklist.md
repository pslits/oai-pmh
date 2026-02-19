# Value Object Validation Checklist

Complete checklist for validating OAI-PMH value objects against project standards.

## 1. File Header (5 checks)

- [ ] File header block exists
- [ ] `@author` tag: `Paul Slits <paul.slits@gmail.com>`
- [ ] `@copyright` tag: `(c) 2025 Paul Slits`
- [ ] `@license` tag: `MIT License - https://opensource.org/licenses/MIT`
- [ ] `@link` tag: `https://github.com/pslits/oai-pmh`
- [ ] `@since` tag exists with version

## 2. Class Declaration (4 checks)

- [ ] Class is marked as `final`
- [ ] No `abstract` or other modifiers
- [ ] Proper namespace: `OaiPmh\Domain\ValueObject`
- [ ] Class name matches file name (PascalCase)

## 3. Properties (3 checks)

- [ ] All properties are `private`
- [ ] NO public or protected properties
- [ ] Properties have type declarations

## 4. Immutability (2 checks)

- [ ] NO setter methods exist
- [ ] Properties only assigned in constructor

## 5. Constructor (6 checks)

- [ ] Constructor exists
- [ ] Parameter uses descriptive name (e.g., `$baseUrl`, not `$value`)
- [ ] Parameter has type declaration
- [ ] Calls validation before assignment
- [ ] Has complete docblock
- [ ] Docblock includes `@throws InvalidArgumentException`

## 6. Domain-Specific Getter (5 checks) - REQUIRED

- [ ] Has domain-specific getter (e.g., `getBaseUrl()`) - MANDATORY
- [ ] Named after domain concept (NOT generic like getValue())
- [ ] Returns correct type
- [ ] Has docblock with `@return`
- [ ] Returns the encapsulated value

## 7. equals() Method (8 checks)

- [ ] Method exists with signature: `equals(self $parameter): bool`
- [ ] Parameter name is descriptive (e.g., `$otherBaseUrl`, not `$other`)
- [ ] Uses type hint `self`
- [ ] Returns `bool`
- [ ] Compares internal values
- [ ] ALL parameter references in body match declaration
- [ ] Has complete docblock
- [ ] Docblock parameter description is clear

## 8. __toString() Method (3 checks)

- [ ] Method exists with signature: `__toString(): string`
- [ ] Returns string representation
- [ ] Uses `sprintf()` for formatting (e.g., `sprintf('BaseURL(value: %s)', $this->value)`)
- [ ] Has docblock

## 9. Validation Structure (10 checks)

- [ ] Has private `validate()` method
- [ ] `validate()` called in constructor before assignment
- [ ] Validation parameter uses descriptive name
- [ ] Complex validation split into focused methods
- [ ] Each validator method has single responsibility
- [ ] Validator names are descriptive (e.g., `validateNotEmpty()`, `validateFormat()`)
- [ ] All validators are private
- [ ] Validators accept parameters (don't directly access $this->value)
- [ ] All validators documented
- [ ] Validation logic matches domain requirements

## 10. Exception Handling (4 checks)

- [ ] Throws `InvalidArgumentException` for validation failures
- [ ] Error messages are descriptive
- [ ] Uses `sprintf()` with context: `sprintf('Error: %s', $context)`
- [ ] Each validator can throw independently

## 11. Documentation (23 checks)

### File-Level Docblock (3 checks)
- [ ] File docblock at very top (before namespace)
- [ ] Includes `@author`, `@copyright`, `@license`, `@link`, `@since` tags
- [ ] `@package` tag present (e.g., `OaiPmh\Domain\ValueObject`)

### Class-Level Docblock (8 checks)
- [ ] Class docblock directly above class declaration
- [ ] References OAI-PMH 2.0 specification section
- [ ] Explains what the value represents
- [ ] Mentions encapsulation principle
- [ ] Mentions immutability principle
- [ ] Mentions value equality (compared by value not identity)
- [ ] Lists allowed values (for enums) or format requirements
- [ ] States if required/optional in OAI-PMH protocol

### Method/Function Docblock (4 checks)
- [ ] All public methods have complete docblock directly above method
- [ ] `@param` tags: one per parameter with type and description
- [ ] `@return` tags: type and description, including `Type|null` for nullable returns
- [ ] `@throws` tags: one per exception type that may be thrown

### Docblock Structure (5 checks)
- [ ] Summary: one-line description immediately after `/**`
- [ ] Summary: written as a complete sentence
- [ ] Summary: clearly states what the element does
- [ ] Description: more detailed explanation after blank line (recommended)
- [ ] Description: explains "why" and "how", not just "what"

### Docblock Quality Standards (5 checks)
- [ ] All descriptions use clear, complete sentences
- [ ] Parameter descriptions explain purpose, not just repeat name
- [ ] Return descriptions explain what is returned and when
- [ ] Complex private methods documented when logic is non-trivial
- [ ] Docblocks explain WHY not just WHAT

### Canonical PHPDoc Examples

**Docblock Structure (Summary + Description):**
```php
/**
 * Validates that the URL uses HTTP or HTTPS protocol. ← SUMMARY (one line, complete sentence)
 *                                                      ← BLANK LINE
 * Checks if the provided URL starts with http:// or https://.  ← DESCRIPTION START
 * This is required by the OAI-PMH specification for base URLs.
 * Explains WHY and HOW, not just WHAT.                         ← DESCRIPTION END
 *
 * @param string $baseUrl The URL to validate.
 *
 * @throws InvalidArgumentException If the URL doesn't use HTTP/HTTPS.
 */
```

**File-Level Docblock:****
```php
<?php
/**
 * Contains the BaseURL value object.
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 * @package   OaiPmh\Domain\ValueObject
 */
```

**Class-Level Docblock:**
```php
/**
 * Represents an OAI-PMH repository base URL as a value object.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify),
 * the baseURL identifies the repository and must be a valid HTTP/HTTPS URL.
 *
 * This value object:
 * - encapsulates a validated base URL,
 * - is immutable and compared by value (not identity),
 * - ensures only valid HTTP/HTTPS URLs are accepted,
 * - is required in the OAI-PMH Identify response.
 *
 * @package OaiPmh\Domain\ValueObject
 * @since   0.1.0
 */
final class BaseURL
{
```

**Constructor Docblock:**
```php
/**
 * Constructs a new BaseURL instance.
 *
 * Validates that the provided URL is not empty, is a valid URL format,
 * and uses HTTP or HTTPS protocol.
 *
 * @param string $baseUrl The repository base URL to encapsulate.
 *
 * @throws InvalidArgumentException If the URL is empty, invalid, or not HTTP/HTTPS.
 */
public function __construct(string $baseUrl)
{
```

**Getter Method Docblock:**
```php
/**
 * Returns the base URL.
 *
 * @return string The validated repository base URL.
 */
public function getBaseUrl(): string
{
```

**equals() Method Docblock:**
```php
/**
 * Checks if this BaseURL is equal to another.
 *
 * Two BaseURL instances are considered equal if they contain
 * the same URL value (case-sensitive comparison).
 *
 * @param BaseURL $otherBaseUrl The other instance to compare with.
 *
 * @return bool True if both have the same URL value, false otherwise.
 */
public function equals(self $otherBaseUrl): bool
{
```

**Validation Method Docblock:**
```php
/**
 * Validates that the URL uses HTTP or HTTPS protocol.
 *
 * @param string $baseUrl The URL to validate.
 *
 * @throws InvalidArgumentException If the URL doesn't start with http:// or https://.
 */
private function validateHttpProtocol(string $baseUrl): void
{
```

## 12. Naming Conventions (5 checks)

- [ ] Class name is PascalCase
- [ ] Methods are camelCase
- [ ] Constructor parameter is descriptive
- [ ] equals() parameter is descriptive (e.g., `$otherX`)
- [ ] Validation method parameters are descriptive

## 13. OAI-PMH Compliance (6 checks)

- [ ] Implements correct OAI-PMH concept
- [ ] Docblock cites specific spec section (e.g., "section 4.2")
- [ ] Validates against spec-defined constraints
- [ ] Matches OAI-PMH data type format
- [ ] Required vs optional correctly understood
- [ ] XML element name considerations (if applicable)

## 14. Code Quality - PHPStan (3 checks)

- [ ] Passes PHPStan Level 8 analysis
- [ ] 0 errors reported
- [ ] All types properly declared

Command:
```bash
vendor\bin\phpstan analyse src/Domain/ValueObject/[File].php
```

## 15. Code Quality - PSR-12 (5 checks)

- [ ] Passes PHPCS checks
- [ ] 4 spaces indentation (no tabs)
- [ ] Lines under 120 characters
- [ ] Proper spacing and formatting
- [ ] No trailing whitespace

Commands:
```bash
vendor\bin\phpcs src/Domain/ValueObject/[File].php
vendor\bin\phpcbf src/Domain/ValueObject/[File].php
```

## 16. Test File Existence (3 checks)

- [ ] Test file exists: `tests/Domain/ValueObject/[Name]Test.php`
- [ ] Test class name: `[Name]Test`
- [ ] Test namespace: `OaiPmh\Tests\Domain\ValueObject`

## 17. Test Quality (11 checks)

- [ ] Extends PHPUnit `TestCase`
- [ ] Test methods use pattern: `testMethodName_Condition_ExpectedBehavior()`
- [ ] Uses BDD-style Given-When-Then comments
- [ ] Tests constructor with valid data
- [ ] Tests all validation scenarios
- [ ] Tests constructor with invalid data
- [ ] Tests equals() with same values
- [ ] Tests equals() with different values
- [ ] Tests domain-specific getter
- [ ] Tests __toString()
- [ ] Uses data providers where appropriate

## 18. Test Coverage (5 checks)

- [ ] Tests run successfully
- [ ] Line coverage ≥ 95% (aim for 100%)
- [ ] Branch coverage ≥ 95%
- [ ] All validation paths tested
- [ ] Edge cases covered

Commands:
```bash
vendor\bin\phpunit tests/Domain/ValueObject/[File]Test.php
vendor\bin\phpunit --coverage-html coverage/html
```

## Total Checks: 111

### Scoring

- **✅ PASS**: 90-100% checks pass
- **⚠️ WARNINGS**: 75-89% checks pass
- **❌ FAILED**: <75% checks pass

### Priority Levels

**Critical (Must Fix):**
- PHPStan errors
- Missing required methods
- Broken immutability
- Invalid OAI-PMH implementation
- Test failures

**High Priority (Should Fix):**
- Parameter naming issues
- Missing docblocks
- PSR-12 violations
- Incomplete test coverage

**Medium Priority (Recommended):**
- Non-descriptive parameter names
- Missing @throws tags
- Documentation improvements

**Low Priority (Optional):**
- Code style improvements
- Enhanced error messages
- Additional test scenarios

---

## Grep Patterns for Finding Checks

**Find specific sections:**
- All checks in a section: `/^## \d+\. /`
- File header checks: `/^## 1\. File Header/`
- Constructor checks: `/^## 5\. Constructor/`
- Getter checks: `/^## 6\. Domain-Specific Getter/`
- Documentation checks: `/^## 11\. Documentation/`
- Validation checks: `/^## 9\. Validation Structure/`
- Test checks: `/^## 18\. Test Coverage/`

**Find specific patterns:**
- All checkboxes: `/^- \[ \] /`
- Priority items: `/Priority:/`
- Required items: `/REQUIRED|MANDATORY|CRITICAL/`
- Domain-specific patterns: `/domain-specific/i`
