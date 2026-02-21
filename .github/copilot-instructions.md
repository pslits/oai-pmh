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
 * [Short description of the file's purpose]
 *
 * @author    [author] <[email]>
 * @copyright (c) [Year when created] Paul Slits
 * @license   MIT License - https://opensource.org/licenses/MIT
 * @link      https://github.com/pslits/oai-pmh
 * @since     [Project version when created]
 */
```

**Note:** The `@since` version should match the `"version"` field in `composer.json` at the time the file was created. Check `composer.json` for the current version (currently `0.1.0`).

## Domain-Driven Design Patterns

### Value Objects
- **Always** make value objects `final`
- **Always** make value objects immutable (no setters)
- Include validation in the constructor
- Throw `InvalidArgumentException` for validation failures
- Implement these methods:
  - Domain-specific getter (e.g., `getBaseUrl()`, `getDeletedRecord()`, `getProtocolVersion()`)
  - `getValue()` as an alias for backward compatibility
  - `equals(self $otherInstance): bool` for value comparison (use descriptive parameter names)
  - `__toString(): string` for string representation
- Use descriptive error messages in exceptions
- When validation is complex, extract into separate private methods (e.g., `validateNotEmpty()`, `validateFormat()`)

#### Getter Method Pattern
Value objects should provide:
1. **Domain-specific getter**: Named after the domain concept (e.g., `getBaseUrl()`, `getRepositoryName()`)
2. **Generic getter alias**: `getValue()` for consistency and backward compatibility
3. Both should return the same value; the domain-specific name improves code readability

#### Parameter Naming
- **Constructor parameters**: Use descriptive names matching the domain concept (e.g., `$baseUrl`, not `$url`)
- **equals() method**: Use descriptive parameter names (e.g., `$otherBaseUrl`, not `$other`)
- **Validation methods**: Use descriptive parameter names matching what they validate
- **CRITICAL**: When renaming parameters, update ALL references within the method body

### Example Value Object Pattern:
```php
final class ExampleValue
{
    private string $value;

    /**
     * Constructs a new ExampleValue instance.
     *
     * @throws InvalidArgumentException If validation fails.
     */
    public function __construct(string $exampleValue)
    {
        $this->validate($exampleValue);
        $this->value = $exampleValue;
    }

    /**
     * Returns the example value (domain-specific getter).
     */
    public function getExampleValue(): string
    {
        return $this->value;
    }

    /**
     * Returns the value (alias for getExampleValue).
     *
     * Provided for consistency with other value objects.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Checks if this ExampleValue is equal to another.
     */
    public function equals(self $otherExampleValue): bool
    {
        return $this->value === $otherExampleValue->value;
    }

    /**
     * Returns a string representation of the ExampleValue object.
     *
     * Format: `ExampleValue(value: <value>)`
     */
    public function __toString(): string
    {
        return sprintf('ExampleValue(value: %s)', $this->value);
    }

    /**
     * Validates the example value.
     *
     * For complex validation, split into separate methods:
     * - validateNotEmpty()
     * - validateFormat()
     * - validateBusinessRule()
     *
     * @throws InvalidArgumentException If validation fails.
     */
    private function validate(string $exampleValue): void
    {
        $this->validateNotEmpty($exampleValue);
        // Add more validation as needed
    }

    /**
     * Validates that the value is not empty.
     *
     * @throws InvalidArgumentException If the value is empty.
     */
    private function validateNotEmpty(string $exampleValue): void
    {
        if (empty($exampleValue)) {
            throw new InvalidArgumentException('ExampleValue cannot be empty');
        }
    }
}
```

### Collection Objects
- Collections should be type-safe and contain only one type of object
- Implement `\Countable` and `\IteratorAggregate` interfaces when appropriate
- Validate items when adding to the collection
- Make collections immutable when possible

### Refactoring Safety Guidelines

When refactoring value objects (renaming parameters, methods, or extracting validation):

#### Parameter Renaming Checklist
1. ✅ Update the parameter declaration in method signature
2. ✅ Update ALL references to that parameter within the method body
3. ✅ Update @param docblock tags
4. ✅ Update related test files that call the method
5. ✅ Run tests to verify no references were missed
6. ✅ Run PHPStan to catch any type mismatches

**Common Mistake**: Renaming `$value` → `$exampleValue` in the constructor but forgetting to update `$this->validate($value)` → `$this->validate($exampleValue)`

#### Method Signature Changes
When adding domain-specific getters (e.g., `getBaseUrl()`) while keeping `getValue()`:
1. ✅ Add the new domain-specific getter method
2. ✅ Keep `getValue()` as an alias for backward compatibility
3. ✅ Update test files to use the new getter (or keep getValue() if preferred)
4. ✅ Document both methods clearly in docblocks
5. ✅ Run full test suite to ensure nothing breaks

#### Validation Refactoring Pattern
When splitting complex validation into separate methods:
1. ✅ Create a main `validate()` method that calls sub-validators
2. ✅ Name sub-validators descriptively: `validateNotEmpty()`, `validateFormat()`, `validateHttpProtocol()`
3. ✅ Each validator should have a single responsibility
4. ✅ Pass the value being validated as a parameter (don't access $this->value)
5. ✅ Document each validator's purpose
6. ✅ Keep validators private
7. ✅ Ensure tests still cover all validation paths

Example:
```php
private function validateUrl(string $baseUrl): void
{
    $this->validateNotEmpty($baseUrl);
    $this->validateUrlFormat($baseUrl);
    $this->validateHttpProtocol($baseUrl);
}

private function validateNotEmpty(string $baseUrl): void
{
    if (empty($baseUrl)) {
        throw new InvalidArgumentException('BaseURL cannot be empty.');
    }
}
```

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

### Test Update Requirements
When changing value object method signatures:
- **Adding domain-specific getters**: Update test assertions to use new getter names
  - Example: `$baseUrl->getValue()` → `$baseUrl->getBaseUrl()`
- **Changing parameter names**: Review if tests pass parameters that need renaming
- **Refactoring validation**: Ensure all validation paths are still tested
- **Always run full test suite** after refactoring to catch breaking changes
- If tests fail after refactoring, **fix the tests**, not the value object (unless there's a real bug)

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

### Code Documentation
- Add docblocks to all new PHP classes, methods, and properties following PHPDoc standards

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

## Commit Message Guidelines

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

---

## Team Collaboration

This project uses specialized GitHub Copilot agents for different development roles. For information about agent workflows, roles, and collaboration patterns, see:

**📁 [.github/agents/README.md](agents/README.md)**

---

## Remember

- Quality over speed
- Write code that is easy to read and maintain
- Document the "why" not just the "what"
- Think about the domain, not just the code
- Every class should tell a story about the domain

---

*For coding standards and implementation patterns, see above. For team workflows and agent collaboration, see [.github/agents/](agents/).*