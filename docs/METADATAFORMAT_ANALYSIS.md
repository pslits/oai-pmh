# MetadataFormat Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** MetadataFormat Value Object  
**File:** `src/Domain/ValueObject/MetadataFormat.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0 - ListMetadataFormats](http://www.openarchives.org/OAI/openarchivesprotocol.html#ListMetadataFormats)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification section 4.5 (ListMetadataFormats):

> This verb is used to retrieve the metadata formats available from a repository. An optional argument restricts the request to formats available for a specific item.
> 
> **metadataPrefix** - A string to specify the unique identifier of the metadata format in OAI-PMH requests issued to the repository.  
> **schema** - The URL of an XML schema describing the metadata format.  
> **metadataNamespace** - The XML namespace URI of the format.

### Key Requirements

- ✅ Must have a unique metadataPrefix
- ✅ Must specify schema location (XSD)
- ✅ Must define one or more XML namespaces
- ✅ Must specify root element tag
- ✅ Immutable representation of format declaration

### XML Example

```xml
<ListMetadataFormats>
  <metadataFormat>
    <metadataPrefix>oai_dc</metadataPrefix>
    <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
    <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
  </metadataFormat>
  <metadataFormat>
    <metadataPrefix>mods</metadataPrefix>
    <schema>http://www.loc.gov/standards/mods/v3/mods-3-7.xsd</schema>
    <metadataNamespace>http://www.loc.gov/mods/v3</metadataNamespace>
  </metadataFormat>
</ListMetadataFormats>
```

### Common Metadata Formats

| Format | Prefix | Purpose | Schema |
|--------|--------|---------|--------|
| Dublin Core | oai_dc | Simple metadata (required by spec) | OAI DC XSD |
| MODS | mods | Descriptive metadata | Library of Congress |
| MARC21 | marc21 | Bibliographic records | Library of Congress |
| METS | mets | Digital objects | Library of Congress |
| ESE | ese | Europeana Semantic Elements | Europeana |

---

## 2. User Story

**As a** repository administrator  
**When** I configure my OAI-PMH repository  
**Where** harvesters need to know what metadata formats I support  
**I want** to declare each metadata format with its prefix, schema, namespaces, and structure  
**Because** harvesters use this information to request records in the correct format  

### Acceptance Criteria

- [x] Format must have a unique metadataPrefix
- [x] Format must specify schema URL for validation
- [x] Format must define XML namespace(s)
- [x] Format must specify root element tag
- [x] Format must be immutable after creation
- [x] Formats can be compared for equality
- [x] Format extends ContainerFormat base class

---

## 3. Implementation Details

### File Structure
```
src/Domain/ValueObject/MetadataFormat.php (62 lines)
src/Domain/ValueObject/ContainerFormat.php (base class, 130 lines)
tests/Domain/ValueObject/MetadataFormatTest.php
```

### Class Structure

```php
final class MetadataFormat extends ContainerFormat
{
    public function __construct(
        MetadataPrefix $prefix,
        MetadataNamespaceCollection $namespaces,
        AnyUri $schemaUrl,
        MetadataRootTag $rootTag
    )
    
