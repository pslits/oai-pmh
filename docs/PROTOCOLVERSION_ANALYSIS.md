# ProtocolVersion Value Object Analysis

**Analysis Date:** February 7, 2026  
**Component:** ProtocolVersion Value Object  
**File:** `src/Domain/ValueObject/ProtocolVersion.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0](http://www.openarchives.org/OAI/openarchivesprotocol.html)

---

## 1. OAI-PMH Requirement

### Specification Context

From the OAI-PMH 2.0 specification:

> **protocolVersion** - the version of the OAI-PMH protocol supported by the repository.
> This version of the specification defines protocol version 2.0.

### Key Requirements

- ✅ Must be exactly `2.0` (current OAI-PMH version)
- ✅ Required in Identify response (exactly one, can be repeated for multiple versions)
- ✅ Fixed value for this implementation
- ✅ Future-proof for potential protocol updates

### XML Example

```xml
<Identify>
  <repositoryName>My Repository</repositoryName>
  <baseURL>http://www.example.org/oai</baseURL>
  <protocolVersion>2.0</protocolVersion>
  <adminEmail>admin@example.org</adminEmail>
</Identify>
```

---

## 2. User Story

**As a** repository administrator  
**When** I configure my OAI-PMH repository  
**Where** I declare protocol compliance  
**I want** to specify the OAI-PMH protocol version supported  
**Because** harvesters need to know which protocol version to use  

### Acceptance Criteria

- [x] ProtocolVersion must accept only `2.0`
- [x] Invalid versions must be rejected
- [x] Immutable value object
- [x] Value equality support
- [x] String representation

---

## 3. Implementation Details

### Class Structure

```php
final class ProtocolVersion
{
    private const ALLOWED_VERSION = '2.0';
    private string $version;
    
    public function __construct(string $version)
    public function getValue(): string
    public function equals(self $other): bool
    public function __toString(): string
    private function validateVersion(string $version): void
}
```

### Design Characteristics

| Aspect | Implementation | Status |
|--------|----------------|--------|
| Single allowed value | `2.0` only | ✅ |
| Immutability | Private property | ✅ |
| Validation | Exact match only | ✅ |
| Future-proof | Easy to extend for 3.0 | ✅ |

### Test Coverage

- **Total Tests:** 6
- **Coverage:** 100%
- **Status:** ✅ All passing

---

## 4. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\ProtocolVersion;

// ✅ Valid: OAI-PMH 2.0
$version = new ProtocolVersion('2.0');

// ❌ Invalid: Future version (throws exception)
$version = new ProtocolVersion('3.0'); // Exception!

// ❌ Invalid: Old version (throws exception)  
$version = new ProtocolVersion('1.0'); // Exception!
```

### Integration Example

```php
use OaiPmh\Domain\ValueObject\ProtocolVersion;

$identify = [
    'protocolVersion' => new ProtocolVersion('2.0'),
    // ... other fields
];

echo $identify['protocolVersion']->getValue(); // 2.0
```

---

## 5. Design Decisions

### Decision: Single Allowed Value vs. Enumeration

**Rationale:**
- Currently only 2.0 is valid
- Simple validation (exact match)
- Easy to extend when 3.0 is released
- Prevents accidental version mismatches

**Future Evolution:**
```php
// When OAI-PMH 3.0 is released:
private const ALLOWED_VERSIONS = ['2.0', '3.0'];

if (!in_array($version, self::ALLOWED_VERSIONS, true)) {
    throw new InvalidArgumentException(...);
}
```

---

## 6. References

- [OAI-PMH 2.0 Specification](http://www.openarchives.org/OAI/openarchivesprotocol.html)
- [docs/DELETEDRECORD_ANALYSIS.md](DELETEDRECORD_ANALYSIS.md) - Similar validation pattern
- [docs/GRANULARITY_ANALYSIS.md](GRANULARITY_ANALYSIS.md) - Enumeration pattern

---

*Analysis generated on February 7, 2026*  
*Branch: 10-define-repository-identity-value-object*
