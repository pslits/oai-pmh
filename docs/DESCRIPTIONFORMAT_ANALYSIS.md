# DescriptionFormat Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** DescriptionFormat Value Object  
**File:** `src/Domain/ValueObject/DescriptionFormat.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

---

## Executive Summary

DescriptionFormat extends ContainerFormat to represent repository-level description formats in OAI-PMH Identify responses. Unlike MetadataFormat (which requires a metadataPrefix), DescriptionFormat has an optional prefix since descriptions are embedded directly in Identify responses.

---

## 1. OAI-PMH Requirement

### Key Requirements
- ✅ Defines structure for repository descriptions
- ✅ No metadataPrefix required (embedded in Identify, not harvested separately)
- ✅ Specifies XML namespaces, schema, root tag
- ✅ Common formats: oai-identifier, branding, rights

### XML Example

```xml
<Identify>
  <!-- ... other Identify elements ... -->
  <description>
    <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier">
      <scheme>oai</scheme>
      <repositoryIdentifier>example.org</repositoryIdentifier>
    </oai-identifier>
  </description>
</Identify>
```

---

## 2. Implementation

### Class Structure
```php
final class DescriptionFormat extends ContainerFormat
{
    public function __construct(
        MetadataNamespaceCollection $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    ) {
        // Note: prefix is null for DescriptionFormat
        parent::__construct(null, $namespaces, $schemaUrl, $rootTag);
    }
    
    // Inherits from ContainerFormat:
    // - getPrefix(): ?MetadataPrefix (returns null)
    // - getNamespaces(): MetadataNamespaceCollection
    // - getSchemaUrl(): AnyUri
    // - getRootTag(): MetadataRootTag
    // - equals(ContainerFormat $other): bool
    // - __toString(): string
}
```

### Key Difference from MetadataFormat

| Aspect | DescriptionFormat | MetadataFormat |
|--------|------------------|---------------|
| **Prefix** | ❌ No prefix (null) | ✅ Required prefix |
| **Constructor** | 3 params (no prefix) | 4 params (with prefix) |
| **Use case** | Repository descriptions | Record metadata |
| **Harvested by** | N/A (embedded in Identify) | GetRecord, ListRecords |

---

## 3. Test Coverage

**Tests:** 6 | **Assertions:** 10 | **Coverage:** 100%

✅ Valid instantiation (OAI-identifier example)  
✅ Equality (same values)  
✅ Inequality (different namespaces)  
✅ Inequality (different schema)  
✅ Inequality (different root tag)  
✅ String representation  

---

## 4. Code Examples

### OAI-Identifier Description Format

```php
use OaiPmh\Domain\ValueObject\DescriptionFormat;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;

$oaiIdentifierFormat = new DescriptionFormat(
    new MetadataNamespaceCollection(
        new MetadataNamespace(
            new NamespacePrefix('oai-identifier'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
        ),
        new MetadataNamespace(
            new NamespacePrefix('xsi'),
            new AnyUri('http://www.w3.org/2001/XMLSchema-instance')
        )
    ),
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd'),
    new MetadataRootTag('oai-identifier')
);

// No prefix (returns null)
var_dump($oaiIdentifierFormat->getPrefix());  // null

// Has namespaces, schema, root tag
echo $oaiIdentifierFormat->getRootTag()->getValue();  // "oai-identifier"
```

### Branding Description Format

```php
$brandingFormat = new DescriptionFormat(
    new MetadataNamespaceCollection(
        new MetadataNamespace(
            new NamespacePrefix('branding'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/branding/')
        )
    ),
    new AnyUri('http://www.openarchives.org/OAI/2.0/branding.xsd'),
    new MetadataRootTag('branding')
);
```

### Usage with Description

```php
// Combine format with data
$xmlData = '<oai-identifier>...</oai-identifier>';
$description = new Description($oaiIdentifierFormat, $xmlData);

// Add to collection
$descriptions = new DescriptionCollection($description);
```

---

## 5. Design Decisions

**Decision: Extend ContainerFormat**
- Shares structure with MetadataFormat
- Reuses equality, toString, getters
- Only difference: no prefix parameter

**Decision: Null Prefix**
- Descriptions don't use metadataPrefix in protocol
- Embedded in Identify, not harvested separately
- No need for prefix identifier

**Decision: Final Class**
- No further specialization needed
- Clear domain boundary
- Type-safe

---

## 6. Inheritance Hierarchy

```
ContainerFormat (abstract)
  │
  ├── MetadataFormat (prefix required)
  │   └── Used in: ListMetadataFormats, GetRecord, ListRecords
  │
  └── DescriptionFormat (prefix null)
      └── Used in: Identify (description element)
```

---

## 7. Common Description Formats

| Format | Root Tag | Purpose |
|--------|----------|---------|
| oai-identifier | `oai-identifier` | Repository ID scheme |
| branding | `branding` | Logo, icons, branding |
| rights | `rights` | Usage rights, licensing |
| provenance | `provenance` | Content provenance |
| Custom | varies | Institution-specific |

---

## 8. References

- [OAI-PMH 2.0 - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [OAI-Identifier Spec](http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm)
- [docs/DESCRIPTION_ANALYSIS.md](DESCRIPTION_ANALYSIS.md)
- [docs/CONTAINERFORMAT_ANALYSIS.md](CONTAINERFORMAT_ANALYSIS.md)
- [docs/METADATAFORMAT_ANALYSIS.md](METADATAFORMAT_ANALYSIS.md)
- Issue #8: PHP 8.2 readonly migration

---

*Analysis generated on February 7, 2026*
