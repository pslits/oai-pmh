# GitHub Copilot Instructions for OAI-PMH Project

## Project Overview
This is a PHP library implementing the OAI-PMH (Open Archives Initiative Protocol for Metadata Harvesting) protocol. The project follows Domain-Driven Design principles with a focus on immutable value objects.

## General Coding Standards

### PHP Version
- Use PHP 8.0+ features
- Leverage typed properties, constructor property promotion where appropriate
- Use strict types: `declare(strict_types=1);` (when needed)

### Code Style
- Follow **PSR-12** coding standards strictly
- Use 4 spaces for indentation (no tabs)
- Keep lines under 120 characters when possible
- Use camelCase for methods and variables
- Use PascalCase for class names

### Namespacing
- Root namespace: `OaiPmh\`
- Domain layer: `OaiPmh\Domain\`
- Value objects: `OaiPmh\Domain\ValueObject\`
- Schema objects: `OaiPmh\Domain\Schema\`
- Tests mirror the src structure: `OaiPmh\Tests\`

## File Structure and Documentation

### File Headers
Every PHP file must include this header block:
```php
<?php

/**
 * @author    Paul Slits <paul.slits@gmail.com>
 * @copyright (c) 2025 Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     0.1.0
 */
```

### Class Documentation
- Every class must have a comprehensive docblock
- Explain what the class represents and its purpose
- For value objects, mention: encapsulation, immutability, value equality
- Document any validation rules or constraints

### Method Documentation
- All public methods require docblocks with clear descriptions
- Document `@param` with type and description
- Document `@return` with type and description
- Document `@throws` for any exceptions that may be thrown
- Private methods should have docblocks if the logic is complex

## Domain-Driven Design Patterns

### Value Objects
- **Always** make value objects `final`
- **Always** make value objects immutable (no setters)
- Include validation in the constructor
- Throw `InvalidArgumentException` for validation failures
- Implement these methods:
  - `getValue()` or similar getter to retrieve the value
  - `equals(self $other): bool` for value comparison
  - `__toString(): string` for string representation
- Use descriptive error messages in exceptions

### Example Value Object Pattern:
```php
final class ExampleValue
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validateValue($value);
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return sprintf('ExampleValue(value: %s)', $this->value);
    }

    private function validateValue(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Value cannot be empty');
        }
    }
}
```

### Collection Objects
- Collections should be type-safe and contain only one type of object
- Implement `\Countable` and `\IteratorAggregate` interfaces when appropriate
- Validate items when adding to the collection
- Make collections immutable when possible

## Testing Requirements

### Test Structure
- Use PHPUnit 9.6+
- Tests must be in `tests/` directory mirroring `src/` structure
- Test class names should match source class names with `Test` suffix
- Use descriptive test method names following pattern: `testMethodName_Condition_ExpectedBehavior()`

### Test Coverage
- Aim for high test coverage (tracked with PHPUnit coverage)
- Test happy paths and edge cases
- Test all validation rules and exceptions
- Use data providers for testing multiple scenarios

### Test Documentation
- Add docblocks to test classes explaining what is being tested
- Complex test methods should have comments explaining the scenario

## Quality Assurance

### Static Analysis
- Code must pass **PHPStan Level 8** without errors
- Run PHPStan before committing: `vendor/bin/phpstan analyse`

### Code Standards
- Code must pass PHP_CodeSniffer checks
- Run PHPCS: `vendor/bin/phpcs`
- Fix issues automatically when possible: `vendor/bin/phpcbf`

## Naming Conventions

### Classes
- Value Objects: Descriptive nouns (e.g., `Email`, `ProtocolVersion`, `UTCdatetime`)
- Collections: Plural or with `Collection` suffix (e.g., `EmailCollection`, `MetadataNamespaceCollection`)
- Interfaces: Descriptive with `Interface` suffix (e.g., `MetadataFormatInterface`)

### Methods
- Getters: `getValue()`, `getEmail()`, `toArray()`, etc.
- Boolean methods: Use `is`, `has`, `can` prefixes (e.g., `isEmpty()`, `hasErrors()`)
- Factory methods: Use `create`, `from` prefixes (e.g., `createFromString()`, `fromArray()`)

### Variables
- Use descriptive names that clearly indicate purpose
- Avoid single-letter variables except in loops or closures
- Boolean variables should read like questions (e.g., `$isValid`, `$hasValue`)

## Error Handling

### Exceptions
- Use `InvalidArgumentException` for invalid input/validation errors
- Use specific exception types when appropriate
- Always provide descriptive error messages with context
- Format: `sprintf('Error description: %s', $context)`

### Validation
- Validate all inputs in constructors
- Fail fast: validate before assigning to properties
- Provide specific feedback about what is invalid

## OAI-PMH Specific Guidelines

### XML Handling
- Follow XML serialization patterns established in the project
- Respect OAI-PMH protocol specifications
- Use proper namespace handling

### Protocol Compliance
- Ensure all implementations comply with OAI-PMH 2.0 specification
- Validate metadata formats according to standards
- Handle protocol-specific data types correctly (e.g., UTC datetime, granularity)

## Value Object Analysis Documentation

### Purpose
**Each individual value object** must have its own comprehensive analysis document that provides detailed documentation about its design, implementation, testing, and OAI-PMH compliance.

### When to Create Analysis Documents
- **REQUIRED**: After implementing each new value object (one document per value object)
- When completing a feature branch that adds domain objects
- For any value object or collection class
- When documenting design decisions for future reference

### Analysis Document Structure

Create **separate** analysis documents in the `docs/` directory with naming pattern: `docs/{VALUEOBJECT}_ANALYSIS.md`

**Examples:**
- `docs/BASEURL_ANALYSIS.md` - Individual BaseURL value object analysis
- `docs/REPOSITORYNAME_ANALYSIS.md` - Individual RepositoryName value object analysis  
- `docs/DESCRIPTIONCOLLECTION_ANALYSIS.md` - Individual DescriptionCollection analysis
- `docs/EMAIL_ANALYSIS.md` - Individual Email value object analysis
- etc.

**Note:** Each value object gets its own dedicated analysis file following the detailed template below.

### Required Sections

Follow the structure from `docs/DESCRIPTIONCOLLECTION_ANALYSIS.md`, `docs/BASEURL_ANALYSIS.md`, or `docs/REPOSITORYNAME_ANALYSIS.md`:

1. **Document Header**
   - Analysis date
   - Component name (e.g., "BaseURL Value Object")
   - File path (e.g., `src/Domain/ValueObject/BaseURL.php`)
   - OAI-PMH Version: 2.0
   - Specification reference link

2. **OAI-PMH Requirement** (Section 1)
   - Specification context (quote from OAI-PMH spec)
   - Key requirements (bullet list)
   - XML example from specification
   - Common patterns or usage table
   - OAI-PMH compliance notes

3. **User Story** (Section 2)
   - Story template (As a.../When.../Where.../I want.../Because...)
   - Complete context for why this value object exists
   - Acceptance criteria with checkboxes [x]
   - All must be checked/satisfied

4. **Implementation Details** (Section 3)
   - File structure
   - Class structure (code block showing methods)
   - Design characteristics table (Aspect | Implementation | OAI-PMH Alignment | Status)
   - Validation logic with code examples
   - Relationship to other components (ASCII diagram)

5. **Acceptance Criteria** (Section 4)
   - Functional requirements table with test coverage mapping
   - OAI-PMH protocol compliance table
   - Non-functional requirements table
   - Each row with ✅ PASS or ⚠️ status

6. **Test Coverage Analysis** (Section 5)
   - Test statistics table (total tests, assertions, coverage percentages)
   - Test categories with test names
   - Test quality assessment (strengths and any coverage gaps)
   - Specific test examples showing BDD style

7. **Code Examples** (Section 6)
   - Basic usage with code blocks
   - Validation examples (valid and invalid cases)
   - Integration with other value objects
   - Real-world usage scenarios

8. **Design Decisions** (Section 7)
   - Each major decision as subsection (Decision 1, 2, 3...)
   - Context/Options Considered/Rationale/Trade-offs for each
   - Code examples showing the decision
   - Justification from OAI-PMH spec where applicable

9. **Known Issues & Future Enhancements** (Section 8)
   - Current known issues (if any)
   - Future enhancements with priority levels
   - Migration notes (e.g., PHP 8.2 readonly)
   - Related GitHub issue numbers

10. **Comparison with Related Value Objects** (Section 9)
    - Pattern consistency table
    - Comparison with similar VOs in the library
    - Why this VO vs. reusing existing ones

11. **Recommendations** (Section 10)
    - For developers using the VO
    - For repository administrators
    - For library maintainers
    - DO/DON'T lists with examples

12. **References** (Section 11)
    - OAI-PMH specification links
    - Related standards (RFCs, etc.)
    - Related analysis documents
    - Related GitHub issues

13. **Appendix** (Section 12)
    - Complete test output
    - Code coverage report
    - PHPStan analysis results
    - PHP CodeSniffer results
    - Real-world examples (if applicable)

### Example Analysis Template

```markdown
# {Component Name} Analysis

