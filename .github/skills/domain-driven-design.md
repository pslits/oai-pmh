# Skill: Domain-Driven Design Patterns

## When to Use
When creating or refactoring value objects, entities, and domain methods.

## Principle: Domain-Specific Naming

Use domain terminology in method names, not generic terms.

### Value Object Getters

✅ **Prefer: Domain-Specific Names**
```php
public function getRecordIdentifier(): string
public function getVerb(): string
public function getSetSpec(): string
public function getBaseUrl(): string
public function getRepositoryName(): string
```

❌ **Avoid: Generic Names**
```php
public function getValue(): string        // Which value?
public function getIdentifier(): string   // What kind of identifier?
public function getData(): string         // What data?
public function getString(): string       // What string?
```

### Why Domain-Specific Names?

**1. Self-Documenting Code**
```php
// What kind of identifier? Repository? Set? Record?
$id = $header->getIdentifier()->getValue();

// Crystal clear - it's a record identifier
$id = $header->getIdentifier()->getRecordIdentifier();
```

**2. Prevents Confusion**
When you have multiple identifier types in the same codebase:
- `RecordIdentifier` → `getRecordIdentifier()`
- `RepositoryIdentifier` → `getRepositoryIdentifier()`
- `SetIdentifier` → `getSetIdentifier()`

Generic `getValue()` or `getIdentifier()` doesn't distinguish between them.

**3. IDE Autocomplete**
Typing `$recordId->get...` shows `getRecordIdentifier()` which tells you exactly what you're getting.

**4. Code Review Clarity**
Reviewers can understand the domain concept without looking up the class definition.

## Pattern: Single Domain Getter

**Don't provide aliases:**
```php
// ❌ Bad: Multiple getters for same value
public function getVerb(): string { return $this->value; }
public function getValue(): string { return $this->value; }

// ✅ Good: Single domain-specific getter
public function getVerb(): string { return $this->value; }
```

**Exception:** If you need backward compatibility, clearly document the alias:
```php
public function getVerb(): string
{
    return $this->value;
}

/**
 * Returns the verb value (alias for getVerb).
 *
 * @deprecated Use getVerb() instead for better domain clarity.
 */
public function getValue(): string
{
    return $this->getVerb();
}
```

## Pattern: Entity Method Naming

Entities should also use domain terminology:

✅ **Good:**
```php
class Record
{
    public function getRecordHeader(): RecordHeader { }
    public function getMetadata(): ?array { }
    public function isDeleted(): bool { }
}
```

❌ **Bad:**
```php
class Record
{
    public function getHeader(): RecordHeader { }  // What header?
    public function getData(): ?array { }           // What data?
    public function deleted(): bool { }             // Unclear - is it a verb or state?
}
```

## Naming Guidelines by Domain Concept

### OAI-PMH Specific
| Concept | Method Name | Not |
|---------|-------------|-----|
| OAI Verb | `getVerb()` | `getValue()`, `getAction()` |
| Record Identifier | `getRecordIdentifier()` | `getIdentifier()`, `getId()` |
| Set Specification | `getSetSpec()` | `getValue()`, `getSpec()` |
| Base URL | `getBaseUrl()` | `getUrl()`, `getValue()` |
| Repository Name | `getRepositoryName()` | `getName()`, `getValue()` |
| Metadata Prefix | `getMetadataPrefix()` | `getPrefix()`, `getValue()` |
| Protocol Version | `getProtocolVersion()` | `getVersion()`, `getValue()` |
| Granularity | `getGranularity()` | `getValue()`, `getString()` |

### General Domain Patterns
- Use the exact term from the domain/specification
- Include the full concept name, not abbreviations
- Be specific about what type of thing it is
- Favor clarity over brevity

## Applying to New Value Objects

When creating a new value object:

1. **Identify the domain concept** (e.g., "Record Identifier" from OAI-PMH spec)
2. **Use domain term in class name** (`RecordIdentifier`)
3. **Use domain term in getter** (`getRecordIdentifier()`)
4. **Document OAI-PMH context** in PHPDoc
5. **Don't add `getValue()` alias** (avoid redundancy)

## Refactoring Existing Code

When refactoring to domain-specific names:

1. **Add new domain-specific getter** first
2. **Deprecate old generic getter** (if needed for compatibility)
3. **Search for all usages** of old getter
4. **Update systematically** (see api-refactoring.md skill)
5. **Remove deprecated getter** in next major version

## Consistency Across Library

All value objects in the library should follow this pattern:
- `BaseURL` → `getBaseUrl()`
- `Email` → `getEmail()`
- `RepositoryName` → `getRepositoryName()`
- `ProtocolVersion` → `getProtocolVersion()`
- etc.

**Do NOT** mix patterns (some with `getValue()`, some without).
