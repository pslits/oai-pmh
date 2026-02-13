# Skill: API Refactoring

## When to Use
When removing or renaming public methods in value objects, entities, or any public API.

## Protocol

### Step 1: Search First
Use grep to find ALL usages in both `src/` and `tests/` directories before making changes.

**Search patterns:**
```bash
# Direct calls
grep -r "->methodName()" src/ tests/

# Chained calls
grep -r "->getIdentifier()->" src/

# Pattern matching (multiple methods)
grep -rE "\->(getValue|getIdentifier)\(\)" .

# With line numbers
grep -rn "->getValue()" tests/
```

### Step 2: Update Systematically
Update in this specific order:
1. Value object itself (method signature)
2. All source files calling the method
3. All test files with assertions
4. Entity `__toString()` methods that use the value object
5. PHPDoc comments referencing the old method name

Use `multi_replace_string_in_file` for batch updates when possible.

### Step 3: Test Incrementally
Run tests after each batch of changes:
```bash
vendor\bin\phpunit
```

Don't wait until all changes are done. Test frequently to catch issues early.

### Step 4: Document Breaking Changes
If the change is breaking, create a migration guide:

**Migration Table:**
| Old API | New API | Component |
|---------|---------|-----------|
| `$verb->getValue()` | `$verb->getVerb()` | OaiVerb |
| `$id->getIdentifier()` | `$id->getRecordIdentifier()` | RecordIdentifier |

**Files Affected List:**
- List all modified source files
- List all modified test files
- Note impact on consuming code

### Step 5: Never Assume
Never assume you found all usages. Let tests reveal what you missed.

**Common places developers forget to check:**
- Entity `__toString()` methods
- Chained method calls: `$a->getB()->getC()`
- Test assertions checking both old and new methods
- PHPDoc `@return` or `@see` tags
- Documentation files and examples

## Example from Practice

When we removed `getValue()` from 3 value objects:
- Initial search found 20+ matches
- Made changes to 3 value objects
- Tests failed → found 2 more entity `__toString()` methods
- Fixed those → tests failed again → found test assertions
- Fixed tests → all green
- **Total:** 9 files modified across 3 iterations

## Quality Checklist
- [ ] Searched both src/ and tests/ directories
- [ ] Updated value object method signature
- [ ] Updated all direct method calls
- [ ] Updated all chained method calls
- [ ] Updated entity __toString() methods
- [ ] Updated test assertions
- [ ] Updated PHPDoc comments
- [ ] Ran tests after each batch
- [ ] All tests passing
- [ ] Created migration guide (if breaking)
- [ ] Documented in commit message
