# Feature F-012: ListMetadataFormats Verb Handler

**Feature ID:** F-012  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `ListMetadataFormatsHandler` that returns the metadata formats available from the repository. When called without an `identifier` argument, it returns all formats. When called with a specific `identifier`, it returns only formats available for that particular record.

## User Stories

**US-012.1:** As a **harvester**, I want to list all metadata formats so that I know which `metadataPrefix` values I can use for ListRecords/GetRecord.

**US-012.2:** As a **harvester**, I want to check which formats are available for a specific record so that I request an appropriate format.

## Acceptance Criteria

- [ ] Returns all enabled metadata formats when called without `identifier`
- [ ] Returns record-specific formats when called with valid `identifier`
- [ ] Each format includes:
  - `<metadataPrefix>` (e.g., `oai_dc`)
  - `<schema>` (XSD URL)
  - `<metadataNamespace>` (namespace URI)
- [ ] Returns `idDoesNotExist` error when identifier is unknown
- [ ] Returns `noMetadataFormats` error when no formats available for the item
- [ ] Returns `badArgument` for illegal arguments
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response is cacheable
- [ ] Response time < 100ms
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-017** Metadata Format Plugin System (MetadataFormatRegistry)

### Blocks
- **F-037** Integration Testing

## Files

```
src/Protocol/Service/Handler/ListMetadataFormatsHandler.php
tests/Protocol/Service/Handler/ListMetadataFormatsHandlerTest.php
```

## Testing Requirements

- [ ] List all formats (no identifier)
- [ ] List formats for specific valid record
- [ ] idDoesNotExist error for unknown identifier
- [ ] noMetadataFormats error for item with no formats
- [ ] badArgument for illegal arguments
- [ ] Response XML validates against XSD
- [ ] Caching behavior

## Notes

- `oai_dc` (Dublin Core) is the minimally recommended format per OAI-PMH spec, but the system allows custom formats too.
