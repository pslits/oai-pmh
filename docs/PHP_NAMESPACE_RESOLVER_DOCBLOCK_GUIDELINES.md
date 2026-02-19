# PHP Namespace Resolver Docblock Guidelines

**Document Date:** February 15, 2026  
**Issue:** PHP Namespace Resolver false positives in docblocks  
**Scope:** OAI-PMH project coding standards  
**Status:** Active guideline

---

## Executive Summary

The PHP Namespace Resolver extension in VS Code parses docblock tags and certain text patterns as type/class references, causing false "class not imported" warnings. This document provides guidelines to avoid these false positives while maintaining clear, informative documentation.

---

## Background

### The Problem

The PHP Namespace Resolver analyzes docblocks to identify type references (classes, interfaces, traits) that need to be imported. However, it can misinterpret certain text patterns as class names, generating false warnings.

### Common Triggers

1. **Text after `@see` tags** containing hyphens (e.g., `@see OAI-PMH 2.0 Specification`)
2. **Quoted text in bullet lists** with colons (e.g., `- identifier: "Unique identifier"`)
3. **Certain formatting patterns** that resemble type declarations

### Why It Happens

The resolver uses heuristics to identify type references in docblocks. It treats specific tags (`@see`, `@var`, `@param`, `@return`, `@throws`, `@uses`, `@link`) as containing type information and attempts to resolve any non-primitive identifiers as class names.

---

## Examples of Problematic Patterns

### ❌ Pattern 1: Text after @see tag

```php
/**
 * Represents an OAI-PMH record header.
 *
 * @see OAI-PMH 2.0 Specification Section 2.5
 */
```

**Problem:** "OAI-PMH" is interpreted as a class name (invalid due to hyphens).

### ❌ Pattern 2: Quoted text in bullet lists

```php
/**
 * Header components:
 * - identifier (required): "Unique identifier for the item"
 * - datestamp (required): "Date of creation, modification, or deletion"
 */
```

**Problem:** The quoted text after the colon is parsed as a class name.

### ❌ Pattern 3: @see with textual references

```php
/**
 * @see Section 4.2 in OAI-PMH Protocol
 */
```

**Problem:** The resolver tries to interpret the text as a type reference.

---

## Recommended Solutions

### ✅ Solution 1: Use URLs in @see tags

**Before:**
```php
/**
 * @see OAI-PMH 2.0 Specification Section 2.5
 */
```

**After:**
```php
/**
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
 */
```

**Why:** The resolver recognizes URLs and doesn't try to parse them as types.

### ✅ Solution 2: Move spec references to description

**Before:**
```php
/**
 * Represents an OAI-PMH record header.
 *
 * @see OAI-PMH 2.0 Specification Section 2.5
 */
```

**After:**
```php
/**
 * Represents an OAI-PMH record header.
 *
 * According to OAI-PMH 2.0 specification section 2.5 (Record), a record header
 * contains core metadata about an item in a repository.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
 */
```

**Why:** Plain description text (not in special tags) isn't parsed as type references.

### ✅ Solution 3: Remove quotes from bullet list descriptions

**Before:**
```php
/**
 * Header components:
 * - identifier (required): "Unique identifier for the item"
 * - datestamp (required): "Date of creation, modification, or deletion"
 */
```

**After:**
```php
/**
 * Header components:
 * - identifier (required): Unique identifier for the item
 * - datestamp (required): Date of creation, modification, or deletion
 */
```

**Why:** Removing quotes prevents the resolver from misinterpreting the text.

### ✅ Solution 4: Use prose instead of structured lists

**Before:**
```php
/**
 * Header components:
 * - identifier (required): "Unique identifier for the item"
 */
```

**After:**
```php
/**
 * Header components (per OAI-PMH specification):
 *
 * The identifier (required) is the unique identifier for the item.
 * The datestamp (required) is the date of creation, modification, or deletion.
 */
```

**Why:** Natural prose is less likely to trigger false positives.

### ✅ Solution 5: Use em-dashes or other separators

**Before:**
```php
/**
 * - identifier (required): "Unique identifier for the item"
 */
```

**After:**
```php
/**
 * - identifier (required) — Unique identifier for the item
 * - datestamp (required) — Date of creation, modification, or deletion
 */
```

**Why:** Using em-dashes (—) instead of colons with quotes changes the parsing pattern.

---

## Best Practices for OAI-PMH Project

### Docblock Structure

Follow this recommended structure for class docblocks:

