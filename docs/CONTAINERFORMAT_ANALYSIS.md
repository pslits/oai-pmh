# ContainerFormat Abstract Class Analysis

**Analysis Date:** February 7, 2026  
**Component:** ContainerFormat Abstract Base Class  
**File:** `src/Domain/ValueObject/ContainerFormat.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Protocol](http://www.openarchives.org/OAI/openarchivesprotocol.html)

---

## Executive Summary

ContainerFormat is an abstract base class providing shared functionality for all OAI-PMH XML container formats (metadata, about, description, setDescription). It encapsulates common properties (optional prefix, namespaces, schema URL, root tag) and provides value equality semantics.

---

## 1. OAI-PMH Context

### Specification Context

The OAI-PMH 2.0 specification defines several XML container types that share common structural patterns:

- **metadata** (section 2.5) - Record-level descriptive metadata
- **about** (section 2.8) - Record-level rights/provenance information  
- **description** (section 3.1.1.2) - Repository-level descriptions
- **setDescription** (section 4.6) - Set-level descriptions

All containers share: XML namespaces, schema location, and root element structure. ContainerFormat provides the common implementation for these shared concerns.

---

## 2. Purpose & Design

### Unified Container Pattern

OAI-PMH defines several XML container types:
- **metadata** - Record-level descriptive metadata (requires prefix)
- **about** - Record-level rights/provenance (no prefix)
- **description** - Repository-level descriptions (no prefix)
- **setDescription** - Set-level descriptions (no prefix)

All share common structure but differ in prefix requirement.

### Key Design Goals
- ✅ Code reuse for common behavior
- ✅ Flexible prefix (required for metadata, optional for others)
- ✅ Consistent equality and string representation
- ✅ Extensible for specific container types

---

## 2. Implementation

### Class Structure
```php
abstract class ContainerFormat
{
    protected ?MetadataPrefix $prefix;  // Nullable in base
    protected MetadataNamespaceCollection $namespaces;
    protected AnyUri $schemaUrl;
    protected MetadataRootTag $rootTag;
    
    public function __construct(
        ?MetadataPrefix $prefix,  // Optional
        MetadataNamespaceCollection $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    )
    
    public function getPrefix(): ?MetadataPrefix
    public function getNamespaces(): MetadataNamespaceCollection
    public function getSchemaUrl(): AnyUri
    public function getRootTag(): MetadataRootTag
    public function equals(ContainerFormat $other): bool
    public function __toString(): string
}
```

### Inheritance Hierarchy

```
ContainerFormat (abstract)
  │
  ├── MetadataFormat (final)
  │   └── Constructor: requires MetadataPrefix (non-null)
  │       getPrefix(): MetadataPrefix (non-null return type)
  │
  ├── DescriptionFormat (final)
  │   └── Constructor: passes null prefix
  │       getPrefix(): ?MetadataPrefix (null)
  │
  └── (Future: AboutFormat, SetDescriptionFormat)
```

---

## 3. Equality Implementation

### Value Equality Logic

```php
public function equals(ContainerFormat $other): bool
{
    // Compare all components
    if (!$this->isSamePrefix($other)) {
        return false;
    }
    if (!$this->namespaces->equals($other->getNamespaces())) {
        return false;
    }
    if (!$this->schemaUrl->equals($other->getSchemaUrl())) {
        return false;
    }
    if (!$this->rootTag->equals($other->getRootTag())) {
        return false;
    }
    return true;
}

private function isSamePrefix(ContainerFormat $other): bool
{
    if ($this->prefix === null && $other->getPrefix() === null) {
        return true;  // Both null
    }
    if ($this->prefix === null || $other->getPrefix() === null) {
        return false;  // One null, one not
    }
    return $this->prefix->equals($other->getPrefix());  // Both non-null
}
```

---

## 4. Test Coverage

**Tests:** 5 | **Assertions:** N/A (tested via subclasses)  
**Coverage:** 100% through MetadataFormat and DescriptionFormat tests

**Testing Strategy:**
- Abstract class tested indirectly
- MetadataFormatTest covers prefix required path
- DescriptionFormatTest covers prefix null path
- Combined coverage = 100%

---

## 5. Code Examples

### Cannot Instantiate Directly

```php
// ❌ Cannot do this (abstract class)
$container = new ContainerFormat(...);

