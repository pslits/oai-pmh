# XML Serialization Architecture Analysis

**Date:** February 6, 2026  
**Component:** DescriptionCollection and Domain Layer  
**Question:** Should XML serialization be in the Domain layer or outside?  
**Answer:** ✅ **Keep it OUTSIDE the Domain Layer** (Current approach is CORRECT)

---

## Executive Summary

**The current design is architecturally sound and follows Domain-Driven Design (DDD) best practices.**

XML serialization should **NOT** be added to the domain layer. The domain layer should remain:
- ✅ Technology-agnostic
- ✅ Focused on business logic and rules
- ✅ Independent of transport/presentation concerns

---

## 1. Current Architecture (Correct Approach)

### 1.1 Separation of Concerns

```
┌─────────────────────────────────────────────────────┐
│               PRESENTATION LAYER                     │
│  (XML Serialization, HTTP, Response Formatting)     │
│                                                      │
│  - OaiResponseSerializer                            │
│  - IdentifyResponseBuilder                          │
│  - DescriptionXmlSerializer  ← NEW (future)         │
└─────────────────────────────────────────────────────┘
                        ↓ uses
┌─────────────────────────────────────────────────────┐
│               APPLICATION LAYER                      │
│         (Use Cases, Orchestration)                  │
│                                                      │
│  - IdentifyHandler                                  │
│  - ListMetadataFormatsHandler                       │
└─────────────────────────────────────────────────────┘
                        ↓ uses
┌─────────────────────────────────────────────────────┐
│                DOMAIN LAYER                         │
│      (Business Logic, Value Objects)                │
│                                                      │
│  ✅ DescriptionCollection                           │
│  ✅ Description                                      │
│  ✅ DescriptionFormat                                │
│  ✅ All Value Objects                                │
│                                                      │
│  NO XML CONCERNS HERE!                              │
└─────────────────────────────────────────────────────┘
```

### 1.2 Evidence from Codebase

Looking at the existing domain objects, **ALL explicitly state**:

```php
/**
 * Domain concerns such as XML serialization or protocol transport 
 * are handled outside this class.
 */
```

**Examples:**
- `MetadataPrefix.php`: "Domain concerns such as XML serialization or protocol transport are handled outside this class."
- `MetadataNamespace.php`: "Domain concerns such as XML serialization or protocol transport are handled outside this class."
- `MetadataNamespaceCollection.php`: "Concerns such as XML serialization or protocol transport are handled outside the domain layer."
- `MetadataFormat.php`: "Concerns such as XML serialization, protocol transport, or I/O are handled outside the domain layer."
- `NamespacePrefix.php`: "Domain concerns such as XML serialization or protocol transport are handled outside this class."

**Only Exception:** `AnyUri.php` uses `DOMDocument` for **validation purposes only** (validating against XSD schema), NOT for serialization.

---

## 2. Why XML Should Stay Outside Domain

### 2.1 Domain-Driven Design Principles

| Principle | Explanation | Violated if XML in Domain |
|-----------|-------------|---------------------------|
| **Single Responsibility** | Domain objects model business concepts | ❌ Would also handle technical formatting |
| **Technology Independence** | Domain should work with JSON, XML, gRPC, etc. | ❌ Coupled to XML technology |
| **Pure Business Logic** | Domain = "what", not "how" | ❌ Mixed with presentation logic |
| **Testability** | Test business rules without I/O | ❌ Would need XML assertions in domain tests |
| **Bounded Context** | OAI-PMH domain ≠ XML serialization domain | ❌ Context boundary violated |

### 2.2 Hexagonal Architecture (Ports & Adapters)

```
         ┌──────────────────────────┐
         │    DOMAIN CORE           │
         │  (DescriptionCollection) │
         │  Pure business logic     │
         └──────────────────────────┘
                    ↑
                    │ Port (interface)
                    │
         ┌──────────┴──────────┐
         │                     │
    ┌────▼────┐          ┌────▼────┐
    │ XML     │          │ JSON    │
    │ Adapter │          │ Adapter │
    └─────────┘          └─────────┘
```

**Benefits:**
- ✅ Can swap XML for JSON without touching domain
- ✅ Can use different XML libraries (DOMDocument, XMLWriter, SimpleXML)
- ✅ Can support multiple OAI-PMH protocol versions
- ✅ Domain remains stable when serialization changes

### 2.3 Real-World Scenarios

#### Scenario 1: Protocol Evolution
**OAI-PMH 3.0 uses JSON instead of XML**
- ✅ With separation: Change presentation layer only
- ❌ With XML in domain: Rewrite all domain objects

