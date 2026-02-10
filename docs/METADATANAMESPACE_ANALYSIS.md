# MetadataNamespace Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** MetadataNamespace Value Object  
**File:** `src/Domain/ValueObject/MetadataNamespace.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - ListMetadataFormats](http://www.openarchives.org/OAI/openarchivesprotocol.html#ListMetadataFormats)

---

## Executive Summary

MetadataNamespace is a composite value object pairing a NamespacePrefix with an AnyUri to represent an XML namespace declaration. It ensures valid namespace mappings for metadata formats in OAI-PMH responses.

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 4.5 (ListMetadataFormats):

> **metadataNamespace** - The XML namespace URI for the format.

Each metadata format must declare its XML namespace(s) to properly qualify elements in the metadata payload.

---

## 2. Purpose & Requirements

### Key Requirements
- ✅ Combines prefix + URI pair
- ✅ Both components must be valid
- ✅ Immutable once created
- ✅ Value equality (both prefix and URI must match)

### XML Context
```xml
<dc:title xmlns:dc="http://purl.org/dc/elements/1.1/">
   <!--     ^^^^^^  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^  -->
   <!--     prefix  URI (namespace)                   -->
</dc:title>
```

---

## 2. Implementation

### Class Structure
```php
final class MetadataNamespace
{
    private NamespacePrefix $prefix;
    private AnyUri $uri;
    
    public function __construct(NamespacePrefix $prefix, AnyUri $uri)
    public function getPrefix(): NamespacePrefix
    public function getUri(): AnyUri
    public function equals(MetadataNamespace $other): bool
    public function __toString(): string
}
```

### Composition
- Delegates validation to `NamespacePrefix` and `AnyUri`
- No additional validation needed
- Pure composition pattern

---

## 3. Test Coverage

**Tests:** 6 | **Assertions:** 9 | **Coverage:** 100%

✅ Valid instantiation with prefix and URI  
✅ Getters return expected values  
✅ Immutability  
✅ Value equality  
✅ String representation  

---

## 4. Code Examples

```php
// Dublin Core namespace
$dc = new MetadataNamespace(
    new NamespacePrefix('dc'),
    new AnyUri('http://purl.org/dc/elements/1.1/')
);

// OAI-DC namespace
$oai_dc = new MetadataNamespace(
    new NamespacePrefix('oai_dc'),
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
);

// XSI namespace (for schemaLocation)
$xsi = new MetadataNamespace(
    new NamespacePrefix('xsi'),
    new AnyUri('http://www.w3.org/2001/XMLSchema-instance')
);

// Access components
echo $dc->getPrefix()->getValue();  // "dc"
echo $dc->getUri()->getValue();     // "http://purl.org/dc/elements/1.1/"

// Equality
$dc2 = new MetadataNamespace(
    new NamespacePrefix('dc'),
    new AnyUri('http://purl.org/dc/elements/1.1/')
);
var_dump($dc->equals($dc2));  // true
```

---

## 5. Design Decisions

**Decision: Composite Value Object**
- Combines two separate concerns (prefix + URI)
- Each validates independently
- Clean separation of responsibilities

**Decision: Final Class**
- Pure value object, no need for extension
- Immutable by design
- Type-safe

---

## 6. Usage in Collections

```php
$namespaces = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('oai_dc'),
        new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
    ),
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    )
);
```

---

## 7. References

- [docs/NAMESPACEPREFIX_ANALYSIS.md](NAMESPACEPREFIX_ANALYSIS.md)
- [docs/ANYURI_ANALYSIS.md](ANYURI_ANALYSIS.md)
- [docs/METADATANAMESPACE COLLECTION_ANALYSIS.md](METADATANAMESPACECOLLECTION_ANALYSIS.md)

---

*Analysis generated on February 7, 2026*