// ✅ Use concrete implementations
$metadataFormat = new MetadataFormat(...);
$descriptionFormat = new DescriptionFormat(...);
```

### Extending ContainerFormat

```php
// Example: Future AboutFormat implementation
final class AboutFormat extends ContainerFormat
{
    public function __construct(
        MetadataNamespaceCollection $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    ) {
        // About format has no prefix
        parent::__construct(null, $namespaces, $schemaUrl, $rootTag);
    }
}
```

---

## 6. Design Decisions

### Decision 1: Abstract Base Class vs Interface

**Options:**
1. Interface only
2. Abstract base class (chosen)
3. Trait

**Rationale:**
- Need shared implementation (not just contract)
- Equality logic is identical for all containers
- Getters are identical
- toString is identical
- Base class perfect fit

**Trade-offs:**
- ✅ Eliminates code duplication
- ✅ Consistent behavior
- ✅ Single source of truth
- ⚠️ Slight coupling (acceptable for value objects)

### Decision 2: Optional Prefix (Nullable)

**Why nullable in base class:**
- MetadataFormat needs prefix (for harvesting)
- Other containers don't use prefix
- Subclasses override return type as needed
- Flexibility without separate base classes

**Implementation Pattern:**
```php
// Base class
public function getPrefix(): ?MetadataPrefix  // Nullable

// MetadataFormat override
public function getPrefix(): MetadataPrefix  // Non-null
{
    return parent::getPrefix();  // Safe: always non-null in MetadataFormat
}
```

### Decision 3: Protected Properties

**Why protected (not private):**
- Subclasses may need access
- Flexibility for future extensions
- Common practice for base classes

---

## 7. Supported Container Types

| Container Type | Prefix | OAI-PMH Context | Implemented |
|---------------|--------|-----------------|-------------|
| **metadata** | Required | Record-level metadata | ✅ MetadataFormat |
| **description** | Optional/None | Repository-level | ✅ DescriptionFormat |
| **about** | Optional/None | Record-level rights/provenance | 🔮 Future |
| **setDescription** | Optional/None | Set-level descriptions | 🔮 Future |

---

## 8. Comparison with Other Base Classes

| Aspect | ContainerFormat | Other Base Classes |
|--------|----------------|-------------------|
| **Type** | Abstract class | N/A (only abstract in project) |
| **Purpose** | Shared container behavior | N/A |
| **Extensibility** | ✅ Designed for extension | VOs are final |
| **Properties** | Protected | VOs have private |

---

## 9. Future Enhancements

- [ ] **AboutFormat** - Record-level rights/provenance containers
- [ ] **SetDescriptionFormat** - Set-level description containers
- [ ] **Issue #8**: PHP 8.2 readonly properties for base class

---

## 10. References

- [OAI-PMH 2.0 Spec](http://www.openarchives.org/OAI/openarchivesprotocol.html)
- [docs/METADATAFORMAT_ANALYSIS.md](METADATAFORMAT_ANALYSIS.md)
- [docs/DESCRIPTIONFORMAT_ANALYSIS.md](DESCRIPTIONFORMAT_ANALYSIS.md)
- Issue #8: PHP 8.2 readonly migration

---

## 11. Design Patterns Used

**Template Method Pattern:**
- Base class defines structure
- Subclasses customize behavior (prefix requirement)

**Value Object Pattern:**
- Immutable
- Value equality
- No setters

**Composition:**
- Composes multiple value objects
- Delegates validation

---

*Analysis generated on February 7, 2026*
