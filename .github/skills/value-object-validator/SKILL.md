---
name: value-object-validator
description: Validates PHP value objects and abstract base classes against OAI-PMH project standards including DDD patterns, immutability, domain-specific getters, PSR-12, PHPStan Level 8, and OAI-PMH 2.0 spec compliance. Generates comprehensive analysis documents at docs/analysis/ValueObject/{CLASSNAME}_ANALYSIS.md. Use when reviewing value objects in src/Domain/ValueObject/, checking implementations (both final value objects and abstract base classes), validating refactoring, ensuring test coverage, or creating analysis documentation.
license: Complete terms in LICENSE.txt
---

# Value Object Validator

Validates PHP value objects and abstract base classes against OAI-PMH project standards and generates comprehensive analysis documentation.

## Quick Start

**Validate and generate analysis:**
```
Validate src/Domain/ValueObject/{CLASS}.php
```

This will:
1. Classify class type (final vs abstract)
2. Run appropriate validation checklist
3. Generate validation report with priority fixes
4. **Create analysis document** at `docs/analysis/ValueObject/{CLASS}_ANALYSIS.md`

## Workflow

**Step 1: Classify**
- `abstract` class → [Abstract Base Class checklist](references/abstract-base-class-checklist.md)
- `final` class → [Standard Value Object checklist](references/vo-checklist.md)
- Neither → WARN and suggest modifier

**Step 2: Validate**
- Execute all checks from selected checklist
- Generate structured report with priority-based findings

**Step 3: Output**
- **Console Report:** Validation results with ✅ PASS / ⚠️ WARN / ❌ FAIL
- **Analysis Document:** Complete analysis at `docs/analysis/ValueObject/{CLASS}_ANALYSIS.md` (see Output Format below)

## Validation Focus

### Standard Value Objects (final classes)
**Critical requirements:**
- `final` class with `private` properties
- Domain-specific getter (e.g., `getBaseUrl()`, not just `getValue()`)
- `equals(self $other): bool` with descriptive parameter names
- `__toString(): string`
- Validation split into focused methods

**Scoring:** 90%+ required for PASS

**Complete checklist:** [references/vo-checklist.md](references/vo-checklist.md)

### Abstract Base Classes
**Adapted requirements:**
- `abstract` class with `protected` properties (where needed)
- Template method pattern with `protected` extension points
- Breaking change impact analysis
- Null safety documentation

**Scoring:** Qualitative assessment (Infrastructure Stability, Subclass Contract, Null Safety, Code Quality)

**Complete checklist:** [references/abstract-base-class-checklist.md](references/abstract-base-class-checklist.md)

## Output Format

### 1. Console Validation Report

```
📋 Value Object Validation Report
==================================

File: src/Domain/ValueObject/{CLASS}.php
Class Type: [Standard Value Object | Abstract Base Class]
Checklist: [references/vo-checklist.md | references/abstract-base-class-checklist.md]

✅/⚠️/❌ [Category Name] (X/Y checks)
  ✅ Check description
  ❌ Check description

Priority Fixes:
🔴 CRITICAL: Must fix before merging
🟡 HIGH: Should fix soon
🟢 LOW: Nice to have

Recommendations:
- [List of improvements]

Next Steps:
1. [Action items]
```

**Detailed format:** [references/output-format.md](references/output-format.md)

### 2. Analysis Document (REQUIRED OUTPUT)

**File:** `docs/analysis/ValueObject/{CLASS}_ANALYSIS.md`

**Required sections (13 total):**
1. Document Header (date, component, file path, OAI-PMH version)
2. OAI-PMH Requirement (spec context, XML examples)
3. User Story (acceptance criteria with checkboxes)
4. Implementation Details (class structure, design characteristics table)
5. Acceptance Criteria (functional/OAI-PMH/non-functional tables)
6. Test Coverage Analysis (statistics, categories, quality)
7. Code Examples (basic, validation, integration)
8. Design Decisions (context, rationale, trade-offs for each decision)
9. Known Issues & Future Enhancements
10. Comparison with Related Value Objects
11. Recommendations (for developers, admins, maintainers)
12. References (OAI-PMH spec, related docs, issues)
13. Appendix (test output, coverage, PHPStan, PHPCS results)

**Complete template:** [references/analysis-template.md](references/analysis-template.md)

**Note:** Analysis documents are generated in the main project at `docs/analysis/ValueObject/{CLASS}_ANALYSIS.md` following this template when validation passes.

## Validation Examples

**Complete examples:** [references/validation-examples.md](references/validation-examples.md)

## Quality Commands

```bash
# PHPStan Level 8
vendor\bin\phpstan analyse src/Domain/ValueObject/[FileName].php

# PSR-12 Compliance
vendor\bin\phpcs src/Domain/ValueObject/[FileName].php

# Auto-fix
vendor\bin\phpcbf src/Domain/ValueObject/[FileName].php

# Tests with coverage
vendor\bin\phpunit --coverage-html coverage/html tests/Domain/ValueObject/[FileName]Test.php
```

## Resources

### scripts/
- `validate_vo.php` - **CI/CD-ready automated validation script**

### references/
- `vo-checklist.md` - 90+ point checklist for standard value objects
- `abstract-base-class-checklist.md` - Adapted checklist for abstract base classes
- `oai-pmh-requirements.md` - OAI-PMH compliance guide
- `validation-examples.md` - Complete validation examples
- `analysis-template.md` - **Analysis document template (REQUIRED OUTPUT)**
- `output-format.md` - Validation report format

### assets/
- `value-object-template.php` - Standard value object template
- `abstract-base-class-template.php` - Base class template

**Related Documentation:**
- Project standards: `.github/copilot-instructions.md`
- Value objects index: `docs/VALUE_OBJECTS_INDEX.md`