#### Scenario 2: Multiple Output Formats
**Need both XML and JSON-LD for semantic web**
- ✅ With separation: Add `JsonLdSerializer`
- ❌ With XML in domain: Add `toJson()`, `toJsonLd()`, `toRdf()` to every class

#### Scenario 3: Performance Optimization
**Need to use streaming XML for large responses**
- ✅ With separation: Swap `XmlSerializer` implementation
- ❌ With XML in domain: Change all domain objects

#### Scenario 4: Testing
**Unit test business logic**
- ✅ With separation: `assertEquals($expected, $collection->toArray())`
- ❌ With XML in domain: Parse XML strings in every test

---

## 3. How to Implement XML Serialization (Outside Domain)

### 3.1 Recommended Approach: Serializer Pattern

**Create in `src/Infrastructure/Serialization/` or `src/Presentation/Serializer/`:**

```php
<?php

namespace OaiPmh\Infrastructure\Serialization;

use DOMDocument;
use DOMElement;
use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Domain\ValueObject\Description;

/**
 * Serializes DescriptionCollection to OAI-PMH XML format.
 * 
 * This is an infrastructure concern, NOT a domain concern.
 */
final class DescriptionCollectionXmlSerializer
{
    /**
     * Serializes a DescriptionCollection to XML elements.
     * 
     * @param DescriptionCollection $collection The collection to serialize.
     * @param DOMDocument $document The XML document to append to.
     * @param DOMElement $parentElement The parent element to append descriptions to.
     * @return void
     */
    public function serialize(
        DescriptionCollection $collection,
        DOMDocument $document,
        DOMElement $parentElement
    ): void {
        foreach ($collection as $description) {
            $this->serializeDescription($description, $document, $parentElement);
        }
    }

    /**
     * Serializes a single Description to XML.
     * 
     * @param Description $description The description to serialize.
     * @param DOMDocument $document The XML document.
     * @param DOMElement $parentElement The parent element.
     * @return void
     */
    private function serializeDescription(
        Description $description,
        DOMDocument $document,
        DOMElement $parentElement
    ): void {
        // Create <description> container
        $descElement = $document->createElement('description');
        $parentElement->appendChild($descElement);

        $format = $description->getDescriptionFormat();
        $data = $description->getData();

        // Create root element with namespaces
        $rootTag = $format->getRootTag()->getValue();
        $rootElement = $document->createElement($rootTag);
        $descElement->appendChild($rootElement);

        // Add namespace declarations
        foreach ($format->getNamespaces() as $namespace) {
            $prefix = $namespace->getPrefix()->getValue();
            $uri = $namespace->getUri()->getValue();
            $rootElement->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                "xmlns:$prefix",
                $uri
            );
        }

        // Add schema location
        $rootElement->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            $format->getNamespaces()->toArray()[0]->getUri()->getValue() . 
            ' ' . 
            $format->getSchemaUrl()->getValue()
        );

        // Serialize data array to XML elements
        $this->arrayToXml($data, $document, $rootElement);
    }

    /**
     * Converts a data array to XML elements.
     * 
     * @param array<string, mixed> $data The data array.
     * @param DOMDocument $document The XML document.
     * @param DOMElement $parent The parent element.
     * @return void
     */
    private function arrayToXml(array $data, DOMDocument $document, DOMElement $parent): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $document->createElement($key);
                $parent->appendChild($child);
                $this->arrayToXml($value, $document, $child);
            } else {
                $child = $document->createElement($key, htmlspecialchars((string)$value));
                $parent->appendChild($child);
            }
        }
    }
}
```

### 3.2 Usage Example

```php
<?php

namespace OaiPmh\Presentation\Http;

use OaiPmh\Domain\ValueObject\DescriptionCollection;
use OaiPmh\Infrastructure\Serialization\DescriptionCollectionXmlSerializer;
use DOMDocument;

class IdentifyResponseBuilder
{
    private DescriptionCollectionXmlSerializer $descriptionSerializer;

    public function __construct(DescriptionCollectionXmlSerializer $descriptionSerializer)
    {
        $this->descriptionSerializer = $descriptionSerializer;
    }

    public function buildResponse(/* repository identity */): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        // Create OAI-PMH root
        $root = $document->createElement('OAI-PMH');
        $document->appendChild($root);

        // Create Identify element
        $identify = $document->createElement('Identify');
        $root->appendChild($identify);

        // Add required elements
        // ... repositoryName, baseURL, etc ...

        // Add optional descriptions using serializer
        $this->descriptionSerializer->serialize(
            $repositoryIdentity->getDescriptions(),  // DescriptionCollection
            $document,
            $identify
        );

        return $document->saveXML();
    }
}
```

