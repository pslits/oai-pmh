# Value Object Validation Examples

Detailed code examples showing correct value object patterns.

## Basic Value Object Structure

### Complete Example: BaseURL

```php
<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */

declare(strict_types=1);

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Represents the OAI-PMH base URL as a value object.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify),
 * the baseURL is the URL of the repository's OAI-PMH interface.
 *
 * This value object:
 * - encapsulates a validated base URL,
 * - is immutable and compared by value (not identity),
 * - ensures only valid HTTP/HTTPS URLs are accepted,
 * - is required in the OAI-PMH Identify response.
 */
final class BaseURL
{
    private string $value;

    /**
     * Constructs a new BaseURL instance.
     *
     * @param string $baseUrl The base URL to encapsulate.
     *
     * @throws InvalidArgumentException If the URL is empty or invalid.
     */
    public function __construct(string $baseUrl)
    {
        $this->validate($baseUrl);
        $this->value = $baseUrl;
    }

    /**
     * Returns the base URL.
     *
     * @return string The encapsulated base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->value;
    }

    /**
     * Checks if this BaseURL is equal to another.
     *
     * @param BaseURL $otherBaseUrl The other instance to compare with.
     *
     * @return bool True if both have the same value, false otherwise.
     */
    public function equals(self $otherBaseUrl): bool
    {
        return $this->value === $otherBaseUrl->value;
    }

    /**
     * Returns a string representation of the BaseURL.
     *
     * @return string A string representation.
     */
    public function __toString(): string
    {
        return sprintf('BaseURL(value: %s)', $this->value);
    }

    /**
     * Validates the base URL.
     *
     * @param string $baseUrl The URL to validate.
     *
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $baseUrl): void
    {
        $this->validateNotEmpty($baseUrl);
        $this->validateUrlFormat($baseUrl);
        $this->validateHttpProtocol($baseUrl);
    }

    /**
     * Validates that the URL is not empty.
     *
     * @param string $baseUrl The URL to check.
     *
     * @throws InvalidArgumentException If the URL is empty.
     */
    private function validateNotEmpty(string $baseUrl): void
    {
        if (empty($baseUrl)) {
            throw new InvalidArgumentException('BaseURL cannot be empty.');
        }
    }

    /**
     * Validates the URL format.
     *
     * @param string $baseUrl The URL to validate.
     *
     * @throws InvalidArgumentException If the format is invalid.
     */
    private function validateUrlFormat(string $baseUrl): void
    {
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf('Invalid URL format: %s', $baseUrl)
            );
        }
    }

    /**
     * Validates that the URL uses HTTP or HTTPS protocol.
     *
     * @param string $baseUrl The URL to validate.
     *
     * @throws InvalidArgumentException If protocol is not HTTP/HTTPS.
     */
    private function validateHttpProtocol(string $baseUrl): void
    {
        if (!preg_match('/^https?:\/\//', $baseUrl)) {
            throw new InvalidArgumentException(
                'BaseURL must use HTTP or HTTPS protocol per OAI-PMH specification.'
            );
        }
    }
}
```

## Enumeration Value Object Example: DeletedRecord

```php
final class DeletedRecord
{
    private const ALLOWED_VALUES = ['no', 'transient', 'persistent'];

    private string $value;

    public function __construct(string $deletedRecord)
    {
        $this->validate($deletedRecord);
        $this->value = $deletedRecord;
    }

    public function getDeletedRecord(): string
    {
        return $this->value;
    }

    public function equals(self $otherDeletedRecord): bool
    {
        return $this->value === $otherDeletedRecord->value;
    }

    public function __toString(): string
    {
        return sprintf('DeletedRecord(value: %s)', $this->value);
    }

    private function validate(string $deletedRecord): void
    {
        $this->validateNotEmpty($deletedRecord);
        $this->validateAllowedValue($deletedRecord);
    }

    private function validateNotEmpty(string $deletedRecord): void
    {
        if (empty($deletedRecord)) {
            throw new InvalidArgumentException('DeletedRecord cannot be empty.');
        }
    }

    private function validateAllowedValue(string $deletedRecord): void
    {
        if (!in_array($deletedRecord, self::ALLOWED_VALUES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid deletedRecord value "%s". Must be one of: %s',
                    $deletedRecord,
                    implode(', ', self::ALLOWED_VALUES)
                )
            );
        }
    }
}
```

