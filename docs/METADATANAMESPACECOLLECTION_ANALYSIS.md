# MetadataNamespaceCollection Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** MetadataNamespaceCollection Value Object  
**File:** `src/Domain/ValueObject/MetadataNamespaceCollection.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - ListMetadataFormats](http://www.openarchives.org/OAI/openarchivesprotocol.html#ListMetadataFormats)

---

## Executive Summary

MetadataNamespaceCollection is a type-safe, immutable collection of MetadataNamespace objects. It enforces uniqueness constraints (no duplicate prefixes or URIs), requires at least one namespace, and provides order-insensitive equality semantics for namespace sets. Each metadata format must declare at least one XML namespace for proper element qualification in OAI-PMH responses.

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 4.5 (ListMetadataFormats):

> **metadataNamespace** - The XML namespace URI for the format.

While the specification shows a single namespace in examples, complex metadata formats may require multiple namespace declarations (e.g., Dublin Core with both oai_dc and dc namespaces).

---

## 2. Requirements

### Key Requirements
- ✅ Must contain at least 1 namespace
- ✅ No duplicate namespace prefixes
- ✅ No duplicate namespace URIs
- ✅ Immutable after creation
- ✅ Iterable (`foreach` support)
- ✅ Countable (`count()` support)
- ✅ Order-insensitive equality

### Use Cases
- Declare XML namespaces for metadata formats
- Define namespace mappings for complex schemas
- Support multiple namespace declarations

---

## 2. Implementation

### Class Structure
```php
final class MetadataNamespaceCollection implements IteratorAggregate, Countable
{
    private array $namespaces = [];
    
    public function __construct(MetadataNamespace ...$namespaces)
    public function getIterator(): ArrayIterator
    public function count(): int
    public function equals(self $other): bool
    public function __toString(): string
    private function validateNamespaces(array $namespaces): void
    private function toAssoc(array $namespaces): array
}
```

### Validation Logic

**1. Non-empty Requirement:**
```php
if (empty($namespaces)) {
    throw new InvalidArgumentException(
        'At least one MetadataNamespace must be provided.'
    );
}
```

**2. Unique Prefixes:**
```php
$prefixes = [];
foreach ($namespaces as $namespace) {
    $prefix = $namespace->getPrefix()->getValue();
    if (in_array($prefix, $prefixes, true)) {
        throw new InvalidArgumentException(
            "Duplicate namespace prefix found: $prefix"
        );
    }
    $prefixes[] = $prefix;
}
```

**3. Unique URIs:**
```php
$uris = [];
foreach ($namespaces as $namespace) {
    $uri = $namespace->getUri()->getValue();
    if (in_array($uri, $uris, true)) {
        throw new InvalidArgumentException(
            "Duplicate namespace URI found: $uri"
        );
    }
    $uris[] = $uri;
}
```

---

## 3. Test Coverage

**Tests:** 10 | **Assertions:** 13 | **Coverage:** 100%

✅ Valid instantiation  
✅ Empty collection rejection  
✅ Duplicate prefix rejection  
✅ Duplicate URI rejection  
✅ Order-insensitive equality  
✅ Different namespace inequality  
✅ Immutability  
✅ Iteration  
✅ String representation  

---

## 4. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;

// Dublin Core namespace collection
$namespaces = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('oai_dc'),
        new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
    ),
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    ),
    new MetadataNamespace(
        new NamespacePrefix('xsi'),
        new AnyUri('http://www.w3.org/2001/XMLSchema-instance')
    )
);

// Count namespaces
echo count($namespaces);  // 3

// Iterate
foreach ($namespaces as $namespace) {
    echo $namespace->getPrefix()->getValue() . ' => ';
    echo $namespace->getUri()->getValue() . PHP_EOL;
}
```

### Validation Examples

```php
// ✅ Valid: Single namespace
$single = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    )
);

// ✅ Valid: Multiple unique namespaces
$multiple = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    ),
    new MetadataNamespace(
        new NamespacePrefix('dcterms'),
        new AnyUri('http://purl.org/dc/terms/')
    )
);

// ❌ Invalid: Empty collection
$empty = new MetadataNamespaceCollection();  // Exception!

// ❌ Invalid: Duplicate prefix
$dc1 = new MetadataNamespace(
    new NamespacePrefix('dc'),
    new AnyUri('http://purl.org/dc/elements/1.1/')
);
$dc2 = new MetadataNamespace(
    new NamespacePrefix('dc'),  // Duplicate!
    new AnyUri('http://purl.org/dc/terms/')
);
$duplicate = new MetadataNamespaceCollection($dc1, $dc2);  // Exception!
```

### Order-Insensitive Equality

```php
$collection1 = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    ),
    new MetadataNamespace(
        new NamespacePrefix('xsi'),
        new AnyUri('http://www.w3.org/2001/XMLSchema-instance')
    )
);

$collection2 = new MetadataNamespaceCollection(
    new MetadataNamespace(
        new NamespacePrefix('xsi'),
        new AnyUri('http://www.w3.org/2001/XMLSchema-instance')
    ),
    new MetadataNamespace(
        new NamespacePrefix('dc'),
        new AnyUri('http://purl.org/dc/elements/1.1/')
    )
);

// Order doesn't matter
var_dump($collection1->equals($collection2));  // true
```

---

## 5. Design Decisions

### Decision 1: Order-Insensitive Equality

**Rationale:**
- XML namespace declarations are unordered
- Same set of namespaces = same meaning
- User-friendly comparison

**Implementation:**
```php
public function equals(self $other): bool
{
    // Convert to associative arrays (prefix => URI)
    $array1 = $this->toAssoc($this->namespaces);
    $array2 = $other->toAssoc($other->namespaces);
    
    // Sort by keys (prefixes) for comparison
    ksort($array1);
    ksort($array2);
    
    return $array1 === $array2;
}
```

### Decision 2: Require At Least One Namespace

**Rationale:**
- Metadata formats always need namespaces
- Fail-fast validation
- Type guarantee

### Decision 3: Prevent Both Prefix AND URI Duplicates

**Rationale:**
- Same prefix twice = ambiguous XML
- Same URI twice = redundant, likely error
- Both constraints prevent mistakes

**Different from EmailCollection:**
- EmailCollection: only checks duplicate emails
- This: checks BOTH prefix AND URI uniqueness

---

## 6. Comparison with Related Collections

| Aspect | MetadataNamespaceCollection | EmailCollection | DescriptionCollection |
|--------|---------------------------|-----------------|----------------------|
| Minimum size | 1 (required) | 1 (required) | 0 (optional) |
| Duplicates | No (prefix OR URI) | No | No |
| Equality | Order-insensitive | Order-insensitive | Order-sensitive |
| Use case | XML namespaces | Admin contacts | Repository descriptions |

---

## 7. Known Issues & Enhancements

- [ ] **Issue #8**: PHP 8.2 readonly properties
- [ ] Named constructor for common namespace sets

```php
public static function dublinCore(): self
{
    return new self(
        new MetadataNamespace(
            new NamespacePrefix('oai_dc'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
        ),
        new MetadataNamespace(
            new NamespacePrefix('dc'),
            new AnyUri('http://purl.org/dc/elements/1.1/')
        )
    );
}
```

---

## 8. References

- [docs/METADATANAMESPACE_ANALYSIS.md](METADATANAMESPACE_ANALYSIS.md)
- [docs/METADATAFORMAT_ANALYSIS.md](METADATAFORMAT_ANALYSIS.md)
- [docs/EMAILCOLLECTION_ANALYSIS.md](EMAILCOLLECTION_ANALYSIS.md)

---

*Analysis generated on February 7, 2026*