**Analysis Date:** {Date}  
**Component:** {Name}  
**Branch:** {branch-name}  
**Status:** {Completed/In Progress/etc.}

---

## Executive Summary

Brief 2-3 sentence overview of what this component does and its importance.

---

## Value Object Overview

### Purpose
What problem does this solve in the domain?

### OAI-PMH Context
Which part of the OAI-PMH specification does this implement?

### Key Characteristics
- Characteristic 1
- Characteristic 2

---

## Implementation

### File Structure
```
src/Domain/ValueObject/{Name}.php
tests/Domain/ValueObject/{Name}Test.php
```

### Class Design
- **Namespace:** OaiPmh\Domain\ValueObject
- **Type:** final class
- **Implements:** Interface1, Interface2

### Properties
| Property | Type | Visibility | Description |
|----------|------|------------|-------------|
| $property1 | Type | private | Purpose |

### Methods
| Method | Parameters | Return | Purpose |
|--------|------------|--------|---------|
| __construct() | ... | void | Initialize |
| getValue() | none | Type | Get value |
| equals() | self $other | bool | Value equality |
| __toString() | none | string | String repr |

### Validation Rules
1. Rule 1: Description
2. Rule 2: Description

---

## Design Decisions

### Decision 1: {Title}
**Why:** Explanation
**Alternatives:** What else was considered
**Trade-offs:** Benefits vs. drawbacks

