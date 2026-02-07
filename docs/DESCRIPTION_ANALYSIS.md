# Description Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** Description Value Object  
**File:** `src/Domain/ValueObject/Description.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)

---

## Executive Summary

Description is a composite value object combining a DescriptionFormat with arbitrary XML data. It represents repository-level descriptive information in OAI-PMH Identify responses, supporting extensible metadata about the repository itself.

---

## 1. OAI-PMH Requirement

### Key Requirements
- ✅ Combines format specification with data
- ✅ Format defines XML structure (namespaces, schema, root tag)
- ✅ Data contains actual description content
- ✅ Used in Identify response (0..* descriptions)

### Common Description Types
- `oai-identifier` - Repository identifier scheme
- `branding` - Repository branding/logo
- `rights` - Usage rights information
- `provenance` - Content provenance
- Custom formats as needed

---

## 2. Implementation

### Class Structure
```php
final class Description
{
    private DescriptionFormat $descriptionFormat;
    private string $data;
    
    public function __construct(DescriptionFormat $descriptionFormat, string $data)
    public function getDescriptionFormat(): DescriptionFormat
    public function getData(): string
    public function equals(Description $other): bool
    public function __toString(): string
}
```

### Design
- Pairs format metadata with actual content
- Format describes structure, data is the content
- Both must match for equality
- Immutable after creation

---

## 3. Test Coverage

**Tests:** 6 | **Assertions:** 9 | **Coverage:** 100%

✅ Valid instantiation  
✅ Immutability  
✅ Equality (same format + data)  
✅ Inequality (different format)  
✅ Inequality (different data)  
✅ String representation  

---

## 4. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\Description;
use OaiPmh\Domain\ValueObject\DescriptionFormat;

// Create description format
$oaiIdFormat = new DescriptionFormat(
    new MetadataNamespaceCollection(
        new MetadataNamespace(
            new NamespacePrefix('oai-identifier'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier')
        )
    ),
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai-identifier.xsd'),
    new MetadataRootTag('oai-identifier')
);

// XML data for the description
$xmlData = <<<XML
<oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier">
  <scheme>oai</scheme>
  <repositoryIdentifier>example.org</repositoryIdentifier>
  <delimiter>:</delimiter>
  <sampleIdentifier>oai:example.org:12345</sampleIdentifier>
</oai-identifier>
XML;

// Create description
$description = new Description($oaiIdFormat, $xmlData);

// Access components
echo $description->getDescriptionFormat()->getRootTag()->getValue();  // "oai-identifier"
echo $description->getData();  // XML string
```

### Multiple Descriptions in Collection

```php
// Used in Identify response
$descriptions = new DescriptionCollection(
    $oaiIdentifierDescription,
    $brandingDescription,
    $rightsDescription
);
```

---

## 5. Design Decisions

**Decision: Separate Format from Data**
- Format = structural metadata (how to interpret)
- Data = actual content (what to interpret)
- Clean separation of concerns

**Decision: String Data Type**
- XML is stored as string
- No parsing at value object level
- Parsing is infrastructure concern

**Decision: Immutable**
- Value object pattern
- No modifications after creation
- Thread-safe

---

## 6. Comparison

| Aspect | Description | DescriptionFormat |
|--------|-------------|-------------------|
| **Purpose** | Format + data pair | Format specification only |
| **Contains data** | ✅ Yes | ❌ No |
| **Has prefix** | Via format (optional) | ✅ Optional |
| **Use case** | Identify response | Format definition |

---

## 7. References

- [OAI-PMH 2.0 - Identify](http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify)
- [docs/DESCRIPTIONFORMAT_ANALYSIS.md](DESCRIPTIONFORMAT_ANALYSIS.md)
- [docs/DESCRIPTIONCOLLECTION_ANALYSIS.md](DESCRIPTIONCOLLECTION_ANALYSIS.md)
- Issue #8: PHP 8.2 readonly migration

---

*Analysis generated on February 7, 2026*