```php
/**
 * [One-line summary of the class]
 *
 * According to OAI-PMH 2.0 specification section X.Y (Title), [explanation
 * of what the specification says about this component].
 *
 * [Additional context about the component's role]
 *
 * [List of characteristics or features using prose or em-dash separated bullets]
 *
 * This [value object|entity|collection]:
 * - [characteristic 1],
 * - [characteristic 2],
 * - [characteristic 3].
 *
 * @see [URL to specification]
 */
```

### OAI-PMH Specification References

**DO:**
- ✅ Include spec references in the description paragraph
- ✅ Use full URLs in `@see` tags
- ✅ Reference spec sections in prose: "According to OAI-PMH 2.0 specification section 4.2..."

**DON'T:**
- ❌ Use `@see OAI-PMH ...` with text instead of URLs
- ❌ Use hyphens or special characters after `@see` without a URL
- ❌ Put quoted descriptions in bullet lists with colons

### Bullet Lists in Docblocks

**DO:**
- ✅ Use prose descriptions without quotes
- ✅ Use em-dashes (—) or plain colons without quotes
- ✅ Keep descriptions concise and type-identifier-free

**DON'T:**
- ❌ Quote descriptions: `"Description text"`
- ❌ Create patterns that look like type declarations
- ❌ Use complex formatting in lists

### Example: Full Class Docblock

```php
/**
 * Represents the base URL of an OAI-PMH repository.
 *
 * According to OAI-PMH 2.0 specification section 4.2 (Identify), the baseURL is
 * the base URL of the repository - the URL that is used to submit OAI-PMH requests.
 * It must be an HTTP or HTTPS URL that accepts OAI-PMH protocol requests.
 *
 * This value object:
 * - encapsulates the base URL where the repository responds to OAI-PMH requests,
 * - validates that the URL is a valid HTTP or HTTPS URL,
 * - is immutable and compared by value (not identity),
 * - is used in the Identify response to indicate the repository's endpoint.
 *
 * @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
 */
final class BaseURL
{
    // ...
}
```

---

## Recommendations

### 1. Update Copilot Instructions

**File:** `.github/copilot-instructions.md`

**Recommendation:** Add a new section on docblock formatting:

```markdown
### Docblock Best Practices

**Format for OAI-PMH specification references:**
- Include spec references in description paragraphs, not as standalone @see tags with text
- Use full URLs in @see tags: `@see http://www.openarchives.org/OAI/...`
- Format: "According to OAI-PMH 2.0 specification section X.Y (Title)..."

**Avoid PHP Namespace Resolver false positives:**
- ❌ DON'T: `@see OAI-PMH 2.0 Specification`
- ✅ DO: `@see http://www.openarchives.org/OAI/openarchivesprotocol.html`
- ❌ DON'T: `- identifier: "Unique identifier"`
- ✅ DO: `- identifier: Unique identifier` or `- identifier — Unique identifier`

**Bullet lists in docblocks:**
- Remove quotes from descriptions in bullet lists
- Use prose or em-dash (—) separators instead of colon-quote patterns
- Keep lists simple to avoid triggering type resolution
```

### 2. Create a Validation Skill

**File:** `.github/skills/docblock-validator/SKILL.md`

**Purpose:** Validate docblocks against project standards and check for patterns that trigger false PHP Namespace Resolver warnings.

**Skill Definition:**

```markdown
# Docblock Validator Skill

## Description
Validates PHP docblocks in the OAI-PMH project against coding standards, checking for:
- Proper OAI-PMH specification references
- Patterns that trigger PHP Namespace Resolver false positives
- PSR-5 PHPDoc compliance
- Consistency with project documentation standards

## When to Use
- Reviewing new or modified PHP classes
- Before committing changes with documentation updates
- When encountering "class not imported" warnings in docblocks
- As part of code review checklist

## Validation Checklist

### @see Tags
- [ ] All @see tags use full URLs (no text-only references)
- [ ] No hyphens or special characters after @see without URLs
- [ ] Specification references appear in description, not standalone @see

### Bullet Lists
- [ ] No quoted descriptions after colons in bullet lists
- [ ] Use prose or em-dash separators
- [ ] No patterns resembling type declarations

### OAI-PMH References
- [ ] Spec sections referenced in prose: "According to OAI-PMH 2.0..."
- [ ] URLs link to specific sections when possible
- [ ] Clear indication of which OAI-PMH verb/response uses the component

### General
- [ ] Class docblock includes purpose and domain context
- [ ] Method docblocks have @param, @return, @throws as needed
- [ ] No PHP Namespace Resolver warnings
- [ ] Follows PSR-5 PHPDoc standards

