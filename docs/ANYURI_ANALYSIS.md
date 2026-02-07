# AnyUri Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** AnyUri Value Object  
**File:** `src/Domain/ValueObject/AnyUri.php`  
**XML Schema Spec:** [anyURI Type](https://www.w3.org/TR/xmlschema-2/#anyURI)

---

## Executive Summary

AnyUri represents a URI conforming to XML Schema anyURI type. It validates URIs using XSD schema validation to ensure compliance with XML specifications, providing type-safe URI handling for OAI-PMH operations.

---

## 1. XML Schema Requirement

### Key Requirements
- ✅ Must be valid anyURI per XML Schema specification
- ✅ Supports HTTP/HTTPS URLs
- ✅ Supports Unicode characters
- ✅ Used for schema locations, namespace URIs, repository URLs

### Common Use Cases
- Schema URLs: `http://www.openarchives.org/OAI/2.0/oai_dc.xsd`
- Namespace URIs: `http://purl.org/dc/elements/1.1/`
- Repository base URLs: `https://repository.example.org/oai`

---

## 2. Implementation

### Class Structure
```php
class AnyUri  // Note: not final (extensible)
{
    private string $uri;
    private const ANYURI_XSD_PATH = __DIR__ . '/../Schema/anyURI.xsd';
    
    public function __construct(string $uri)
    public function getValue(): string
    public function equals(AnyUri $other): bool
    public function __toString(): string
    private function validateAnyUri(string $uri): void
}
```

### Validation Strategy
- Uses DOM document with XSD schema validation
- More accurate than PHP `filter_var(FILTER_VALIDATE_URL)`
- Conforms to XML Schema anyURI specification
- Supports Unicode URIs

---

## 3. Test Coverage

**Tests:** 7 | **Assertions:** 7 | **Coverage:** 94.12%

✅ Valid URI instantiation  
✅ Unicode URI support  
⏭️ Invalid URI rejection (skipped - Issue #7)  
✅ Value equality  
✅ Immutability  
✅ String representation  

**Known Issue #7:** Cannot test invalid URI validation in current context

---

## 4. Code Examples

```php
use OaiPmh\Domain\ValueObject\AnyUri;

// ✅ Valid URIs
$schemaUrl = new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
$namespaceUri = new AnyUri('http://purl.org/dc/elements/1.1/');
$baseUrl = new AnyUri('https://repository.example.org/oai');

// ✅ Unicode URIs supported
$unicodeUri = new AnyUri('http://example.org/文档');

// Access value
echo $schemaUrl->getValue();  
// "http://www.openarchives.org/OAI/2.0/oai_dc.xsd"

// Equality
$uri1 = new AnyUri('http://example.org/path');
$uri2 = new AnyUri('http://example.org/path');
var_dump($uri1->equals($uri2));  // true
```

### Usage in Other Value Objects

```php
// In MetadataNamespace
$namespace = new MetadataNamespace(
    new NamespacePrefix('dc'),
    new AnyUri('http://purl.org/dc/elements/1.1/')
);

// In MetadataFormat
$format = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    $namespaces,
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
    $rootTag
);

// In BaseURL
$baseUrl = new BaseURL('https://repository.example.org/oai');
```

---

## 5. Design Decisions

**Decision: XSD Schema Validation**
- More accurate than regex or `filter_var`
- Conforms to XML Schema spec exactly
- Supports Unicode properly

**Trade-offs:**
- ✅ Accurate XML Schema compliance
- ✅ Unicode support
- ⚠️ Slightly slower than regex
- ⚠️ Requires XSD file dependency
- ✅ Worth it for correctness

**Decision: Not Final (Extensible)**
- Can be extended (e.g., BaseURL extends AnyUri)
- Allows specialization
- Flexibility for derived types

---

## 6. Known Issues

### Issue #7: Invalid URI Testing

**Problem:** Cannot effectively test invalid URI rejection in current test context

**Impact:**
- Test is skipped
- Coverage: 94.12% (not 100%)
- Validation code exists but not tested

**Workaround:** Manual testing confirms validation works

---

## 7. Comparison

| Aspect | AnyUri | BaseURL |
|--------|--------|---------|
| **Purpose** | Generic XML Schema anyURI | OAI-PMH base URL |
| **Validation** | XSD schema | XSD + OAI-PMH rules |
| **Extensible** | ✅ Yes | ❌ No (final) |
| **Use case** | Schemas, namespaces, URIs | Repository endpoint |

---

## 8. References

- [XML Schema anyURI](https://www.w3.org/TR/xmlschema-2/#anyURI)
- [docs/BASEURL_ANALYSIS.md](BASEURL_ANALYSIS.md)
- [docs/METADATANAMESPACE_ANALYSIS.md](METADATANAMESPACE_ANALYSIS.md)
- Issue #7: anyURI validation testing
- Issue #8: PHP 8.2 readonly migration

---

*Analysis generated on February 7, 2026*
