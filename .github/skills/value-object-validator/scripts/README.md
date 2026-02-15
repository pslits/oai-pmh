# Value Object Validator Script

CI/CD-ready automated validation script for PHP value objects and abstract base classes.

## Features

- ✅ Detects class type automatically (abstract vs final)
- ✅ Applies appropriate validation checklist
- ✅ Generates console validation report with priority fixes
- ✅ Creates analysis document template in `docs/analysis/ValueObject/`
- ✅ Exit codes for CI/CD integration
- ✅ Supports both standard value objects and abstract base classes

## Usage

### Basic Validation

```bash
php validate_vo.php src/Domain/ValueObject/BaseURL.php
```

This will:
1. Classify the class type (final or abstract)
2. Run appropriate validation checks
3. Display console report with results
4. Generate analysis document at `docs/analysis/ValueObject/BASEURL_ANALYSIS.md`

### Skip Analysis Document

If you only want the console validation report:

```bash
php validate_vo.php src/Domain/ValueObject/BaseURL.php --no-analysis
```

### CI/CD Integration

The script returns appropriate exit codes:
- `0` = Validation passed or has minor warnings
- `1` = Validation failed, requires fixes

Example GitHub Actions workflow:

```yaml
- name: Validate Value Objects
  run: |
    php .github/skills/value-object-validator/scripts/validate_vo.php \
      src/Domain/ValueObject/BaseURL.php
```

Example in composer.json:

```json
{
  "scripts": {
    "validate:vo": "php .github/skills/value-object-validator/scripts/validate_vo.php"
  }
}
```

## Output

### Console Report

```
📋 Value Object Validation Report
==================================

File: src/Domain/ValueObject/BaseURL.php
Class: BaseURL
Class Type: Standard Value Object
Checklist: references/vo-checklist.md
Date: 2026-02-14 10:30:00

File Header (5/5 checks)
------------------------
  ✅ @author tag present
  ✅ @copyright tag present
  ✅ @license tag present
  ✅ @link tag correct
  ✅ @since tag present

[... more checks ...]

Summary
=======
✅ Passed: 45
❌ Failed: 2
⚠️  Warnings: 3
Score: 45/47 (96%)

Status: ✅ PASS - Good compliance

Priority Fixes:
===============

🟡 HIGH: Should fix soon
   - Add @throws documentation

Next Steps:
===========
1. Fix priority issues listed above
2. Run quality checks:
   vendor\bin\phpstan analyse src/Domain/ValueObject/BaseURL.php
   vendor\bin\phpcs src/Domain/ValueObject/BaseURL.php
   vendor\bin\phpunit tests/Domain/ValueObject/BaseURLTest.php
3. Generate analysis document: docs/analysis/ValueObject/BASEURL_ANALYSIS.md

Generating analysis document...
✅ Analysis document created: docs/analysis/ValueObject/BASEURL_ANALYSIS.md
```

### Analysis Document

Creates a template analysis document at `docs/analysis/ValueObject/{CLASSNAME}_ANALYSIS.md` with:

- Executive summary with validation results
- Placeholders for OAI-PMH requirements
- User story section
- Implementation details
- Validation results (pre-filled)
- Sections for manual completion (test coverage, design decisions, etc.)

**Note:** The generated analysis document is a **template** that requires manual review and completion for full documentation.

## Validation Criteria

### Standard Value Objects (final classes)

- File header with all required tags
- Class is `final` with `private` properties
- Domain-specific getter (REQUIRED)
- `equals()` and `__toString()` methods
- Descriptive parameter names
- Validation logic with exception handling
- Complete documentation

**Scoring:** 90%+ required for PASS

### Abstract Base Classes

- File header with all required tags  
- Class is `abstract`
- Protected properties where appropriate
- Template method pattern
- Inheritance-aware `equals()` method
- Null safety handling
- Breaking change documentation

**Scoring:** Qualitative assessment (EXCELLENT/GOOD/ACCEPTABLE/NEEDS IMPROVEMENT)

## Examples

### Validate Standard Value Object

```bash
$ php validate_vo.php src/Domain/ValueObject/Email.php

📋 Value Object Validation Report
Class Type: Standard Value Object
Status: ✅ PASS - Good compliance
Score: 48/50 (96%)
```

### Validate Abstract Base Class

```bash
$ php validate_vo.php src/Domain/ValueObject/ContainerFormat.php

📋 Value Object Validation Report
Class Type: Abstract Base Class
Assessment: ✅ GOOD - Most focus areas addressed, low risk
```

### CI/CD Pipeline

```bash
# Validate multiple value objects
for file in src/Domain/ValueObject/*.php; do
    php validate_vo.php "$file" --no-analysis || exit 1
done
```

## Limitations

- Analysis document is a template requiring manual completion
- Does not run PHPStan, PHPCS, or unit tests (suggests them in output)
- Pattern matching may have false positives/negatives
- Best used alongside comprehensive testing and manual review

## See Also

- [../SKILL.md](../SKILL.md) - Complete skill documentation
- [../references/vo-checklist.md](../references/vo-checklist.md) - Standard value object checklist (90+ points)
- [../references/abstract-base-class-checklist.md](../references/abstract-base-class-checklist.md) - Abstract base class checklist
- [../references/validation-examples.md](../references/validation-examples.md) - Code examples
