# Refactoring Safety Guide

Complete checklist for safely refactoring value objects without breaking existing functionality.

## Common Refactoring Scenarios

### 1. Parameter Renaming

**What:** Renaming constructor or method parameters to be more descriptive.

**Example:** Changing `$value` → `$baseUrl` in constructor and validation methods.

**Complete Checklist:**

1. ✅ **Update parameter declaration** in method signature
2. ✅ **Update ALL references** to that parameter within the method body
3. ✅ **Update @param docblock** tag with new name
4. ✅ **Update related test files** that call the method
5. ✅ **Run full test suite** to verify no references were missed
6. ✅ **Run PHPStan** to catch any type mismatches

**Common Mistake:**

```php
// ❌ WRONG: Renamed parameter but forgot to update reference
public function __construct(string $baseUrl)
{
    $this->validate($value);  // ← BUG: $value no longer exists
    $this->value = $baseUrl;
}

// ✅ CORRECT: All references updated
public function __construct(string $baseUrl)
{
    $this->validate($baseUrl);  // ← Fixed
    $this->value = $baseUrl;
}
```

**Validation Example:**

```php
// Before refactoring
public function __construct(string $value)
{
    $this->validate($value);
    $this->value = $value;
}

private function validate(string $value): void
{
    if (empty($value)) {
        throw new InvalidArgumentException('Value cannot be empty');
    }
}

// After refactoring - ALL references updated
public function __construct(string $baseUrl)
{
    $this->validate($baseUrl);
    $this->value = $baseUrl;
}

private function validate(string $baseUrl): void
{
    if (empty($baseUrl)) {
        throw new InvalidArgumentException('BaseURL cannot be empty');
    }
}
```

### 2. Adding Domain-Specific Getters

**What:** Adding domain-specific getter methods (REQUIRED pattern).

**Example:** Adding `getBaseUrl()` as the primary getter.

**Complete Checklist:**

1. ✅ **Add domain-specific getter method** (e.g., `getBaseUrl()`)
2. ✅ **Name after the domain concept** (not generic like `getValue()`)
3. ✅ **Return the encapsulated value**
4. ✅ **Add comprehensive docblock**
5. ✅ **Keep `getValue()` as backward-compatible alias** (optional)
6. ✅ **Update test assertions** to use new getter (or keep getValue() if preferred)
7. ✅ **Document both methods** clearly in docblocks
8. ✅ **Run full test suite** to ensure nothing breaks

**Example:**

```php
final class BaseURL
{
    private string $value;

    public function __construct(string $baseUrl)
    {
        $this->validate($baseUrl);
        $this->value = $baseUrl;
    }

    /**
     * Returns the base URL (domain-specific getter).
     *
     * @return string The encapsulated base URL.
     */
    public function getBaseUrl(): string  // ← REQUIRED domain-specific getter
    {
        return $this->value;
    }

    /**
     * Returns the value (alias for getBaseUrl).
     *
     * Provided for backward compatibility with other value objects.
     *
     * @return string The encapsulated base URL.
     */
    public function getValue(): string  // ← Optional backward-compatible alias
    {
        return $this->value;
    }

    // ... other methods
}
```

**Test Updates:**

```php
// Before (if only getValue() existed)
$baseUrl = new BaseURL('https://example.org/oai');
$this->assertSame('https://example.org/oai', $baseUrl->getValue());

// After (using domain-specific getter)
$baseUrl = new BaseURL('https://example.org/oai');
$this->assertSame('https://example.org/oai', $baseUrl->getBaseUrl());
// getValue() still works for backward compatibility
$this->assertSame('https://example.org/oai', $baseUrl->getValue());
```

### 3. Validation Refactoring

**What:** Splitting complex validation logic into focused private methods.

**Example:** Splitting URL validation into `validateNotEmpty()`, `validateUrlFormat()`, `validateHttpProtocol()`.

**Complete Checklist:**