### 3.3 Alternative: Visitor Pattern

If you need more flexibility:

```php
<?php

interface XmlSerializable
{
    public function acceptSerializer(XmlSerializerVisitor $visitor): void;
}

interface XmlSerializerVisitor
{
    public function visitDescriptionCollection(DescriptionCollection $collection): void;
    public function visitDescription(Description $description): void;
    // ... other visit methods
}

// Still in infrastructure layer, but more extensible
```

---

## 4. Benefits of External Serialization

### 4.1 For Domain Layer

| Benefit | Impact |
|---------|--------|
| **Purity** | Domain remains focused on business rules |
| **Testability** | Simple assertions on arrays/primitives |
| **Reusability** | Same domain objects for XML, JSON, gRPC |
| **Maintainability** | Changes to XML format don't affect domain |
| **Performance** | Can optimize serialization separately |

### 4.2 For Serialization Layer

| Benefit | Impact |
|---------|--------|
| **Flexibility** | Can use any XML library (DOM, XMLWriter, SimpleXML) |
| **Optimization** | Streaming, caching, lazy loading |
| **Validation** | Schema validation separate from domain validation |
| **Transformation** | Can apply XSLT, formatting, pretty-printing |
| **Evolution** | Support multiple OAI-PMH versions simultaneously |

---

## 5. What the Domain SHOULD Provide

The domain layer should provide **everything needed for serialization**, but not the serialization itself:

### ✅ What Domain Provides (Current Implementation)

```php
// DescriptionCollection provides:
- count(): int                    // How many descriptions
- getIterator(): ArrayIterator    // Iterate over descriptions
- toArray(): array                // Get all descriptions as array
- equals(): bool                  // Value comparison

// Description provides:
- getDescriptionFormat(): DescriptionFormat  // Schema/structure info
- getData(): array                           // Actual data to serialize

// DescriptionFormat provides:
- getNamespaces(): MetadataNamespaceCollection  // xmlns declarations
- getSchemaUrl(): AnyUri                         // xsi:schemaLocation
- getRootTag(): MetadataRootTag                  // Root element name
```

**This is PERFECT for serialization!** The serializer can:
1. Iterate over the collection
2. Get format metadata (namespaces, schema, root tag)
3. Get data array
4. Build XML from this information

### ❌ What Domain Should NOT Provide

```php
// DON'T ADD THESE to domain objects:
- toXml(): string
- toDOMElement(): DOMElement
- writeXml(XMLWriter $writer): void
- appendToDocument(DOMDocument $doc, DOMElement $parent): void
```

**Why not?**
- Violates Single Responsibility Principle
- Couples domain to XML technology
- Makes testing harder
- Prevents alternative serialization formats
- Violates DDD principles

---

## 6. Comparison with Other DDD Libraries

### 6.1 Symfony Serializer (Industry Standard)

```php
// Symfony approach: Domain objects are DUMB data containers
class Product {
    private string $name;
    private Money $price;
    // No toXml(), toJson() - just getters!
}

// Serialization is handled externally
$serializer = new Serializer([...]);
$xml = $serializer->serialize($product, 'xml');
$json = $serializer->serialize($product, 'json');
```

### 6.2 Doctrine ORM (Industry Standard)

```php
// Doctrine entities: NO database code in domain
class User {
    private string $email;
    // No save(), update(), delete() - pure domain logic!
}

// Persistence handled externally
$entityManager->persist($user);
$entityManager->flush();
```

### 6.3 Your OAI-PMH Library (Correct Approach)

```php
// Domain: Pure value objects
class DescriptionCollection {
    private array $descriptions;
    // No toXml() - just business logic!
}

// Serialization handled externally
$serializer = new DescriptionCollectionXmlSerializer();
$xml = $serializer->serialize($collection, $document, $parent);
```

**Your library follows the same pattern as industry-standard frameworks!** ✅

---

## 7. Addressing Common Concerns

### Concern 1: "But it's convenient to call `$collection->toXml()`!"

**Response:** Convenience ≠ Good Architecture

- ✅ **Better:** `$serializer->toXml($collection)`
- **Why:** Separates concerns, testable, flexible

**Example:**
```php
// Bad (convenient but coupled)
$xml = $collection->toXml();  // What if you need JSON tomorrow?

// Good (slightly more verbose but flexible)
$xml = $xmlSerializer->serialize($collection);
$json = $jsonSerializer->serialize($collection);  // Easy to add!
```

### Concern 2: "Other libraries have `toArray()` in domain!"

**Response:** `toArray()` is NOT serialization!

