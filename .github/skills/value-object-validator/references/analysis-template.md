# Analysis Document Template

Complete template for generating comprehensive value object analysis documents.

## Document Location

**Path:** `docs/analysis/ValueObject/{VALUEOBJECT}_ANALYSIS.md`

**Examples:**
- `docs/analysis/ValueObject/BASEURL_ANALYSIS.md`
- `docs/analysis/ValueObject/REPOSITORYNAME_ANALYSIS.md`
- `docs/analysis/ValueObject/DELETEDRECORD_ANALYSIS.md`
- `docs/analysis/ValueObject/DESCRIPTIONCOLLECTION_ANALYSIS.md`

## When to Create Analysis Documents

- ✅ **REQUIRED**: After implementing each new value object
- ✅ **REQUIRED**: After significant refactoring (method signatures, validation logic)
- ✅ After adding new features or methods
- ⚠️ Optional for minor docblock improvements

## Complete Template

````markdown
# {ValueObjectName} Analysis

**Analysis Date:** {YYYY-MM-DD}  
**Component:** {ValueObjectName} Value Object  
**File Path:** `src/Domain/ValueObject/{FileName}.php`  
**OAI-PMH Version:** 2.0  
**Specification:** [OAI-PMH 2.0](https://www.openarchives.org/OAI/openarchivesprotocol.html)

---

## 1. OAI-PMH Requirement

### Specification Context

According to **OAI-PMH 2.0 specification section {X.X} ({SectionName})**, {brief description of what the spec says}.

**Quote from specification:**
> "{Relevant quote from OAI-PMH 2.0 specification}"

### Key Requirements

- **Format:** {Expected format or data type}
- **Required:** {Yes/No} - {When is it required}
- **Validation:** {What validation rules apply}
- **Allowed Values:** {List allowed values for enumerations, or "Any valid {type}" for open values}
- **Protocol Context:** {Where this appears in OAI-PMH responses}

### XML Example from Specification

```xml
<!-- Example from OAI-PMH 2.0 specification -->
<{tagName}>{exampleValue}</{tagName}>
```

### Common Patterns

| Pattern | Example | Usage |
|---------|---------|-------|
| {Pattern 1} | {Example 1} | {When to use} |
| {Pattern 2} | {Example 2} | {When to use} |

### OAI-PMH Compliance Notes

- {Note 1 about compliance}
- {Note 2 about compliance}
- {Note 3 about compliance}

---

## 2. User Story

**As a** {role/persona}  
**When** {triggering condition}  
**Where** {location in OAI-PMH flow}  
**I want** {goal/objective}  
**Because** {reason/business value}

### Acceptance Criteria

- [x] Encapsulates {domain concept} as immutable value object
- [x] Validates {validation rule 1}
- [x] Validates {validation rule 2}
- [x] Throws InvalidArgumentException for invalid input
- [x] Implements value equality via equals() method
- [x] Provides domain-specific getter get{Name}()
- [x] Provides string representation via __toString()
- [x] Complies with OAI-PMH 2.0 specification section {X.X}
- [x] Passes PHPStan Level 8 analysis
- [x] Passes PSR-12 code standards
- [x] Achieves {X}% test coverage

---

## 3. Implementation Details

### File Structure

```
src/Domain/ValueObject/{FileName}.php
tests/Domain/ValueObject/{FileName}Test.php
docs/analysis/ValueObject/{FILENAME}_ANALYSIS.md
```

### Class Structure

```php
final class {ClassName}
{
    private {type} $value;

    public function __construct({type} ${parameterName}) { }
    public function get{Name}(): {type} { }
    public function getValue(): {type} { }  // Alias for backward compatibility
    public function equals(self $other{Name}): bool { }
    public function __toString(): string { }
    private function validate({type} ${parameterName}): void { }
    private function validateNotEmpty({type} ${parameterName}): void { }
    private function validate{Rule}({type} ${parameterName}): void { }
}
```

### Design Characteristics

| Aspect | Implementation | OAI-PMH Alignment | Status |
|--------|----------------|-------------------|--------|
| **Immutability** | final class, private properties, no setters | Ensures data integrity | ✅ PASS |
| **Validation** | Constructor validation before assignment | Enforces spec compliance | ✅ PASS |
| **Value Equality** | equals() compares encapsulated values | Domain-driven comparison | ✅ PASS |
| **Domain-Specific API** | get{Name}() method | Self-documenting code | ✅ PASS |
| **Type Safety** | Strict typing throughout | PHP 8.0+ best practices | ✅ PASS |
| **OAI-PMH Compliance** | {Specific compliance point} | Section {X.X} adherence | ✅ PASS |

### Validation Logic

**Rules enforced:**

1. **{Rule 1 Name}**: {Description}
   ```php
   // Code example showing validation
   ```

2. **{Rule 2 Name}**: {Description}
   ```php
   // Code example showing validation
   ```

**Error messages:**
- `"{Error message 1}"` - When {condition}
- `"{Error message 2}"` - When {condition}

### Relationship to Other Components

```
OAI-PMH Repository
    └── RepositoryIdentity
            ├── {ThisValueObject}  ← Current
            ├── {RelatedValueObject1}
            └── {RelatedValueObject2}
```

**Used by:**
- `{Class1}` - {How it's used}
- `{Class2}` - {How it's used}

**Related value objects:**
- `{RelatedVO1}` - {Relationship}
- `{RelatedVO2}` - {Relationship}

---

## 4. Acceptance Criteria

### Functional Requirements

| Requirement | Test Coverage | Status |
|-------------|---------------|--------|
| Accept valid {values} | `test{Method}_ValidValue_ReturnsInstance()` | ✅ PASS |
| Reject empty values | `test{Method}_EmptyValue_ThrowsException()` | ✅ PASS |
| Reject {invalid type} | `test{Method}_InvalidValue_ThrowsException()` | ✅ PASS |
| Implement value equality | `testEquals_SameValue_ReturnsTrue()`, `testEquals_DifferentValue_ReturnsFalse()` | ✅ PASS |
| Provide domain getter | `testGet{Name}_ReturnsValue()` | ✅ PASS |
| Provide string representation | `testToString_ReturnsFormattedString()` | ✅ PASS |

### OAI-PMH Protocol Compliance

| Specification Requirement | Implementation | Status |
|---------------------------|----------------|--------|
| {Spec requirement 1} | {How implemented} | ✅ PASS |
| {Spec requirement 2} | {How implemented} | ✅ PASS |
| {Spec requirement 3} | {How implemented} | ✅ PASS |

### Non-Functional Requirements

| Requirement | Implementation | Status |
|-------------|----------------|--------|
| Immutability | final class, private properties, no setters | ✅ PASS |
| Thread-safety | Immutable state | ✅ PASS |
| Type safety | Strict types throughout | ✅ PASS |
| PHPStan Level 8 | 0 errors | ✅ PASS |
| PSR-12 compliance | 0 violations | ✅ PASS |
| Test coverage | {X}% line coverage | ✅ PASS |

---

## 5. Test Coverage Analysis

### Statistics

| Metric | Value | Status |
|--------|-------|--------|
| **Total Tests** | {X} | ✅ |
| **Assertions** | {Y} | ✅ |
| **Line Coverage** | {Z}% | ✅ |
| **Method Coverage** | 100% | ✅ |
| **CRAP Index** | Low | ✅ |

### Test Categories

**Constructor Validation ({X} tests):**
- `testConstructor_ValidValue_CreatesInstance()`
- `testConstructor_EmptyValue_ThrowsException()`
- `testConstructor_InvalidValue_ThrowsException()`

**Value Access ({X} tests):**
- `testGet{Name}_ReturnsValue()`
- `testGetValue_ReturnsValue()`

**Value Equality ({X} tests):**
- `testEquals_SameValue_ReturnsTrue()`
- `testEquals_DifferentValue_ReturnsFalse()`
- `testEquals_SameInstance_ReturnsTrue()`

**String Representation ({X} tests):**
- `testToString_ReturnsFormattedString()`
- `testToString_ContainsValue()`

**Edge Cases ({X} tests):**
- {Edge case test 1}
- {Edge case test 2}

### Test Quality

**Strengths:**
- ✅ BDD-style Given-When-Then structure
- ✅ User story references in comments
- ✅ Descriptive test names
- ✅ Comprehensive assertions
- ✅ Data providers for multiple scenarios
- ✅ Tests all validation paths

**Coverage Gaps:**
- {Any identified gaps, or "None identified" if complete}

### Test Example

```php
/**
 * User Story: Repository administrator configuring OAI-PMH endpoint
 * Given: A valid {value}
 * When: Creating a {ClassName} instance
 * Then: Instance is created successfully
 */
public function testConstructor_ValidValue_CreatesInstance(): void
{
    // Given
    $value = '{valid example}';
    
    // When
    ${instance} = new {ClassName}($value);
    
    // Then
    $this->assertInstanceOf({ClassName}::class, ${instance});
    $this->assertSame($value, ${instance}->get{Name}());
}
```

---

## 6. Code Examples

### Basic Usage

```php
use OaiPmh\Domain\ValueObject\{ClassName};

// Create instance
${instance} = new {ClassName}('{valid value}');

// Access value using domain-specific getter
echo ${instance}->get{Name}();  // Output: {valid value}

// String representation
echo ${instance};  // Output: {formatted output}
```

### Validation Examples

**Valid values:**
```php
// {Example 1 description}
${instance1} = new {ClassName}('{valid example 1}');

// {Example 2 description}
${instance2} = new {ClassName}('{valid example 2}');
```

**Invalid values (throw exceptions):**
```php
// Empty value
try {
    ${instance} = new {ClassName}('');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();  // "{Error message}"
}

// Invalid format
try {
    ${instance} = new {ClassName}('{invalid example}');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();  // "{Error message}"
}
```

### Integration with Other Value Objects

```php
use OaiPmh\Domain\ValueObject\{ClassName};
use OaiPmh\Domain\ValueObject\{RelatedClass};

// Combined usage
${instance1} = new {ClassName}('{value 1}');
${instance2} = new {RelatedClass}('{value 2}');

// {Usage scenario description}
```

### Real-World OAI-PMH Scenario

```php
// {Describe realistic OAI-PMH usage scenario}
${instance} = new {ClassName}('{realistic value}');

// {Show how it fits into larger OAI-PMH context}
```

---

## 7. Design Decisions

### Decision 1: {Decision Title}

**Context:**  
{What situation led to this design decision}

**Options Considered:**
1. {Option 1}: {Description}
2. {Option 2}: {Description}
3. {Option 3}: {Description}

**Decision:**  
Chose {selected option} because {rationale}.

**Rationale:**
- {Reason 1}
- {Reason 2}
- {Reason 3}

**Trade-offs:**
- ✅ Benefit: {Benefit 1}
- ✅ Benefit: {Benefit 2}
- ⚠️ Trade-off: {Trade-off 1}

**Implementation:**
```php
// Code example showing the decision
```

**OAI-PMH Alignment:**  
{How this aligns with OAI-PMH specification}

---

### Decision 2: Domain-Specific Getter Method

**Context:**  
Value objects need a consistent way to access encapsulated values while being self-documenting.

**Options Considered:**
1. Only `getValue()` for consistency across all VOs
2. Only domain-specific getter (e.g., `get{Name}()`)
3. Both domain-specific getter AND `getValue()` alias

**Decision:**  
Provide both `get{Name}()` as primary getter and `getValue()` as backward-compatible alias.

**Rationale:**
- Domain-specific name improves code readability
- Self-documenting API makes code more maintainable
- Backward-compatible alias ensures consistency with other VOs

**Trade-offs:**
- ✅ Benefit: More expressive, domain-driven API
- ✅ Benefit: Backward compatible with existing code patterns
- ✅ Benefit: Self-documenting code reduces need for comments
- ⚠️ Trade-off: Two method names for same functionality

**Implementation:**
```php
public function get{Name}(): {type}
{
    return $this->value;
}

public function getValue(): {type}  // Alias for backward compatibility
{
    return $this->value;
}
```

---

### Decision 3: {Another Decision Title}

{Follow same structure as above}

---

## 8. Known Issues & Future Enhancements

### Current Known Issues

{List any known issues, or "None identified" if clean}

### Future Enhancements

**Priority: High**
- [ ] {Enhancement 1} (Issue #{X})
  - Description: {What needs to be done}
  - Rationale: {Why this is important}

**Priority: Medium**
- [ ] {Enhancement 2} (Issue #{Y})
  - Description: {What needs to be done}
  - Rationale: {Why this is important}

**Priority: Low**
- [ ] {Enhancement 3} (Issue #{Z})
  - Description: {What needs to be done}
  - Rationale: {Why this is important}

### Migration Notes

**PHP 8.2+ Migration:**
- TODO #{X}: Convert to readonly properties when minimum PHP version is 8.2
- TODO #{Y}: {Other migration task}

---

## 9. Comparison with Related Value Objects

### Pattern Consistency

| Aspect | {ThisVO} | {RelatedVO1} | {RelatedVO2} | Consistent? |
|--------|----------|--------------|--------------|-------------|
| **Immutability** | ✅ final class | ✅ final class | ✅ final class | ✅ Yes |
| **Domain Getter** | get{Name}() | get{OtherName}() | get{ThirdName}() | ✅ Yes |
| **Validation** | Constructor | Constructor | Constructor | ✅ Yes |
| **Value Equality** | equals() | equals() | equals() | ✅ Yes |
| **String Repr** | __toString() | __toString() | __toString() | ✅ Yes |

### Similarities

- {Similarity 1 with other VOs}
- {Similarity 2 with other VOs}
- {Similarity 3 with other VOs}

### Differences

- **{ThisVO}**: {What makes it unique}
- **{RelatedVO1}**: {What makes it different}
- **{RelatedVO2}**: {What makes it different}

### Why Not Reuse Existing Value Objects?

{Explain why this needed to be a separate VO rather than reusing an existing one}

---

## 10. Recommendations

### For Developers Using This Value Object

**DO:**
- ✅ Use domain-specific getter `get{Name}()` for clarity
- ✅ Let the value object validate data (don't pre-validate)
- ✅ Use value equality with `equals()` for comparisons
- ✅ Catch `InvalidArgumentException` when user input involved

**DON'T:**
- ❌ Don't try to modify the value after creation (immutable)
- ❌ Don't use `===` comparison (use `equals()` instead)
- ❌ Don't bypass validation by direct property access
- ❌ Don't create wrapper classes (value object is sufficient)

### For Repository Administrators

- {Recommendation 1 for admins}
- {Recommendation 2 for admins}
- {Recommendation 3 for admins}

### For Library Maintainers

- {Recommendation 1 for maintainers}
- {Recommendation 2 for maintainers}
- {Recommendation 3 for maintainers}

---

## 11. References

### OAI-PMH Specification

- [OAI-PMH 2.0 Protocol](https://www.openarchives.org/OAI/openarchivesprotocol.html)
- [Section {X.X}: {Section Name}](https://www.openarchives.org/OAI/openarchivesprotocol.html#{anchor})
- {Other relevant spec sections}

### Related Standards

- {RFC or other standard 1}
- {RFC or other standard 2}

### Related Analysis Documents

- [{RelatedVO1} Analysis](RELATEDVO1_ANALYSIS.md)
- [{RelatedVO2} Analysis](RELATEDVO2_ANALYSIS.md)

### GitHub Issues

- Issue #{X}: {Issue title}
- Issue #{Y}: {Issue title}

### Project Documentation

- `.github/copilot-instructions.md` - Coding standards
- `docs/VALUE_OBJECTS_INDEX.md` - All value objects
- `docs/ARCHITECTURE_UPDATE_{DATE}.md` - Architecture decisions

---

## 12. Appendix

### A. Complete Test Output

```
PHPUnit {version} by Sebastian Bergmann and contributors.

...........                                                       {X} / {X} (100%)

Time: {X} ms, Memory: {Y} MB

OK ({X} tests, {Y} assertions)
```

### B. Code Coverage Report

```
Code Coverage Report:
{Date and time}

Summary:
  Classes:  100.00% ({X}/{X})
  Methods:  100.00% ({Y}/{Y})
  Lines:    100.00% ({Z}/{Z})

OaiPmh\Domain\ValueObject\{ClassName}
  Methods: 100.00% ({X}/{X})   Lines: 100.00% ({Y}/{Y})
```

### C. PHPStan Analysis Results

```
[OK] No errors

PHPStan Level: 8 (Maximum)
Analyzed files: 1
```

### D. PHP CodeSniffer Results

```
FILE: src/Domain/ValueObject/{FileName}.php
----------------------------------------------------------------------
FOUND 0 ERRORS AND 0 WARNINGS AFFECTING 0 LINES
----------------------------------------------------------------------
```

### E. Real-World Example (if applicable)

{Include a complete real-world example if relevant}

---

*Analysis generated on {YYYY-MM-DD}*
````

## Sections Explained

### Section 1: OAI-PMH Requirement
**Purpose:** Document what the OAI-PMH spec requires.  
**Include:** Direct quotes, XML examples, allowed values, protocol context.

### Section 2: User Story
**Purpose:** Explain why this value object exists.  
**Include:** User story template, complete acceptance criteria with checkboxes.

### Section 3: Implementation Details
**Purpose:** Show how it's implemented.  
**Include:** Class structure, design characteristics table, validation logic, relationships.

### Section 4: Acceptance Criteria
**Purpose:** Define what "done" means.  
**Include:** Functional requirements, OAI-PMH compliance, non-functional requirements tables.

### Section 5: Test Coverage Analysis
**Purpose:** Document test quality and coverage.  
**Include:** Statistics, test categories, quality assessment, example tests.

### Section 6: Code Examples
**Purpose:** Show how to use the value object.  
**Include:** Basic usage, validation examples, integration, real-world scenarios.

### Section 7: Design Decisions
**Purpose:** Explain "why" not just "what".  
**Include:** Context, options considered, rationale, trade-offs, implementation.

### Section 8: Known Issues & Future Enhancements
**Purpose:** Track planned improvements.  
**Include:** Known issues, future enhancements with priorities, migration notes.

### Section 9: Comparison with Related Value Objects
**Purpose:** Show consistency across library.  
**Include:** Pattern consistency table, similarities, differences, rationale for new VO.

### Section 10: Recommendations
**Purpose:** Guide users of the value object.  
**Include:** DO/DON'T lists for developers, admins, maintainers.

### Section 11: References
**Purpose:** Link to related documentation.  
**Include:** OAI-PMH spec, standards, related docs, GitHub issues.

### Section 12: Appendix
**Purpose:** Include actual tool output.  
**Include:** Test output, coverage report, PHPStan results, PHPCS results.

## Example Analyses

For complete real-world examples, see:

- [docs/analysis/ValueObject/BASEURL_ANALYSIS.md](docs/analysis/ValueObject/BASEURL_ANALYSIS.md)
- [docs/analysis/ValueObject/REPOSITORYNAME_ANALYSIS.md](docs/analysis/ValueObject/REPOSITORYNAME_ANALYSIS.md)
- [docs/analysis/ValueObject/DESCRIPTIONCOLLECTION_ANALYSIS.md](docs/analysis/ValueObject/DESCRIPTIONCOLLECTION_ANALYSIS.md)

## Tips for Writing Analysis Documents

1. **Be Comprehensive:** Include all sections, even if some are brief
2. **Be Specific:** Use actual code examples, not pseudocode
3. **Be Accurate:** Include real tool output in appendix
4. **Be Current:** Update when significant changes occur
5. **Cross-Reference:** Link to related docs and specs
6. **Think Future:** Document decisions for future maintainers
