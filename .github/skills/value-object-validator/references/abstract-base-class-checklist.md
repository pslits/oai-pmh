# Abstract Base Class Validation Checklist

This checklist adapts the standard value object validation criteria for abstract base classes that serve as infrastructure for multiple concrete implementations.

## When to Use This Checklist

Use this checklist when validating classes that are:
- Marked with `abstract` keyword
- Located in `src/Domain/ValueObject/`
- Serve as base classes for multiple concrete implementations
- Provide shared behavior or template methods

**Examples:**
- `ContainerFormat.php` - Base class for Dublin Core and OAIPMH containers
- Future base classes for metadata format families

---

## 1. File Header (5 checks)

- [ ] Has `@author Paul Slits <paul.slits@gmail.com>`
- [ ] Has `@copyright (c) 2025 Paul Slits`
- [ ] Has `@license MIT License - https://opensource.org/licenses/MIT`
- [ ] Has `@link https://github.com/pslits/oai-pmh`
- [ ] Has `@since 0.1.0`

---

## 2. Class Modifiers (4 checks)

- [ ] Class is marked `abstract`
- [ ] Has comprehensive class-level docblock
- [ ] Docblock explains base class purpose and relationship to subclasses
- [ ] Docblock references OAI-PMH specification context

---

## 3. Property Design (6 checks)

- [ ] Properties use appropriate visibility (`protected` for subclass access, `private` for encapsulation)
- [ ] All properties have type declarations
- [ ] Properties are documented with `@var` tags
- [ ] Properties are immutable (no setters) unless base class requires mutability
- [ ] Null safety is explicitly handled
- [ ] Properties follow descriptive naming (not generic `$value`)

---

## 4. Method Design (8 checks)

- [ ] Has domain-specific getter for primary value
- [ ] `protected` methods are used for template method pattern where appropriate
- [ ] Abstract methods define required subclass behavior
- [ ] `equals()` method handles inheritance correctly
- [ ] `__toString()` provides meaningful representation
- [ ] All public methods documented with complete docblocks
- [ ] Protected methods documented when providing extension points
- [ ] Private methods documented if logic is complex

---

## 5. Template Method Pattern (4 checks)

- [ ] Template methods (`protected`) provide extension points for subclasses
- [ ] Clear documentation of which methods subclasses should override
- [ ] Base implementation provides sensible defaults or throws appropriate exceptions
- [ ] Template methods use descriptive parameter names

---

## 6. Validation Logic (8 checks)

- [ ] Has validation appropriate for base class role
- [ ] Validation is delegated to subclasses where appropriate
- [ ] Uses `InvalidArgumentException` for validation failures
- [ ] Error messages are descriptive and contextual
- [ ] Null values handled explicitly (validated or documented as acceptable)
- [ ] Validation parameters use descriptive names
- [ ] Complex validation split into focused methods
- [ ] Each validator has single responsibility

---

## 7. Null Safety (6 checks)

- [ ] Constructor parameters document null acceptance/rejection
- [ ] Null values validated if not acceptable
- [ ] Null returns documented with `@return Type|null`
- [ ] Protected methods document null handling contract for subclasses
- [ ] Getters handle null values safely
- [ ] Comparison methods (`equals()`) handle null safely

---

## 8. Breaking Change Considerations (8 checks)

- [ ] Method signature changes documented
- [ ] New abstract methods noted as breaking changes
- [ ] Visibility changes (private → protected → public) tracked
- [ ] Parameter type changes assessed for impact
- [ ] Return type changes assessed for impact
- [ ] Migration path provided for breaking changes
- [ ] Subclass impact analyzed and documented
- [ ] Version number implications noted

---

## 9. Documentation (10 checks)