- ✅ **`toArray()`** = Converts to primitive PHP data structure (still domain concern)
- ❌ **`toXml()`** = Converts to specific format for transport (infrastructure concern)

**Why the difference?**
- Array is a universal PHP data structure (no dependency)
- XML requires libraries (DOMDocument, XMLWriter) = external dependency
- Array is language-native, XML is protocol-specific

### Concern 3: "Won't serialization code get scattered?"

**Response:** Use dedicated serializer classes/services

**Organization:**
```
src/
├── Domain/
│   └── ValueObject/
│       ├── DescriptionCollection.php  ← Pure domain
│       └── Description.php             ← Pure domain
│
└── Infrastructure/
    └── Serialization/
        ├── Xml/
        │   ├── DescriptionCollectionXmlSerializer.php
        │   ├── IdentifyResponseBuilder.php
        │   └── OaiPmhXmlSerializer.php
        └── Json/  ← Future: JSON-LD support
            └── DescriptionCollectionJsonSerializer.php
```

**All serialization is in ONE place!** Not scattered.

---

## 8. Recommended Implementation Steps

### Step 1: Create Infrastructure Layer

```bash
mkdir -p src/Infrastructure/Serialization/Xml
```

### Step 2: Create Base Serializer Interface

```php
<?php
// src/Infrastructure/Serialization/SerializerInterface.php

namespace OaiPmh\Infrastructure\Serialization;

interface SerializerInterface
{
    public function serialize(mixed $data): string;
}
```

### Step 3: Create XML Serializer

```php
<?php
// src/Infrastructure/Serialization/Xml/OaiPmhXmlSerializer.php

namespace OaiPmh\Infrastructure\Serialization\Xml;

use OaiPmh\Infrastructure\Serialization\SerializerInterface;

class OaiPmhXmlSerializer implements SerializerInterface
{
    private DescriptionCollectionXmlSerializer $descriptionSerializer;

    public function __construct()
    {
        $this->descriptionSerializer = new DescriptionCollectionXmlSerializer();
    }

    public function serialize(mixed $data): string
    {
        // Implementation
    }
}
```

### Step 4: Use in Application Layer

```php
<?php
// src/Application/UseCase/IdentifyHandler.php

namespace OaiPmh\Application\UseCase;

use OaiPmh\Infrastructure\Serialization\Xml\OaiPmhXmlSerializer;

class IdentifyHandler
{
    public function __construct(
        private OaiPmhXmlSerializer $serializer
    ) {}

    public function handle(IdentifyRequest $request): IdentifyResponse
    {
        $repositoryIdentity = // ... get from repository
        
        $xml = $this->serializer->serialize($repositoryIdentity);
        
        return new IdentifyResponse($xml);
    }
}
```

---

## 9. Conclusion

### ✅ Current Architecture: CORRECT

**Keep XML serialization OUT of the domain layer.**

**Reasons:**
1. ✅ Follows Domain-Driven Design principles
2. ✅ Maintains Single Responsibility Principle
3. ✅ Keeps domain technology-agnostic
4. ✅ Enables multiple serialization formats
5. ✅ Improves testability
6. ✅ Follows industry best practices (Symfony, Doctrine, etc.)
7. ✅ Consistent with your existing codebase philosophy
8. ✅ Easier to maintain and evolve

### 📋 Action Items

- [x] **NO CHANGES NEEDED** to `DescriptionCollection`
- [x] **NO CHANGES NEEDED** to `Description`
- [ ] **FUTURE:** Create `src/Infrastructure/Serialization/` directory
- [ ] **FUTURE:** Implement `DescriptionCollectionXmlSerializer`
- [ ] **FUTURE:** Integrate with application/presentation layer

### 🎯 Final Recommendation

**DO NOT add `toXml()` or any XML-related methods to domain objects.**

Instead, create dedicated serializer classes in the infrastructure or presentation layer when you're ready to implement the actual OAI-PMH HTTP responses.

**Your domain layer is architecturally sound as-is!** ✅

---

## Appendix A: Code Examples

See section 3.1 for complete serializer implementation example.

## Appendix B: Further Reading

- **Domain-Driven Design** by Eric Evans (Chapter 4: Layered Architecture)
- **Clean Architecture** by Robert Martin (Chapter 22: The Clean Architecture)
- **Symfony Best Practices**: https://symfony.com/doc/current/best_practices.html
- **Hexagonal Architecture**: https://alistair.cockburn.us/hexagonal-architecture/

---

**Document Version:** 1.0  
**Author:** GitHub Copilot  
**Last Updated:** February 6, 2026
