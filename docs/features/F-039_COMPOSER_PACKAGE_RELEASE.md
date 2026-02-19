# Feature F-039: Composer Package & Release

**Feature ID:** F-039  
**Priority:** MVP (MUST HAVE)  
**Phase:** 10 — Documentation & Release  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Finalize the Composer package for distribution: update `composer.json` with correct metadata and dependencies, set up CI/CD pipeline (GitHub Actions), tag version v0.1.0, and submit to Packagist. This feature represents the final gate before public release.

## User Stories

**US-039.1:** As a **developer**, I want to install the OAI-PMH server via Composer so that I can integrate it with minimal effort.

**US-039.2:** As a **repository administrator**, I want a versioned release so that I can depend on a stable, known version.

**US-039.3:** As a **maintainer**, I want CI/CD that runs tests, PHPStan, and PHPCS on every push so that code quality is enforced.

## Acceptance Criteria

### composer.json
- [ ] Package name: `pslits/oai-pmh` (or as per project)
- [ ] Type: `library`
- [ ] License: MIT
- [ ] PHP requirement: `^8.0`
- [ ] All runtime dependencies declared correctly
- [ ] Dev dependencies separated (PHPUnit, PHPStan, PHPCS)
- [ ] PSR-4 autoload configured for `OaiPmh\\` → `src/`
- [ ] PSR-4 autoload-dev configured for `OaiPmh\\Tests\\` → `tests/`
- [ ] `bin` section: `bin/oaipmh`

### CI/CD (GitHub Actions)
- [ ] Test matrix: PHP 8.0, 8.1, 8.2, 8.3
- [ ] Run PHPUnit tests
- [ ] Run PHPStan Level 8
- [ ] Run PHP_CodeSniffer (PSR-12)
- [ ] Code coverage reporting
- [ ] MySQL and PostgreSQL service containers for integration tests

### Release
- [ ] Version tagged: `v0.1.0`
- [ ] CHANGELOG.md updated
- [ ] GitHub Release created with release notes
- [ ] Packagist submission (optional for v0.1.0)

### Quality Gates (must pass before release)
- [ ] All unit tests pass
- [ ] All integration tests pass (F-037)
- [ ] PHPStan Level 8: 0 errors
- [ ] PHPCS: 0 violations
- [ ] Code coverage > 90%
- [ ] Documentation complete (F-038)

## Dependencies

### Blocked By
- **F-037** Integration Testing & OAI-PMH Compliance (all tests pass)
- **F-038** Documentation & Developer Guides (docs complete)
- **All MVP features** must be implemented and tested

### Blocks
- None (this is the final release feature)

## Files

```
composer.json                              (update)
.github/workflows/ci.yml                  (create)
.github/workflows/release.yml             (create, optional)
CHANGELOG.md                               (update)
```

## Testing Requirements

- [ ] `composer install` succeeds from clean state
- [ ] `composer validate` passes
- [ ] All CI jobs pass on PHP 8.0, 8.1, 8.2, 8.3
- [ ] Package can be installed via Composer from VCS repository

## Notes

- Consider using `roave/security-advisories` to check for vulnerable dependencies.
- The CI/CD pipeline should be the single source of truth for "is this ready to release?"
- Semantic versioning: v0.1.0 indicates initial development release.
- Packagist submission can be deferred until the API is stable (v1.0.0).