    public function getPrefix(): MetadataPrefix  // Non-null for MetadataFormat
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Metadata Prefix** | Required (non-null) | Spec requirement | ✅ |
| **Namespaces** | Collection of namespace declarations | Multi-namespace support | ✅ |
| **Schema URL** | AnyUri value object | XSD location | ✅ |
| **Root Tag** | MetadataRootTag value object | XML structure | ✅ |
| **Immutability** | No setters, final class | Value object pattern | ✅ |
| **Inheritance** | Extends ContainerFormat | Code reuse | ✅ |

### Inheritance Hierarchy

```
ContainerFormat (abstract)
  ├── MetadataFormat (record-level, prefix required)
  ├── DescriptionFormat (repository-level, prefix optional)
  └── (future: AboutFormat, SetDescriptionFormat)
```

**Why MetadataFormat extends ContainerFormat:**
- Shares structure with other XML containers (about, description, setDescription)
- Different constraint: MetadataFormat **requires** prefix (non-null)
- Other container formats have optional prefix
- Code reuse for common behavior (equality, getters, toString)

### Validation Logic

```php
public function __construct(
    MetadataPrefix $prefix,  // Never null for MetadataFormat
    MetadataNamespaceCollection $namespaces,
    AnyUri $schemaUrl,
    MetadataRootTag $rootTag
) {
    parent::__construct($prefix, $namespaces, $schemaUrl, $rootTag);
}
```

**Validation Strategy:**
- Type system enforces prefix is MetadataPrefix (not null)
- All validation delegated to composed value objects
- MetadataPrefix validates format
- AnyUri validates schema URL
- MetadataNamespaceCollection validates namespaces
- MetadataRootTag validates tag name

### Relationship to Other Components

```
MetadataFormat
  │
  ├──> Requires MetadataPrefix (unique identifier)
  ├──> Contains MetadataNamespaceCollection (1+ namespaces)
  ├──> Has AnyUri (schema location)
  └──> Specifies MetadataRootTag (root element)
       │
       └──> Used in:
            ├── ListMetadataFormats response
            └── GetRecord/ListRecords (format selection)
```

---

## 4. Test Coverage Analysis

### Test Statistics

- **Total Tests:** 6
- **Assertions:** 12
- **Coverage:** 100% lines, 100% methods
- **Status:** ✅ All passing

### Test Categories

- ✅ **Constructor validation** (1 test)
  - Valid instantiation with all components
  
- ✅ **Getters** (1 test)
  - All getters return expected values
  - Prefix is non-null
  
- ✅ **Immutability** (1 test)
  - Getters return same instances
  
- ✅ **Equality** (2 tests)
  - Equal when all components match
  - Not equal when components differ
  
- ✅ **String representation** (1 test)

### Test Quality

**Strengths:**
- ✅ BDD-style Given-When-Then structure
- ✅ Tests inheritance from ContainerFormat
- ✅ Tests non-null prefix requirement
- ✅ Comprehensive assertion coverage
- ✅ 100% code coverage

---

## 5. Code Examples

### Basic Usage - Dublin Core

```php
use OaiPmh\Domain\ValueObject\MetadataFormat;
use OaiPmh\Domain\ValueObject\MetadataPrefix;
use OaiPmh\Domain\ValueObject\MetadataNamespace;
use OaiPmh\Domain\ValueObject\MetadataNamespaceCollection;
use OaiPmh\Domain\ValueObject\NamespacePrefix;
use OaiPmh\Domain\ValueObject\AnyUri;
use OaiPmh\Domain\ValueObject\MetadataRootTag;

// Dublin Core format (required by OAI-PMH spec)
$dublinCore = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    new MetadataNamespaceCollection(
        new MetadataNamespace(
            new NamespacePrefix('oai_dc'),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
        ),
        new MetadataNamespace(
            new NamespacePrefix('dc'),
            new AnyUri('http://purl.org/dc/elements/1.1/')
        )
    ),
    new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
    new MetadataRootTag('dc')
);

// Access format details
echo $dublinCore->getPrefix()->getValue();  // "oai_dc"
echo $dublinCore->getSchemaUrl()->getValue();  // "http://..."
echo count($dublinCore->getNamespaces());  // 2
```

### Advanced Usage - MODS

```php
// MODS (Metadata Object Description Schema)
$mods = new MetadataFormat(
    new MetadataPrefix('mods'),
    new MetadataNamespaceCollection(
        new MetadataNamespace(
            new NamespacePrefix('mods'),
            new AnyUri('http://www.loc.gov/mods/v3')
        )
    ),
    new AnyUri('http://www.loc.gov/standards/mods/v3/mods-3-7.xsd'),
    new MetadataRootTag('mods')
);
```

### Repository Configuration

```php
class RepositoryMetadataFormats
{
    private array $formats = [];
    
    public function __construct()
    {
        // Dublin Core (required)
        $this->formats[] = $this->createDublinCoreFormat();
        
        // MODS (optional)
        $this->formats[] = $this->createModsFormat();
        
        // Custom format (optional)
        $this->formats[] = $this->createCustomFormat();
    }
    
    public function getFormats(): array
    {
        return $this->formats;
    }
    
    public function getFormatByPrefix(string $prefix): ?MetadataFormat
    {
        foreach ($this->formats as $format) {
            if ($format->getPrefix()->getValue() === $prefix) {
                return $format;
            }
        }
        return null;
    }
    
    private function createDublinCoreFormat(): MetadataFormat
    {
        return new MetadataFormat(
            new MetadataPrefix('oai_dc'),
            new MetadataNamespaceCollection(
                new MetadataNamespace(
                    new NamespacePrefix('oai_dc'),
                    new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc/')
                ),
                new MetadataNamespace(
                    new NamespacePrefix('dc'),
                    new AnyUri('http://purl.org/dc/elements/1.1/')
                )
            ),
            new AnyUri('http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
            new MetadataRootTag('dc')
        );
    }
    
    private function createModsFormat(): MetadataFormat
    {
        // ... similar implementation
    }
    
    private function createCustomFormat(): MetadataFormat
    {
        // ... custom format definition
    }
}
```

### Format Equality

```php
$format1 = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    $namespaces,
    $schemaUrl,
    $rootTag
);

$format2 = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    $namespaces,
    $schemaUrl,
    $rootTag
);

var_dump($format1->equals($format2));  // true (value equality)
```

---

## 6. Design Decisions

### Decision 1: Extend ContainerFormat Base Class

**Context:** How to structure metadata format and related container types?

**Options Considered:**
1. Separate independent classes for each container type
2. Extend common base class (chosen)
3. Use interface only

**Rationale:**
- All XML containers share: namespaces, schema, root tag
- MetadataFormat requires prefix; others don't
- Base class provides: equality, toString, getters
- Reduces code duplication

**Implementation:**
```php
abstract class ContainerFormat
{
    protected ?MetadataPrefix $prefix;  // Nullable in base
    // ... common properties
}

final class MetadataFormat extends ContainerFormat
{
    public function __construct(
        MetadataPrefix $prefix,  // Non-null for MetadataFormat
        // ...
    ) {
        parent::__construct($prefix, ...);
    }
    
    public function getPrefix(): MetadataPrefix  // Non-null return type
    {
        return parent::getPrefix();  // Safe: always non-null here
    }
}
```

**Trade-offs:**
- ✅ Code reuse (DRY principle)
- ✅ Consistent behavior across container types
- ✅ Type safety maintained with overridden getter
- ⚠️ Slight complexity with nullable/non-null distinction
- ✅ Worth it for maintainability

### Decision 2: Require Prefix (Non-Null)

**Context:** Should MetadataFormat prefix be optional like base class?

**Options Considered:**
1. Keep prefix optional (nullable)
2. Require prefix (non-null) - chosen

**Rationale:**
- OAI-PMH spec requires metadataPrefix for metadata formats
- Other containers (about, description) don't use prefix
- Type safety: MetadataFormat always has prefix
- Fail-fast: cannot create invalid format

**Trade-offs:**
- ✅ Specification compliance
- ✅ Type safety guarantee
- ✅ Clear domain semantics
- ❌ Different from base class (intentional)

### Decision 3: Compose Complex Value Objects

**Context:** How to model format components?

**Options Considered:**
1. Use primitive types (string, array)
2. Compose value objects (chosen)

**Rationale:**
- Each component has its own validation
- MetadataPrefix validates format
- AnyUri validates URL structure
- MetadataNamespaceCollection validates namespace collection
- Separation of concerns

**Trade-offs:**
- ✅ Strong type safety
- ✅ Clear validation boundaries
- ✅ Testable components
- ✅ Domain-driven design
- ⚠️ More classes to manage
- ✅ Worth it for maintainability

### Decision 4: Immutable Value Object

**Context:** Should formats be mutable or immutable?

**Options Considered:**
1. Mutable (with setters)
2. Immutable (chosen)

**Rationale:**
- Value object pattern
- Formats don't change after declaration
- Thread-safe
- Easier to reason about

**Trade-offs:**
- ✅ Immutability guarantees
- ✅ No unexpected changes
- ✅ Simpler mental model
- ❌ Cannot modify (must create new instance)
- ✅ Correct for this domain

---

## 7. Known Issues & Future Enhancements

### Current Known Issues

None

### Future Enhancements

- [ ] **Issue #8**: Migrate to PHP 8.2 readonly properties (Priority: Low)
  ```php
  public readonly MetadataPrefix $prefix;
  public readonly MetadataNamespaceCollection $namespaces;
  // ...
  ```

- [ ] **Factory Methods** (Priority: Low)
  ```php
  public static function dublinCore(): self
  {
      return new self(/* standard DC config */);
  }
  
  public static function mods(): self
  {
      return new self(/* standard MODS config */);
  }
  ```

- [ ] **Format Registry** (Priority: Medium)
  - Central registry of standard formats
  - Validation against known formats
  - Format discovery

- [ ] **Schema Validation** (Priority: Low)
  - Validate that schema URL is accessible
  - Cache schema for performance
  - Not domain concern (infrastructure)

---

## 8. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | MetadataFormat | DescriptionFormat | ContainerFormat |
|--------|---------------|-------------------|-----------------|
| Prefix | Required (non-null) | Optional (nullable) | Optional (nullable) |
| Namespaces | Required collection | Required collection | Required collection |
| Schema URL | Required | Required | Required |
| Root Tag | Required | Required | Required |
| Use Case | Record metadata | Repository description | Base class |

### Why MetadataFormat vs DescriptionFormat?

**MetadataFormat:**
- Used for record-level metadata (GetRecord, ListRecords)
- **Requires** metadataPrefix
- Registered via ListMetadataFormats
- Examples: oai_dc, mods, marc21

**DescriptionFormat:**
- Used for repository-level descriptions (Identify)
- Prefix is **optional** (not used in protocol)
- Embedded in Identify response
- Examples: oai-identifier, branding, rights

---

## 9. Recommendations

### For Developers Using MetadataFormat VO

**DO:**
- ✅ Always provide valid MetadataPrefix
- ✅ Include all required XML namespaces
- ✅ Use publicly accessible schema URLs
- ✅ Follow naming conventions (lowercase with underscore)
- ✅ Reuse format instances (immutable)

```php
// ✅ Good: Complete format definition
$format = new MetadataFormat(
    new MetadataPrefix('oai_dc'),
    new MetadataNamespaceCollection(/* all namespaces */),
    new AnyUri('http://...schema.xsd'),
    new MetadataRootTag('dc')
);
```

**DON'T:**
- ❌ Don't use invalid prefixes
- ❌ Don't omit required namespaces
- ❌ Don't use inaccessible schema URLs
- ❌ Don't create new instances unnecessarily

### For Repository Administrators

- ✅ Always implement Dublin Core (oai_dc) - required by spec
- ✅ Use standard formats when possible (MODS, MARC21, etc.)
- ✅ Ensure schema URLs are publicly accessible
- ✅ Document custom format specifications
- ✅ Test format validation with harvesters
- ✅ Keep format definitions consistent across records

**Standard Format Examples:**
- Dublin Core: `oai_dc` (required)
- MODS: `mods` (recommended for rich metadata)
- MARC21: `marc21` (for library catalogs)
- ESE: `ese` (for Europeana)

### For Library Maintainers

- ✅ Maintain ContainerFormat base class carefully
- ✅ Keep MetadataFormat prefix non-null
- ✅ Consider factory methods for standard formats
- ✅ Document format registration patterns
- ✅ Provide format examples in documentation

---

## 10. References

### Specifications
- [OAI-PMH 2.0 - ListMetadataFormats](http://www.openarchives.org/OAI/openarchivesprotocol.html#ListMetadataFormats)
- [OAI-PMH 2.0 - Metadata](http://www.openarchives.org/OAI/openarchivesprotocol.html#metadata)

### Standard Metadata Formats
- [Dublin Core](http://dublincore.org/documents/dces/)
- [MODS](http://www.loc.gov/standards/mods/)
- [MARC21](http://www.loc.gov/marc/)
- [METS](http://www.loc.gov/standards/mets/)

### Related Analysis Documents
- [docs/METADATAPREFIX_ANALYSIS.md](METADATAPREFIX_ANALYSIS.md) - TBD
- [docs/METADATANAMESPACE_ANALYSIS.md](METADATANAMESPACE_ANALYSIS.md) - TBD
- [docs/METADATAROOTTAG_ANALYSIS.md](METADATAROOTTAG_ANALYSIS.md) - TBD
- [docs/CONTAINERFORMAT_ANALYSIS.md](CONTAINERFORMAT_ANALYSIS.md) - TBD (base class)

### Related GitHub Issues
- Issue #8: PHP 8.2 readonly property migration
- Issue #10: Define repository identity value object

---

## 11. Appendix

### Test Output

```
Metadata Format (OaiPmh\Tests\Domain\ValueObject\MetadataFormat)
 ✔ Can instantiate with valid arguments
 ✔ Getters return expected values
 ✔ Metadata format is immutable
 ✔ Metadata format equality by value
 ✔ Metadata format not equal when different
 ✔ To string returns expected format

OK (6 tests, 12 assertions)
```

### Coverage Report

```
MetadataFormat.php
  Lines: 100.00%
  Methods: 100.00%
  Classes: 100.00%
```

### PHPStan & CodeSniffer

```
PHPStan Level: 8 (max)
Errors: 0

PSR-12 Compliance: 100%
Errors: 0
Warnings: 0
```

### OAI-PMH XML Example

```xml
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/">
  <responseDate>2026-02-07T12:00:00Z</responseDate>
  <request verb="ListMetadataFormats">http://repository.example.org/oai</request>
  <ListMetadataFormats>
    <metadataFormat>
      <metadataPrefix>oai_dc</metadataPrefix>
      <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
      <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>mods</metadataPrefix>
      <schema>http://www.loc.gov/standards/mods/v3/mods-3-7.xsd</schema>
      <metadataNamespace>http://www.loc.gov/mods/v3</metadataNamespace>
    </metadataFormat>
  </ListMetadataFormats>
</OAI-PMH>
```

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*  
*Author: Paul Slits <paul.slits@gmail.com>*
