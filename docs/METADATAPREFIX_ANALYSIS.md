# MetadataPrefix Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** MetadataPrefix Value Object  
**File:** `src/Domain/ValueObject/MetadataPrefix.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - metadataPrefix](http://www.openarchives.org/OAI/openarchivesprotocol.html#metadataPrefix)

---

## Executive Summary

MetadataPrefix is a simple string-based value object that represents the unique identifier used to request specific metadata formats in OAI-PMH protocol operations. It validates format compliance with OAI-PMH naming rules and provides value equality semantics.

---

## 1. OAI-PMH Requirement

### Key Requirements
- ✅ Must match pattern: `[A-Za-z0-9\-_\.!~\*\'\(\)]+`
- ✅ Uniquely identifies a metadata format
- ✅ Used in GetRecord, ListRecords, ListIdentifiers, ListMetadataFormats

### Common Examples
- `oai_dc` - Dublin Core (required by spec)
- `mods` - Metadata Object Description Schema
- `marc21` - MARC 21 bibliographic
- `didl` - Digital Item Declaration Language

---

## 2. Implementation

### Class Structure
```php
final class MetadataPrefix
{
    private string $prefix;
    private const PREFIX_PATTERN = '/^[A-Za-z0-9\-_\.!~\*\'\(\)]+$/';
    
    public function __construct(string $prefix)
    public function getValue(): string
    public function equals(MetadataPrefix $other): bool
    public function __toString(): string
}
```

### Validation
- Regex pattern validates allowed characters
- Empty string rejected by pattern
- Case-sensitive comparison

---

## 3. Test Coverage

**Tests:** 6 | **Assertions:** 7 | **Coverage:** 100%

✅ Valid instantiation  
✅ Empty prefix rejection  
✅ Value equality  
✅ Immutability  
✅ String representation  

---

## 4. Code Examples

```php
// ✅ Valid prefixes
$dc = new MetadataPrefix('oai_dc');
$mods = new MetadataPrefix('mods');
$custom = new MetadataPrefix('my-custom_format.v2');

// ❌ Invalid prefixes
new MetadataPrefix('');  // Exception: empty
new MetadataPrefix('oai dc');  // Exception: space
new MetadataPrefix('oai/dc');  // Exception: slash
```

---

## 5. Design Decisions

**Decision: Regex Validation**
- Allows safe URL-encoding characters
- Prevents injection attacks
- Follows OAI-PMH best practices

**Decision: Immutable**
- Value object pattern
- Thread-safe
- Cacheable

---

## 6. Comparison: MetadataPrefix vs NamespacePrefix

| Aspect | MetadataPrefix | NamespacePrefix |
|--------|---------------|-----------------|
| Purpose | OAI-PMH format identifier | XML namespace prefix |
| Pattern | `[A-Za-z0-9\-_\.!~\*\'\(\)]+` | `[A-Za-z_][A-Za-z0-9_.-]*` |
| Example | `oai_dc`, `mods` | `dc`, `oai_dc`, `xsi` |
| Can start with digit | ✅ Yes | ❌ No (XML rule) |

---

## 7. References

- [OAI-PMH 2.0 Spec](http://www.openarchives.org/OAI/openarchivesprotocol.html)
- [docs/METADATAFORMAT_ANALYSIS.md](METADATAFORMAT_ANALYSIS.md)
- Issue #8: PHP 8.2 readonly migration

---

*Analysis generated on February 7, 2026*