## Pattern: Formatted Value Object (UTCdatetime)

```php
final class UTCdatetime
{
    private string $value;
    private string $granularity;

    public function __construct(string $datetime, string $granularity = 'YYYY-MM-DDThh:mm:ssZ')
    {
        $this->validateGranularity($granularity);
        $this->granularity = $granularity;
        $this->validate($datetime);
        $this->value = $datetime;
    }

    public function getUtcDatetime(): string
    {
        return $this->value;
    }

    public function getGranularity(): string
    {
        return $this->granularity;
    }

    public function equals(self $otherUtcDatetime): bool
    {
        return $this->value === $otherUtcDatetime->value
            && $this->granularity === $otherUtcDatetime->granularity;
    }

    public function __toString(): string
    {
        return sprintf('UTCdatetime(value: %s, granularity: %s)', $this->value, $this->granularity);
    }

    private function validate(string $datetime): void
    {
        $this->validateNotEmpty($datetime);
        $this->validateFormat($datetime);
    }

    private function validateGranularity(string $granularity): void
    {
        $allowed = ['YYYY-MM-DD', 'YYYY-MM-DDThh:mm:ssZ'];
        if (!in_array($granularity, $allowed, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid granularity "%s". Must be: %s', $granularity, implode(' or ', $allowed))
            );
        }
    }

    private function validateNotEmpty(string $datetime): void
    {
        if (empty($datetime)) {
            throw new InvalidArgumentException('UTCdatetime cannot be empty.');
        }
    }

    private function validateFormat(string $datetime): void
    {
        if ($this->granularity === 'YYYY-MM-DD') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datetime)) {
                throw new InvalidArgumentException('Invalid date format. Expected: YYYY-MM-DD');
            }
        } else {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $datetime)) {
                throw new InvalidArgumentException('Invalid datetime format. Expected: YYYY-MM-DDThh:mm:ssZ');
            }
        }
    }
}
```

## Common Validation Patterns

### Empty String Validation
```php
private function validateNotEmpty(string $value): void
{
    if (empty($value)) {
        throw new InvalidArgumentException('[FieldName] cannot be empty.');
    }
}
```

### URL Validation
```php
private function validateUrlFormat(string $url): void
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException(
            sprintf('Invalid URL format: %s', $url)
        );
    }
}
```

### Email Validation
```php
private function validateEmailFormat(string $email): void
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException(
            sprintf('Invalid email format: %s', $email)
        );
    }
}
```

### Enumeration Validation
```php
private function validateAllowedValue(string $value): void
{
    $allowed = ['value1', 'value2', 'value3'];
    if (!in_array($value, $allowed, true)) {
        throw new InvalidArgumentException(
            sprintf('Invalid value "%s". Must be one of: %s', $value, implode(', ', $allowed))
        );
    }
}
```

### Pattern Validation (Regex)
```php
private function validatePattern(string $value): void
{
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
        throw new InvalidArgumentException(
            sprintf('Invalid format "%s". Must match pattern: [A-Za-z0-9_-]+', $value)
        );
    }
}
```

### HTTP/HTTPS Protocol Validation
```php
private function validateHttpProtocol(string $url): void
{
    if (!preg_match('/^https?:\/\//', $url)) {
        throw new InvalidArgumentException(
            'URL must use HTTP or HTTPS protocol.'
        );
    }
}
```

---

## Abstract Base Class Examples

### Example: ContainerFormat Base Class

**File:** `src/Domain/ValueObject/ContainerFormat.php`

**Class Type:** Abstract Base Class

**Purpose:** Provides base infrastructure for container format value objects (DublinCore, OAIPMH)

