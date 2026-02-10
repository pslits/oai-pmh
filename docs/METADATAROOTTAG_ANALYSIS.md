# MetadataRootTag Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** MetadataRootTag Value Object  
**File:** `src/Domain/ValueObject/MetadataRootTag.php`  
**OAI-PMH Version:** 2.0  
**XML Specification:** [XML Names](https://www.w3.org/TR/xml-names/)  
**Related Spec:** [OAI-PMH 2.0 - Metadata](http://www.openarchives.org/OAI/openarchivesprotocol.html#metadata)

---

## Executive Summary

MetadataRootTag represents the root element name in XML metadata documents. It validates XML qualified names (QNames) including namespace-prefixed forms and provides value equality semantics. Used to specify the root element of metadata formats in OAI-PMH responses.

---

## 1. XML Requirements

### Key Requirements
- ✅ Must be valid XML element name (NCName or QName)
- ✅ Pattern: `[A-Za-z_][A-Za-z0-9_.-]*(:[A-Za-z_][A-Za-z0-9_.-]*)?`
- ✅ Supports unprefixed names: `dc`, `record`, `metadata`
- ✅ Supports prefixed names: `oai_dc:dc`, `mods:mods`

### Examples
- `dc` - Unprefixed Dublin Core root
- `oai_dc:dc` - Prefixed OAI Dublin Core root
- `mods` - MODS root element
- `record` - Generic record element

---

## 2. Implementation

### Class Structure
```php
class MetadataRootTag  // Note: not final (extensible)
{
    private string $rootTag;
    private const ROOT_TAG_PATTERN = 
        '/^[A-Za-z_][A-Za-z0-9_.-]*(:[A-Za-z_][A-Za-z0-9_.-]*)?$/';
    
    public function __construct(string $rootTag)
    public function getValue(): string
    public function equals(MetadataRootTag $other): bool
    public function __toString(): string
}
```

### Pattern Breakdown
```
^[A-Za-z_]            # Start: letter or underscore
[A-Za-z0-9_.-]*       # Continue: letters, digits, _, ., -
(:                    # Optional colon for namespace
  [A-Za-z_]           # After colon: letter or underscore
  [A-Za-z0-9_.-]*     # Continue: letters, digits, _, ., -
)?$
```

---

## 3. Test Coverage

**Tests:** 6 | **Assertions:** 7 | **Coverage:** 100%

✅ Valid root tag instantiation  
✅ Invalid root tag rejection  
✅ Value equality  
✅ Immutability  
✅ String representation  

---

## 4. Code Examples

```php
// ✅ Valid root tags - unprefixed
$dc = new MetadataRootTag('dc');
$mods = new MetadataRootTag('mods');
$record = new MetadataRootTag('record');

// ✅ Valid root tags - prefixed (QNames)
$qualified = new MetadataRootTag('oai_dc:dc');
$mods_prefixed = new MetadataRootTag('mods:mods');

// ✅ Valid with special chars
$versioned = new MetadataRootTag('record-v2.0');

// ❌ Invalid root tags
new MetadataRootTag('');  // Exception: empty
new MetadataRootTag('2dc');  // Exception: starts with digit
new MetadataRootTag('dc record');  // Exception: space
new MetadataRootTag('dc:');  // Exception: empty after colon
```

### Usage in MetadataFormat
```php
$format = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    $namespaces,
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
    new MetadataRootTag('dc')  // Root element of metadata
);
```

---

## 5. Design Decisions

**Decision: Support Both NCNames and QNames**
- NCName: unprefixed names like `dc`
- QName: prefixed names like `oai_dc:dc`
- Flexibility for various XML structures

**Decision: Not Final (Extensible)**
- Can be extended if needed
- Similar to NamespacePrefix design choice
- Future flexibility

**Decision: Regex Validation**
- Comprehensive pattern for XML compliance
- Clear error messages
- Prevents invalid XML generation

---

## 6. Comparison

| Aspect | MetadataRootTag | NamespacePrefix | MetadataPrefix |
|--------|----------------|-----------------|---------------|
| **Purpose** | XML root element | XML namespace prefix | OAI-PMH format ID |
| **Supports colons** | ✅ Yes (QName) | ❌ No | ❌ No |
| **Example** | `dc`, `oai_dc:dc` | `dc`, `oai_dc` | `oai_dc` |
| **Extensible** | ✅ Yes | ✅ Yes | ❌ No |

---

## 7. References

- [XML Names Spec](https://www.w3.org/TR/xml-names/)
- [docs/METADATAFORMAT_ANALYSIS.md](METADATAFORMAT_ANALYSIS.md)
- [docs/NAMESPACEPREFIX_ANALYSIS.md](NAMESPACEPREFIX_ANALYSIS.md)

---

*Analysis generated on February 7, 2026*