---

## Code Examples

### Basic Usage
```php
// Example code
```

### Advanced Usage
```php
// Example code
```

---

## Test Coverage

### Statistics
- **Total Tests:** X
- **Assertions:** Y
- **Coverage:** Z%
- **Status:** All passing

### Test Categories
- ✅ Constructor validation (X tests)
- ✅ Value equality (Y tests)
- ✅ Immutability (Z tests)
- ✅ String representation (W tests)
- ✅ Edge cases (V tests)

### Test Quality
- BDD-style Given-When-Then ✅
- User story comments ✅
- Descriptive test names ✅
- Comprehensive assertions ✅

---

## Quality Metrics

| Metric | Result | Status |
|--------|--------|--------|
| PHPStan Level 8 | 0 errors | ✅ |
| PSR-12 Compliance | 100% | ✅ |
| Code Coverage | X% | ✅/⚠️ |
| CRAP Index | Low | ✅ |

---

## Usage Guidelines

### When to Use
- Scenario 1
- Scenario 2

### Best Practices
1. Practice 1
2. Practice 2

### Common Pitfalls
- ❌ Don't do X
- ✅ Instead do Y

---

## Future Enhancements

### Planned
- [ ] Enhancement 1 (Issue #X)
- [ ] Enhancement 2 (Issue #Y)

### Known Issues
- Issue 1 (Issue #X): Description
- Issue 2 (Issue #Y): Description

### Migration Notes
- PHP 8.2: TODO #8 - Convert to readonly properties

---

## References

- [OAI-PMH Specification](URL)
- Related analysis documents
- GitHub issues
- External resources

---

*Analysis generated on {Date}*
```

### Best Practices for Analysis Documents

1. **Keep It Current**
   - Update after significant changes
   - Version control with the code
   - Reference specific code line numbers sparingly (they change)

2. **Be Comprehensive but Concise**
   - Include all important information
   - Use tables and lists for clarity
   - Link to external resources rather than duplicating

3. **Focus on "Why" Not "What"**
   - Code shows "what"
   - Analysis explains "why"
   - Document design decisions and trade-offs

4. **Include Practical Examples**
   - Real-world usage scenarios
   - Integration patterns
   - Common combinations

5. **Maintain Quality Metrics**
   - Track coverage trends
   - Document quality improvements
   - Highlight areas needing attention

6. **Cross-Reference**
   - Link to related value objects
   - Reference OAI-PMH spec sections
   - Connect to GitHub issues

## Additional Best Practices

- **Immutability**: Prefer immutable objects; avoid mutable state
- **Type Safety**: Always use type hints for parameters and return types
- **Single Responsibility**: Each class should have one clear purpose
- **Composition over Inheritance**: Favor composition and interfaces
- **Fail Fast**: Validate early and throw meaningful exceptions
- **No Magic**: Avoid magic methods unless necessary; be explicit
- **DRY Principle**: Don't repeat yourself; extract common patterns
- **KISS Principle**: Keep it simple and straightforward

## Commands Reference

```bash
# Run tests
vendor/bin/phpunit

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/html

# Run static analysis
vendor/bin/phpstan analyse

# Check code standards
vendor/bin/phpcs

# Fix code standards
vendor/bin/phpcbf
```

## Commit message guidelines

Keep commit messages clear, consistent and machine-friendly so changelogs and code review history are useful.

Recommended format (based on Conventional Commits, adapted for this repo):

- Header: type(scope?): short summary
- Blank line
- Body: more detailed explanation (wrap at ~72 chars)
- Blank line
- Footer: references (e.g., issue numbers, breaking changes)

Rules:
- Use imperative, present-tense verb in the header ("Add", "Fix", "Remove").
- Keep the header <= 50 characters when possible.
- Use a scope when it helps clarify the area changed, e.g., `ValueObject`, `Tests`, `CI`.
- Limit body lines to ~72 characters.
- Reference issue or PR numbers in the footer using `Fixes #123` or `Refs #123`.
- Mark breaking changes in the footer using `BREAKING CHANGE: description`.

Common types we use:
- feat: a new feature
- fix: a bug fix
- docs: changes to documentation
- style: formatting, missing semicolons, whitespace (no code change)
- refactor: code change that neither fixes a bug nor adds a feature
- test: adding or updating tests
- chore: tooling, build processes, package updates, ci
- ci: changes to CI configuration

Examples:

- feat(ValueObject): add RepositoryIdentity value object

   Introduce a new immutable value object to represent repository identity.
   Includes validation and unit tests.

- fix(Email): validate email addresses with stricter regex

   Fixes an edge case where emails with plus addressing were rejected.

- docs: update CONTRIBUTING.md with testing instructions

Prefixing with ticket IDs (e.g., `ABC-123: ...`) is optional—use it when your workflow links commits to issue trackers.

When in doubt, write a short, descriptive header and a body that explains the why, not only the what.


## Remember
- Quality over speed
- Write code that is easy to read and maintain
- Document the "why" not just the "what"
- Think about the domain, not just the code
- Every class should tell a story about the domain