#### Valid Abstract Base Class Pattern

```php
<?php

declare(strict_types=1);

namespace OaiPmh\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Base class for container format value objects.
 *
 * Provides shared infrastructure for container format implementations
 * while allowing subclasses to define their specific validation rules
 * and allowed values.
 *
 * Known subclasses:
 * - DublinCore: oai_dc container format
 * - OAIPMH: oai_pmh container format
 *
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
abstract class ContainerFormat
{
    /**
     * The container format value.
     *
     * Protected to allow subclass access. May be null if subclass allows it.
     *
     * @var string|null
     */
    protected string|null $value;

    /**
     * Constructs a new ContainerFormat instance.
     *
     * Subclasses should call parent constructor and perform their own validation.
     *
     * @param string|null $containerFormat The container format value.
     * @throws InvalidArgumentException If validation fails.
     */
    public function __construct(string|null $containerFormat)
    {
        $this->validate($containerFormat);
        $this->value = $containerFormat;
    }

    /**
     * Returns the container format value (template method).
     *
     * Subclasses can override to provide specialized behavior.
     *
     * @return string|null The container format value.
     */
    protected function getContainerFormat(): string|null
    {
        return $this->value;
    }

    /**
     * Returns the value (public interface).
     *
     * @return string|null The container format value.
     */
    public function getValue(): string|null
    {
        return $this->getContainerFormat();
    }

    /**
     * Checks if this ContainerFormat equals another.
     *
     * Handles inheritance - compares class type and value.
     *
     * @param ContainerFormat $otherFormat The other instance to compare.
     * @return bool True if same class and value, false otherwise.
     */
    public function equals(self $otherFormat): bool
    {
        return get_class($this) === get_class($otherFormat) 
            && $this->value === $otherFormat->value;
    }

    /**
     * Returns string representation.
     *
     * @return string String representation of the container format.
     */
    public function __toString(): string
    {
        return sprintf(
            '%s(value: %s)',
            static::class,
            $this->value ?? 'null'
        );
    }

    /**
     * Validates the container format value.
     *
     * Base implementation checks for null. Subclasses should override
     * to add format-specific validation.
     *
     * @param string|null $containerFormat The value to validate.
     * @throws InvalidArgumentException If validation fails.
     */
    protected function validate(string|null $containerFormat): void
    {
        // Base class allows null - subclasses can restrict
    }
}
```

#### Validation Report Example

