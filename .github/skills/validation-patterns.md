# Skill: Validation & Error Handling Patterns

## When to Use
When implementing validation in value object constructors or entity methods.

## Pattern: Extract Validation Logic

### Bad: Inline Validation
```php
public function __construct(RecordHeader $header, ?array $metadata = null)
{
    if ($header->isDeleted() && $metadata !== null) {
        throw new InvalidArgumentException(
            'Deleted records cannot have metadata. According to OAI-PMH 2.0 specification ' .
            'section 2.6, records with a deleted status must not include metadata elements.'
        );
    }
    // More constructor logic...
}
```

### Good: Extracted Validation
```php
public function __construct(RecordHeader $header, ?array $metadata = null)
{
    $this->validateDeletedRecordInvariant($header, $metadata);
    // More constructor logic...
}

/**
 * Validates the deleted record invariant.
 *
 * According to OAI-PMH 2.0 specification section 2.6,
 * records with a deleted status must not include metadata elements.
 *
 * @param RecordHeader $header The record header.
 * @param array|null $metadata The metadata (if any).
 * @throws InvalidArgumentException If a deleted record has metadata.
 */
private function validateDeletedRecordInvariant(RecordHeader $header, ?array $metadata): void
{
    if ($header->isDeleted() && $metadata !== null) {
        throw new InvalidArgumentException('Deleted records cannot have metadata.');
    }
}
```

## Pattern: Multi-Step Validation

For complex validation, split into orchestrator + specific validators:

```php
private function validate(string $value): void
{
    $this->validateNotEmpty($value);
    $this->validateFormat($value);
    $this->validateBusinessRule($value);
}

private function validateNotEmpty(string $value): void
{
    if (empty($value)) {
        throw new InvalidArgumentException('Value cannot be empty.');
    }
}

private function validateFormat(string $value): void
{
    if (!preg_match('/^pattern$/', $value)) {
        throw new InvalidArgumentException('Value format is invalid.');
    }
}

private function validateBusinessRule(string $value): void
{
    if (/* business rule violated */) {
        throw new InvalidArgumentException('Business rule violated.');
    }
}
```

## Naming Convention

**Validation method names should be descriptive:**
- `validate()` - main orchestrator
- `validateNotEmpty()` - checks non-empty constraint
- `validateFormat()` - checks format/pattern
- `validateHttpProtocol()` - checks specific protocol
- `validateXxxInvariant()` - checks business rule/invariant
- `validateSetSpecsAreValid()` - checks collection validity

## Error Message Guidelines

### Concise Error Messages
Exception messages should be brief and actionable:

✅ **Good:**
```php
throw new InvalidArgumentException('BaseURL cannot be empty.');
throw new InvalidArgumentException('Invalid email format.');
throw new InvalidArgumentException('Deleted records cannot have metadata.');
```

❌ **Bad:**
```php
throw new InvalidArgumentException(
    'BaseURL cannot be empty. According to OAI-PMH 2.0 specification section 4.2, ' .
    'the baseURL must be a non-empty string representing...'
);
```

### Where to Put Specification Details

**In PHPDoc comments, not error messages:**
```php
/**
 * Validates that the baseURL is not empty.
 *
 * According to OAI-PMH 2.0 specification section 4.2,
 * the baseURL must be a valid HTTP(S) URL.
 *
 * @param string $baseUrl The URL to validate.
 * @throws InvalidArgumentException If the URL is empty.
 */
private function validateNotEmpty(string $baseUrl): void
{
    if (empty($baseUrl)) {
        throw new InvalidArgumentException('BaseURL cannot be empty.');
    }
}
```

## Benefits Checklist

When validation is properly extracted:
- [ ] Each method has single responsibility
- [ ] Method names are self-documenting
- [ ] Easy to test each validator independently
- [ ] Constructor stays clean and focused
- [ ] Specification references in PHPDoc, not exceptions
- [ ] Error messages are concise (<100 chars)
- [ ] Validation logic can be reused
- [ ] Code is maintainable and readable

## Anti-Patterns to Avoid

❌ Verbose error messages with specs  
❌ Multiple validation concerns in one method  
❌ Validation logic scattered in constructor  
❌ Generic validation method names  
❌ Missing PHPDoc on validators  
❌ Accessing `$this->property` in validators (pass params instead)