## Example Violations and Fixes

### Violation 1: Text in @see tag
```php
❌ @see OAI-PMH 2.0 Specification Section 2.5
✅ @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Record
```

### Violation 2: Quoted bullet descriptions
```php
❌ - identifier: "Unique identifier"
✅ - identifier: Unique identifier
✅ - identifier — Unique identifier
```

### Violation 3: Missing URL
```php
❌ According to the OAI-PMH specification...
✅ According to OAI-PMH 2.0 specification section 4.2 (Identify)...
   @see http://www.openarchives.org/OAI/openarchivesprotocol.html#Identify
```
```

### 3. Update Value Object Validator Skill

**File:** `.github/skills/value-object-validator/SKILL.md`

**Recommendation:** Add docblock validation to the existing skill checklist:

```markdown
### Documentation Standards
- [ ] Class docblock includes OAI-PMH specification reference
- [ ] @see tag uses full URL (not text-only reference)
- [ ] No PHP Namespace Resolver warnings
- [ ] Bullet lists don't use quoted descriptions with colons
- [ ] OAI-PMH context clearly explained in prose
```

### 4. Create GitHub Copilot Agent Role

**File:** `.github/agents/docblock-reviewer.md`

**Purpose:** Specialized agent for reviewing and fixing docblock issues.

**Agent Definition:**

```markdown
# Docblock Reviewer Agent

## Role
Reviews and fixes PHP docblocks to ensure:
- Compliance with OAI-PMH project standards
- No false PHP Namespace Resolver warnings
- Proper specification references
- PSR-5 PHPDoc compliance

## Responsibilities
1. Scan docblocks for patterns that trigger resolver warnings
2. Verify OAI-PMH specification references are properly formatted
3. Check bullet lists for problematic quote-colon patterns
4. Ensure @see tags use full URLs
5. Validate consistency across similar classes

## Workflow
1. Identify files with docblock issues (via linter or manual review)
2. Check each class docblock against validation checklist
3. Apply recommended fixes using multi_replace_string_in_file
4. Verify no new warnings after fixes
5. Document any new patterns discovered

## Common Fixes
- Replace `@see Text Reference` with `@see URL`
- Remove quotes from bullet list descriptions
- Move spec references from @see to description prose
- Standardize bullet list formatting (em-dash separators)
```

### 5. Add Pre-Commit Check

**Recommendation:** Add a simple grep check to catch common patterns:

```bash
# .githooks/pre-commit (example)
# Check for problematic @see patterns
if git diff --cached --name-only | grep -E '\.php$' | \
   xargs grep -n '@see [^h]' | grep -v '/vendor/'; then
    echo "WARNING: Found @see tags without URLs"
    echo "Use full URLs in @see tags to avoid PHP Namespace Resolver warnings"
fi

# Check for quoted bullet descriptions
if git diff --cached --name-only | grep -E '\.php$' | \
   xargs grep -n ' - [a-z]*:.*"[^"]*"' | grep -v '/vendor/'; then
    echo "WARNING: Found quoted descriptions in bullet lists"
    echo "Remove quotes to avoid PHP Namespace Resolver warnings"
fi
```

---

## Quick Reference Card

### ✅ Safe Patterns

```php
// OAI-PMH references in prose
"According to OAI-PMH 2.0 specification section 4.2 (Identify)..."

// @see with URL
@see http://www.openarchives.org/OAI/openarchivesprotocol.html

// Bullet lists without quotes
- identifier (required): Unique identifier for the item
- identifier — Unique identifier for the item

// "OAI-PMH" in plain description
"Represents an OAI-PMH repository."
```

### ❌ Problematic Patterns

```php
// Text after @see
@see OAI-PMH 2.0 Specification

// Quoted bullet descriptions
- identifier: "Unique identifier"

// Hyphens after @see
@see Section 4.2 - Identify
```

---

## Related Resources

- [PSR-5 PHPDoc Standard](https://github.com/php-fig/fig-standards/blob/master/proposed/phpdoc.md)
- [OAI-PMH 2.0 Specification](http://www.openarchives.org/OAI/openarchivesprotocol.html)
- [PHP Namespace Resolver Extension](https://marketplace.visualstudio.com/items?itemName=MehediDracula.php-namespace-resolver)

---

## Changelog

| Date | Version | Changes |
|------|---------|---------|
| 2026-02-15 | 1.0 | Initial guidelines based on RecordHeader.php analysis |

---

*This document is part of the OAI-PMH project coding standards and should be referenced during code reviews and when writing new documentation.*