```
📋 Value Object Validation Report
==================================

File: src/Domain/ValueObject/ContainerFormat.php
Class Type: Abstract Base Class
Validation Checklist: references/abstract-base-class-checklist.md

Assessment: GOOD
Infrastructure is stable with clear extension points.

---

✅ PASS: File Header (5/5 checks)
  ✅ Has @author tag
  ✅ Has @copyright tag
  ✅ Has @license tag
  ✅ Has @link tag
  ✅ Has @since tag

✅ PASS: Class Modifiers (4/4 checks)
  ✅ Class is abstract
  ✅ Has comprehensive class docblock
  ✅ Explains base class purpose
  ✅ References OAI-PMH context

✅ PASS: Property Design (6/6 checks)
  ✅ Uses protected visibility for subclass access
  ✅ Has type declaration (string|null)
  ✅ Documented with @var tag
  ✅ Immutable (no setters)
  ✅ Null safety explicitly handled
  ✅ Descriptive property name

✅ PASS: Method Design (8/8 checks)
  ✅ Has domain-specific getter (getContainerFormat)
  ✅ Uses protected for template method pattern
  ✅ No abstract methods needed (base provides defaults)
  ✅ equals() handles inheritance correctly
  ✅ __toString() provides meaningful output
  ✅ All public methods documented
  ✅ Protected methods documented
  ✅ Private validation documented

✅ PASS: Template Method Pattern (4/4 checks)
  ✅ getContainerFormat() is protected extension point
  ✅ Documentation explains override capability
  ✅ Base implementation is sensible
  ✅ Uses descriptive parameter names

✅ PASS: Validation Logic (8/8 checks)
  ✅ Validation appropriate for base class
  ✅ Delegates to subclasses appropriately
  ✅ Uses InvalidArgumentException
  ✅ Descriptive error messages
  ✅ Null explicitly allowed and documented
  ✅ Validation parameters descriptive
  ✅ Could split validation (not complex enough yet)
  ✅ Single responsibility maintained

✅ PASS: Null Safety (6/6 checks)
  ✅ Constructor documents null acceptance
  ✅ Null explicitly validated/allowed
  ✅ Return types document null
  ✅ Protected methods document null contract
  ✅ Getters handle null safely
  ✅ equals() handles null safely

⚠️  WARN: Breaking Change Considerations (6/8 checks)
  ✅ Method signatures documented
  ✅ No abstract methods added
  ✅ No visibility changes
  ✅ No parameter type changes
  ✅ No return type changes
  ❌ Migration path not documented
  ⚠️  Subclass impact mentioned but not detailed
  ✅ Version implications understood

✅ PASS: Documentation (10/10 checks)
  ✅ Class docblock explains purpose
  ✅ Lists known subclasses
  ✅ Explains template method pattern
  ✅ References OAI-PMH specification
  ✅ Protected methods document extension points
  ✅ @param tags descriptive
  ✅ @return tags include null
  ✅ @throws documented
  ✅ No migration notes needed yet
  ✅ Clear and comprehensive

✅ PASS: Test Infrastructure (10/10 checks)
  ✅ Test class exists
  ✅ Tests cover base behavior
  ✅ Tests verify null handling
  ✅ Tests use BDD-style naming
  ✅ Comprehensive assertions
  ✅ Tests verify inheritance
  ✅ equals() tested with inheritance
  ✅ Coverage appropriate for infrastructure

✅ PASS: Code Quality (8/8 checks)
  ✅ PHPStan Level 8: 0 errors
  ✅ PSR-12: 0 violations
  ✅ No unused imports
  ✅ No TODO comments
  ✅ Correct namespace
  ✅ Type hints everywhere
  ✅ No suppressed warnings
  ✅ Reasonable complexity

⚠️  WARN: Analysis Documentation (4/5 checks)
  ✅ Analysis document exists
  ✅ Explains abstract pattern
  ✅ Lists subclasses
  ⚠️  Breaking change section incomplete
  ✅ Basic migration strategy present

---

Priority Fixes:

🟡 HIGH PRIORITY:
1. Complete migration strategy documentation for future breaking changes
2. Add detailed subclass impact analysis section to docs

🟢 LOW PRIORITY:
1. Consider adding examples of subclass implementations to analysis doc

---

Recommendations:

For Developers:
1. ✅ DO extend ContainerFormat for new container types
2. ✅ DO override validate() to add format-specific rules
3. ❌ DON'T change protected → private without assessing subclass impact
4. ✅ DO document any new template methods added

For Library Maintainers:
1. Monitor for breaking changes to base class
2. Keep subclass list updated in docblock
3. Document migration path when changes needed
4. Consider backward compatibility carefully

---

Next Steps:

1. ✅ Code quality: PASSED - No changes needed
2. ⚠️  Documentation: Update analysis doc with:
   - Complete breaking change migration strategy
   - Detailed subclass impact assessment template
3. ✅ Tests: PASSED - Coverage appropriate
4. ⚠️  Create updated analysis: docs/analysis/ValueObject/CONTAINERFORMAT_ANALYSIS.md

---

Overall Assessment: GOOD

This abstract base class provides stable infrastructure with clear extension
points. Minor documentation improvements needed for breaking change handling.
Subclass contract is well-defined and tests verify inheritance behavior.

✅ Ready for use as base class
⚠️  Documentation improvements recommended
```

---

**Grep patterns for finding specific examples:**
- Find complete examples: `/## .* Example:/`
- Find validation patterns: `/### .* Validation/`
- Find specific value object: `/final class \w+/`
- Find validation methods: `/private function validate\w+/`
