# Feature F-016: GetRecord Verb Handler

**Feature ID:** F-016  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `GetRecordHandler` that retrieves a single record by its unique identifier in a specified metadata format. This is the simplest data-retrieval verb and is commonly used by harvesters to fetch individual records identified through ListIdentifiers.

## User Stories

**US-016.1:** As a **harvester**, I want to retrieve a single record by identifier and metadata format so that I can fetch specific records I need.

## Acceptance Criteria

- [ ] Requires both `identifier` and `metadataPrefix` arguments
- [ ] Returns single record with header and metadata
- [ ] Deleted record returned with `status="deleted"` (header only, no metadata)
- [ ] Returns `idDoesNotExist` when identifier is unknown
- [ ] Returns `cannotDisseminateFormat` when format not supported for that record
- [ ] Returns `badArgument` for missing required arguments
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response time < 500ms
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-004** Record & RecordHeader Entities
- **F-006** Database Schema Mapping
- **F-007** Database Adapters
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-017** Metadata Format Plugin System
- **F-018** Dublin Core Plugin

### Blocks
- **F-037** Integration Testing

## Files

```
src/Protocol/Service/Handler/GetRecordHandler.php
tests/Protocol/Service/Handler/GetRecordHandlerTest.php
```

## Testing Requirements

- [ ] Valid GetRecord returns single record with metadata
- [ ] Deleted record returns header only with status="deleted"
- [ ] idDoesNotExist for unknown identifier
- [ ] cannotDisseminateFormat for unsupported format
- [ ] badArgument for missing identifier
- [ ] badArgument for missing metadataPrefix
- [ ] Response XML validates against XSD

## Notes

- For record-level access control (F-029), unauthorized records should return `idDoesNotExist` (not a distinct authorization error).
