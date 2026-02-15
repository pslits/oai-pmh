# Validation Report Output Format

Detailed examples of validation report output with various scenarios.

## Standard Report Structure

```
Value Object Validation Report
==============================
File: src/Domain/ValueObject/[Name].php
Date: [Current Date]
Status: ✅ PASS / ⚠️ HAS WARNINGS / ❌ FAILED
Score: X/Y checks passed ([Z]%)

1. File Header
--------------
[Results]

2. Class Structure
------------------
[Results]

3. Required Methods
-------------------
[Results]

4. Validation Logic
-------------------
[Results]

5. Documentation
----------------
[Results]

6. Naming Conventions
---------------------
[Results]

7. OAI-PMH Compliance
---------------------
[Results]

8. Code Quality
---------------
[Results]

9. Test Coverage
----------------
[Results]

=================================
Priority Fixes (Must Fix Before Commit):
=================================
[List of ❌ FAIL items]

Recommendations (Should Fix):
================================
[List of ⚠️ WARN items]

Next Steps:
===========
1. Fix all ❌ FAIL items
2. Run quality checks to verify
3. Address ⚠️ WARN items
4. Re-validate before committing
```

## Example 1: Fully Compliant Value Object

```
Value Object Validation Report
==============================
File: src/Domain/ValueObject/DeletedRecord.php
Date: 2026-02-14 13:45:00
Status: ✅ PASS
Score: 19/20 checks passed (95%)

1. File Header
--------------
✅ PASS: @author tag present
✅ PASS: @copyright tag present
✅ PASS: @license tag present
✅ PASS: @link tag correct
✅ PASS: @since tag present

2. Class Structure
------------------
✅ PASS: Class is final
✅ PASS: Properties are private
✅ PASS: No setter methods

3. Required Methods
-------------------
✅ PASS: Has domain-specific getter (getDeletedRecord())
✅ PASS: Has equals() method with self type hint
✅ PASS: Has __toString() method

4. Validation Logic
-------------------
❌ FAIL: Missing validate() method
✅ PASS: Throws InvalidArgumentException
✅ PASS: Uses sprintf() for error messages

5. Documentation
----------------
✅ PASS: References OAI-PMH 2.0 specification
✅ PASS: Has @param documentation
✅ PASS: Has @return documentation
✅ PASS: Documents @throws

6. Naming Conventions
---------------------
✅ PASS: equals() uses descriptive parameter name
✅ PASS: Constructor uses descriptive parameter

=================================
Priority Fixes (Must Fix Before Commit):
=================================
1. ❌ Add private validate() method to coordinate validation

Next Steps:
===========
1. Fix all ❌ FAIL items
2. Run quality checks to verify:
   vendor\bin\phpstan analyse src\Domain\ValueObject\DeletedRecord.php
   vendor\bin\phpcs src\Domain\ValueObject\DeletedRecord.php
   vendor\bin\phpunit tests/Domain/ValueObject/DeletedRecordTest.php
```

## Example 2: Value Object Needing Fixes

```
Value Object Validation Report
==============================
File: src/Domain/ValueObject/BaseURL.php
Date: 2026-02-14 13:30:45
Status: ❌ FAILED
Score: 17/20 checks passed (85%)

1. File Header
--------------
✅ PASS: @author tag present
✅ PASS: @copyright tag present
✅ PASS: @license tag present
✅ PASS: @link tag correct
✅ PASS: @since tag present

2. Class Structure
------------------
✅ PASS: Class is final
✅ PASS: Properties are private
✅ PASS: No setter methods

3. Required Methods
-------------------
❌ FAIL: Missing domain-specific getter (getBaseURL())
   → CRITICAL: Must add public function getBaseURL(): string
   → Domain-specific getters are REQUIRED (not getValue())
❌ FAIL: Missing or incorrect equals() method
✅ PASS: Has __toString() method

4. Validation Logic
-------------------
❌ FAIL: Missing validate() method
✅ PASS: Throws InvalidArgumentException
✅ PASS: Uses sprintf() for error messages

5. Documentation
----------------
✅ PASS: References OAI-PMH 2.0 specification
✅ PASS: Has @param documentation
✅ PASS: Has @return documentation
✅ PASS: Documents @throws

6. Naming Conventions
---------------------
✅ PASS: equals() uses descriptive parameter name
✅ PASS: Constructor uses descriptive parameter

=================================
Priority Fixes (Must Fix Before Commit):
=================================
1. ❌ Add domain-specific getter getBaseURL() - REQUIRED
2. ❌ Fix or add equals(self $otherBaseURL): bool method
3. ❌ Add private validate() method

Next Steps:
===========
1. Fix all ❌ FAIL items
2. Run quality checks to verify:
   vendor\bin\phpstan analyse src\Domain\ValueObject\BaseURL.php
   vendor\bin\phpcs src\Domain\ValueObject\BaseURL.php
   vendor\bin\phpunit tests/Domain/ValueObject/BaseURLTest.php
```