1. ✅ **Create main `validate()` method** that orchestrates sub-validators
2. ✅ **Name sub-validators descriptively:** `validateNotEmpty()`, `validateFormat()`, `validateBusinessRule()`
3. ✅ **Each validator has single responsibility**
4. ✅ **Pass value as parameter** to validators (don't access $this->value directly)
5. ✅ **Document each validator's purpose** with clear docblock
6. ✅ **Keep validators private**
7. ✅ **Ensure all validation paths still tested**
8. ✅ **Run full test suite** to verify behavior unchanged

**Example:**

```php
// Before: All validation in one method
private function validate(string $baseUrl): void
{
    if (empty($baseUrl)) {
        throw new InvalidArgumentException('BaseURL cannot be empty.');
    }
    
    if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
        throw new InvalidArgumentException(
            sprintf('Invalid URL format: %s', $baseUrl)
        );
    }
    
    if (!preg_match('/^https?:\/\//i', $baseUrl)) {
        throw new InvalidArgumentException(
            sprintf('BaseURL must use HTTP or HTTPS protocol: %s', $baseUrl)
        );
    }
}

// After: Focused validators with single responsibility
private function validate(string $baseUrl): void
{
    $this->validateNotEmpty($baseUrl);
    $this->validateUrlFormat($baseUrl);
    $this->validateHttpProtocol($baseUrl);
}

/**
 * Validates that the base URL is not empty.
 *
 * @param string $baseUrl The base URL to validate.
 * @throws InvalidArgumentException If the base URL is empty.
 */
private function validateNotEmpty(string $baseUrl): void
{
    if (empty($baseUrl)) {
        throw new InvalidArgumentException('BaseURL cannot be empty.');
    }
}

/**
 * Validates that the base URL has a valid URL format.
 *
 * @param string $baseUrl The base URL to validate.
 * @throws InvalidArgumentException If the URL format is invalid.
 */
private function validateUrlFormat(string $baseUrl): void
{
    if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
        throw new InvalidArgumentException(
            sprintf('Invalid URL format: %s', $baseUrl)
        );
    }
}

/**
 * Validates that the base URL uses HTTP or HTTPS protocol.
 *
 * @param string $baseUrl The base URL to validate.
 * @throws InvalidArgumentException If the protocol is not HTTP or HTTPS.
 */
private function validateHttpProtocol(string $baseUrl): void
{
    if (!preg_match('/^https?:\/\//i', $baseUrl)) {
        throw new InvalidArgumentException(
            sprintf('BaseURL must use HTTP or HTTPS protocol: %s', $baseUrl)
        );
    }
}
```

### 4. Method Signature Changes

**What:** Changing method parameters or return types in equals(), __toString(), etc.

**Complete Checklist:**

1. ✅ **Update method signature** (parameters, return type)
2. ✅ **Update method body** to match new signature
3. ✅ **Update docblock** (@param, @return tags)
4. ✅ **Update all callers** (tests, other classes)
5. ✅ **Run PHPStan** to catch type mismatches
6. ✅ **Run full test suite**

**Example: equals() Parameter Renaming**

```php
// Before
public function equals(self $other): bool
{
    return $this->value === $other->value;
}

// After: More descriptive parameter name
public function equals(self $otherBaseUrl): bool
{
    return $this->value === $otherBaseUrl->value;
}
```

## Verification Steps After Refactoring

Run these verification steps in order:

### 1. Static Analysis
```bash
# PHPStan Level 8
vendor\bin\phpstan analyse src/Domain/ValueObject/[FileName].php
```

**Expected:** 0 errors

### 2. Code Standards
```bash
# PSR-12 compliance
vendor\bin\phpcs src/Domain/ValueObject/[FileName].php
```

**Expected:** 0 violations

### 3. Unit Tests
```bash
# Run specific test file
vendor\bin\phpunit tests/Domain/ValueObject/[FileName]Test.php
```

**Expected:** All tests pass

### 4. Full Test Suite
```bash
# Run all tests
vendor\bin\phpunit
```

**Expected:** All tests pass, no regressions

### 5. Coverage Check
```bash
# Generate coverage report
vendor\bin\phpunit --coverage-html coverage/html
```

**Expected:** Maintain or improve coverage percentage

## Common Mistakes and Solutions

### Mistake 1: Incomplete Parameter Renaming

**Problem:** Renamed parameter in signature but not in method body.

**Solution:** Search entire method for old parameter name before committing.

**Check:** PHPStan will catch undefined variable errors.

### Mistake 2: Breaking Backward Compatibility

**Problem:** Removed `getValue()` when adding domain-specific getter.

**Solution:** Keep `getValue()` as an alias for existing code.

**Check:** Run full test suite to catch calls to removed methods.

### Mistake 3: Validation Path Not Tested

**Problem:** Added new validation method but no test coverage.

**Solution:** Add tests for all validation scenarios (valid and invalid).

**Check:** Run coverage report to identify untested code paths.

### Mistake 4: Forgot to Update Docblocks

**Problem:** Changed parameter names but @param tags still use old names.

**Solution:** Update docblocks at same time as code changes.

**Check:** PHPStan will warn about docblock mismatches.

### Mistake 5: Tests Use Old Getter Names

**Problem:** Added `getBaseUrl()` but tests still call `getValue()`.

**Solution:** Update test assertions to use domain-specific getter (or decide to keep getValue() calls).

**Check:** Code review to ensure tests use preferred API.

## Refactoring Workflow

**Recommended workflow for safe refactoring:**

1. **Create a git branch** for the refactoring
2. **Run baseline tests** to ensure everything passes
3. **Make one change at a time** (e.g., rename one parameter)
4. **Update all references** immediately after each change
5. **Run PHPStan** to catch issues
6. **Run tests** to verify behavior unchanged
7. **Commit atomic changes** with descriptive messages
8. **Repeat** for next change
9. **Update analysis document** when complete
10. **Final verification** with full test suite and coverage

## Red Flags During Refactoring

Watch for these warning signs:

- ❌ Tests suddenly failing after "simple" rename
- ❌ PHPStan reporting undefined variables
- ❌ Code coverage percentage drops
- ❌ New warnings about unused variables
- ❌ Test assertions updated to match "new expected behavior"

**If you see these:** Stop and review changes carefully. Refactoring should NOT change behavior, only structure.

## When to Update Analysis Document

Update the analysis document after refactoring when:

- ✅ New methods added (domain-specific getters)
- ✅ Validation logic refactored (split into validators)
- ✅ Parameter names changed (affects code examples)
- ✅ New design decisions made

**What to update:**
1. **Implementation Details** - Update class structure table with new methods
2. **Code Examples** - Update examples using new method/parameter names
3. **Design Decisions** - Add new subsection explaining refactoring rationale
4. **Test Coverage** - Update if coverage metrics changed

**See:** `.github/copilot-instructions.md` section "Updating Analysis Documents After Refactoring"
