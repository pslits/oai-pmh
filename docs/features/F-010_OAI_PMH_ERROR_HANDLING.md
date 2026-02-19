# Feature F-010: OAI-PMH Error Handling

**Feature ID:** F-010  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Request Parsing & Response Assembly  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the OAI-PMH error code system as a PHP backed enum (`OaiErrorCode`) and an `OaiError` aggregate. All eight OAI-PMH 2.0 error codes must be supported. The system maps internal exceptions to the appropriate OAI-PMH error codes and generates compliant error responses with descriptive messages.

## User Stories

**US-010.1:** As a **harvester**, I want to receive standard OAI-PMH error codes with descriptive messages so that I can programmatically handle errors and fix my requests.

**US-010.2:** As the **server**, I want a centralized error mapping system so that exceptions from any context are translated to proper OAI-PMH error responses.

## Acceptance Criteria

- [ ] `OaiErrorCode` backed enum with all 8 OAI-PMH error codes:
  - `badArgument` — illegal or missing argument
  - `badResumptionToken` — invalid or expired resumption token
  - `badVerb` — illegal OAI-PMH verb
  - `cannotDisseminateFormat` — metadata format not supported
  - `idDoesNotExist` — identifier unknown in repository
  - `noRecordsMatch` — no records match the criteria
  - `noMetadataFormats` — no metadata formats available for item
  - `noSetHierarchy` — repository does not support sets
- [ ] `OaiError` aggregate with code and human-readable message
- [ ] Exception-to-error-code mapping service
- [ ] Error responses comply with OAI-PMH 2.0 XML schema
- [ ] Multiple errors can be included in a single response
- [ ] HTTP status code is always 200 for OAI-PMH errors (per specification)
- [ ] Error messages do not expose internal details (stack traces, file paths)
- [ ] PHPStan Level 8 passes

## Technical Design

### Error Code Enum (PHP 8.1+)

```php
enum OaiErrorCode: string {
    case BadArgument = 'badArgument';
    case BadResumptionToken = 'badResumptionToken';
    case BadVerb = 'badVerb';
    case CannotDisseminateFormat = 'cannotDisseminateFormat';
    case IdDoesNotExist = 'idDoesNotExist';
    case NoRecordsMatch = 'noRecordsMatch';
    case NoMetadataFormats = 'noMetadataFormats';
    case NoSetHierarchy = 'noSetHierarchy';
}
```

### Important Protocol Rule
Per OAI-PMH 2.0: error responses always use HTTP 200 status code. The error information is conveyed in the XML body, not via HTTP status codes.

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts

### Blocks
- **F-009** OAI-PMH XML Response Assembly (uses OaiErrorCode in `addError()`)
- **F-011** through **F-016** All Verb Handlers (return errors for invalid requests)
- **F-008** OAI-PMH Request Parsing (produces badVerb/badArgument errors)

## Files

```
src/Protocol/ValueObject/OaiErrorCode.php
src/Protocol/Aggregate/OaiError.php
src/Protocol/Service/ExceptionMapper.php
tests/Protocol/ValueObject/OaiErrorCodeTest.php
tests/Protocol/Aggregate/OaiErrorTest.php
tests/Protocol/Service/ExceptionMapperTest.php
```

## Testing Requirements

- [ ] All 8 error codes instantiatable
- [ ] Error code string values match OAI-PMH spec exactly
- [ ] OaiError creation with code and message
- [ ] Exception mapping: internal exceptions → correct OAI error codes
- [ ] Error response XML validates against OAI-PMH XSD
- [ ] No sensitive information in error messages

## Notes

- PHP 8.0 compatibility note: if PHP 8.0 support is required, use a class with string constants instead of a backed enum. ADR-008 should clarify the minimum PHP version.
- The `ExceptionMapper` centralizes the mapping from domain exceptions to OAI error codes.