## Example 3: Value Object with Warnings

```
Value Object Validation Report
==============================
File: src/Domain/ValueObject/Email.php
Date: 2026-02-14 14:00:00
Status: ⚠️ HAS WARNINGS
Score: 18/20 checks passed (90%)

1. File Header
--------------
✅ PASS: @author tag present
✅ PASS: @copyright tag present
✅ PASS: @license tag present
✅ PASS: @link tag correct
✅ PASS: @since tag present

2. Class Structure
------------------
✅ PASS: Class is final
✅ PASS: Properties are private
✅ PASS: No setter methods

3. Required Methods
-------------------
✅ PASS: Has domain-specific getter (getEmail())
✅ PASS: Has equals() method with self type hint
✅ PASS: Has __toString() method

4. Validation Logic
-------------------
✅ PASS: Has validate() method
✅ PASS: Throws InvalidArgumentException
✅ PASS: Uses sprintf() for error messages

5. Documentation
----------------
✅ PASS: References OAI-PMH 2.0 specification
✅ PASS: Has @param documentation
⚠️ WARN: Missing @return in some methods
⚠️ WARN: Missing @throws in constructor docblock

6. Naming Conventions
---------------------
✅ PASS: equals() uses descriptive parameter name
✅ PASS: Constructor uses descriptive parameter

7. Code Quality
---------------
PHPStan Level 8:
  ✅ PASS: 0 errors found

PSR-12:
  ✅ PASS: No violations

8. Test Coverage
----------------
Test File: tests/Domain/ValueObject/EmailTest.php
  ✅ EXISTS: Test file found
  ✅ PASS: Uses BDD-style naming
  ✅ PASS: Tests validation scenarios
  
Coverage:
  ✅ 100% line coverage
  ✅ 100% branch coverage

=================================
Recommendations (Should Fix):
================================
1. ⚠️ Add @return tags to all public methods
2. ⚠️ Add @throws InvalidArgumentException to constructor docblock

Next Steps:
===========
1. Address ⚠️ WARN items for better documentation
2. Re-validate after improvements
```

## Example 4: After Refactoring Check

```
Refactoring Validation Report
==============================
File: src/Domain/ValueObject/ProtocolVersion.php
Changes Detected:
- equals() parameter renamed: $other → $otherProtocolVersion ✅

Critical Refactoring Checklist:
✅ PASS: Parameter updated in method signature
✅ PASS: All references updated in method body
✅ PASS: Docblock @param updated
✅ PASS: Tests still pass

⚠️ INFO: Consider updating tests to use more descriptive assertions

Recommendation:
Value object refactoring is safe. All parameter references correctly updated.
```

## Status Indicators

### Score-Based Status
- **✅ PASS**: 90-100% checks passed (18-20 of 20)
- **⚠️ HAS WARNINGS**: 75-89% checks passed (15-17 of 20)
- **❌ FAILED**: <75% checks passed (<15 of 20)

### Individual Check Indicators
- **✅ PASS**: Requirement met
- **⚠️ WARN**: Best practice violation (non-blocking)
- **❌ FAIL**: Critical issue requiring fix
- **ℹ️ INFO**: Informational note (no action required)

## Output Sections

### 1. File Header
Checks: @author, @copyright, @license, @link, @since tags

### 2. Class Structure
Checks: final class, private properties, immutability, required methods

### 3. Required Methods
Checks: Domain-specific getter, equals(), __toString()

### 4. Validation Logic
Checks: validate() method exists, exception handling, error messages

### 5. Documentation
Checks: Class docblock, OAI-PMH spec references, method docblocks

### 6. Naming Conventions
Checks: Parameter naming (descriptive vs generic)

### 7. OAI-PMH Compliance
Checks: Specification adherence, correct implementation

### 8. Code Quality
Checks: PHPStan Level 8, PSR-12 compliance

### 9. Test Coverage
Checks: Test file exists, BDD style, coverage percentage

---

**Grep patterns for finding specific report sections:**
- Find validation reports: `/Value Object Validation Report/`
- Find failed checks: `/❌ FAIL:/`
- Find warnings: `/⚠️ WARN:/`
- Find priority fixes: `/Priority Fixes/`
- Find specific section: `/^\d+\. \w+ \w+/`