- [ ] Class docblock explains base class purpose
- [ ] Class docblock lists known subclasses
- [ ] Class docblock explains template method pattern if used
- [ ] OAI-PMH specification section referenced
- [ ] Each abstract method documents subclass requirements
- [ ] Each protected method documents extension point purpose
- [ ] `@param` tags use descriptive names
- [ ] `@return` tags document all possible return types including null
- [ ] `@throws` tags document all exceptions
- [ ] Migration notes in docblock if breaking changes exist

---

## 10. Test Infrastructure (10 checks)

- [ ] Test class exists in correct location
- [ ] Tests cover base class behavior
- [ ] Tests verify abstract method contracts
- [ ] Tests check null handling
- [ ] Tests verify template method pattern
- [ ] Tests use BDD-style naming
- [ ] Tests have comprehensive assertions
- [ ] Tests verify inheritance behavior
- [ ] Tests check `equals()` with subclasses
- [ ] Test coverage appropriate for infrastructure role

---

## 11. Code Quality (8 checks)

- [ ] PHPStan Level 8 passes (0 errors)
- [ ] PSR-12 compliant (0 violations)
- [ ] No unused imports
- [ ] No TODO comments in production code
- [ ] Following project namespace conventions
- [ ] Type hints used everywhere
- [ ] No suppressed warnings without justification
- [ ] Code complexity is reasonable for base class role

---

## 12. Analysis Documentation (5 checks)

- [ ] Analysis document exists at `docs/analysis/ValueObject/{CLASSNAME}_ANALYSIS.md`
- [ ] Document explains abstract base class pattern
- [ ] Document lists all known subclasses
- [ ] Document explains breaking change considerations
- [ ] Document includes migration strategy for subclasses

---

## Scoring for Abstract Base Classes

**Unlike standard value objects, abstract base classes use qualitative assessment:**

### Primary Focus Areas:

1. **Infrastructure Stability** (40%)
   - Breaking change impact
   - Subclass contract clarity
   - Migration path quality

2. **Template Method Pattern** (30%)
   - Extension point design
   - Default behavior quality
   - Documentation clarity

3. **Null Safety & Type Safety** (20%)
   - Explicit null handling
   - Type declarations
   - Safe inheritance

4. **Code Quality & Tests** (10%)
   - PHPStan Level 8
   - PSR-12 compliance
   - Test infrastructure

### Assessment Levels:

- **EXCELLENT**: All focus areas well-addressed, minimal risk
- **GOOD**: Most focus areas addressed, low risk
- **ACCEPTABLE**: Core focus areas addressed, medium risk
- **NEEDS IMPROVEMENT**: Critical focus areas missing, high risk

---

## Key Differences from Standard Value Objects

### Allowed for Abstract Base Classes:

✅ `protected` properties (for subclass access)
✅ `protected` methods (template method pattern)
✅ Abstract methods (define subclass requirements)
✅ Nullable return types (if documented)
✅ Different scoring approach (qualitative vs. percentage)

### Still Required:

❌ Cannot be `final` (must be `abstract`)
❌ Must still follow DDD principles
❌ Must still have comprehensive tests
❌ Must still document breaking changes
❌ Must still pass PHPStan Level 8 and PSR-12

---

## Example: ContainerFormat Base Class

**Class Type:** Abstract Base Class  
**Purpose:** Base infrastructure for container format implementations  
**Subclasses:** `DublinCore`, `OAIPMH`

**Key Validation Points:**
1. ✅ Marked `abstract` not `final`
2. ✅ Uses `protected string|null $value` for subclass access
3. ✅ Has `protected getContainerFormat()` template method
4. ✅ Documents null safety explicitly
5. ✅ Breaking change analysis for subclass impact

**Assessment:** GOOD
- Infrastructure is stable
- Template methods well-designed
- Null handling explicit
- Minor: Could improve breaking change migration docs

---

**Last Updated:** 2026-02-14  
**Related Documents:**
- [vo-checklist.md](vo-checklist.md) - Standard value object checklist
- [LEARNINGS_ABSTRACT_BASE_CLASS.md](../LEARNINGS_ABSTRACT_BASE_CLASS.md) - Detailed analysis
