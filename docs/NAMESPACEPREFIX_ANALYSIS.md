# NamespacePrefix Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** NamespacePrefix Value Object  
**File:** `src/Domain/ValueObject/NamespacePrefix.php`  
**OAI-PMH Version:** 2.0  
**XML Specification:** [XML Namespaces](https://www.w3.org/TR/xml-names/)  
**Related Spec:** [OAI-PMH 2.0 - Metadata](http://www.openarchives.org/OAI/openarchivesprotocol.html#metadata)

---

## Executive Summary

NamespacePrefix represents an XML namespace prefix following XML 1.0 naming rules. It validates the prefix format to ensure compliance with XML specifications and provides value equality semantics. Used in metadata format declarations and XML namespace mappings for OAI-PMH responses.

---

## 1. XML Requirement

### Key Requirements
- ✅ Must match pattern: `[A-Za-z_][A-Za-z0-9_.-]*`
- ✅ Must start with letter or underscore (XML rule)
- ✅ Can contain letters, digits, underscore, hyphen, period
- ✅ Cannot start with digit (unlike MetadataPrefix)

### Common Examples
- `dc` - Dublin Core elements
- `oai_dc` - OAI Dublin Core
- `xsi` - XML Schema Instance
- `mods` - MODS namespace

---

## 2. Implementation

### Class Structure
```php
class NamespacePrefix  // Note: not final (can be extended)
{
    private string $prefix;
    private const PREFIX_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.-]*$/';
    
    public function __construct(string $prefix)
    public function getValue(): string
    public function equals(NamespacePrefix $other): bool
    public function __toString(): string
}
```

### Validation
- XML NCName compliant pattern
- Stricter than MetadataPrefix (cannot start with digit)
- Prevents invalid XML generation

---

## 3. Test Coverage

**Tests:** 8 | **Assertions:** 10 | **Coverage:** 100%

✅ Valid instantiation  
✅ Invalid format rejection  
✅ Value equality  
✅ Immutability  
✅ String representation  
✅ Edge cases (starts with underscore, numbers, etc.)  

---

## 4. Code Examples

```php
// ✅ Valid prefixes
$dc = new NamespacePrefix('dc');
$oai = new NamespacePrefix('oai_dc');
$custom = new NamespacePrefix('_myprefix');
$versioned = new NamespacePrefix('ns-v2.0');

// ❌ Invalid prefixes
new NamespacePrefix('');  // Exception: empty
new NamespacePrefix('2dc');  // Exception: starts with digit
new NamespacePrefix('my prefix');  // Exception: space
new NamespacePrefix('my/prefix');  // Exception: slash
```

### Usage in MetadataNamespace
```php
$namespace = new MetadataNamespace(
    new NamespacePrefix('dc'),
    new AnyUri('http://purl.org/dc/elements/1.1/')
);
```

---

## 5. Design Decisions

**Decision: XML NCName Compliance**
- Follows XML specification exactly
- Prevents invalid XML generation
- Stricter than MetadataPrefix (intentional)

**Decision: Not Final (Extensible)**
- Can be extended if needed
- Most VOs are final, this is exception
- Design choice: future flexibility

**Decision: Pattern Validation**
- Regex ensures XML compliance
- Clear error messages
- Fail-fast approach

---

## 6. Comparison Table

| Aspect | NamespacePrefix | MetadataPrefix |
|--------|----------------|---------------|
| **Purpose** | XML namespace prefix | OAI-PMH format ID |
| **Start char** | Letter or `_` only | Letter, digit, or special |
| **Pattern** | XML NCName rule | OAI-PMH rule |
| **Example** | `dc`, `mods`, `xsi` | `oai_dc`, `mods` |
| **Extensible** | ✅ Yes (not final) | ❌ No (final) |
| **Start with digit** | ❌ No | ✅ Yes |

---

## 7. Known Issues & Enhancements

- [ ] **Issue #8**: PHP 8.2 readonly properties
- [ ] Consider making `final` (consistency with other VOs)
- [ ] Add named constructors for common prefixes

---

## 8. References

- [XML Namespaces Spec](https://www.w3.org/TR/xml-names/)
- [docs/METADATANAMESPACE_ANALYSIS.md](METADATANAMESPACE_ANALYSIS.md)
- [docs/METADATAPREFIX_ANALYSIS.md](METADATAPREFIX_ANALYSIS.md)

---

*Analysis generated on February 7, 2026*
