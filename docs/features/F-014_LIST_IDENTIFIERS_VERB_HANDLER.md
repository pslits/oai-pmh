# Feature F-014: ListIdentifiers Verb Handler

**Feature ID:** F-014  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `ListIdentifiersHandler` that returns record headers (identifier, datestamp, setSpecs) without metadata bodies. This is the lightweight alternative to ListRecords, used by harvesters that only need to know which records exist and when they were last modified. Supports selective harvesting (from/until, set) and resumption tokens for pagination.

## User Stories

**US-014.1:** As a **harvester**, I want to list record identifiers efficiently (without full metadata) so that I can determine which records to fetch individually.

**US-014.2:** As a **harvester**, I want to filter identifiers by date range and set so that I only discover records relevant to my needs.

## Acceptance Criteria

- [ ] Requires `metadataPrefix` argument (unless resumptionToken present)
- [ ] Returns record headers: `<identifier>`, `<datestamp>`, `<setSpec>` (zero or more)
- [ ] Deleted records included with `status="deleted"` attribute
- [ ] Supports optional `from` and `until` date arguments (selective harvesting)
- [ ] Supports optional `set` argument (set-based filtering)
- [ ] Supports `resumptionToken` (exclusive with other arguments except verb)
- [ ] Returns `noRecordsMatch` when no results found
- [ ] Returns `badArgument` for invalid/missing arguments
- [ ] Returns `cannotDisseminateFormat` for unsupported metadataPrefix
- [ ] Returns `badResumptionToken` for invalid/expired tokens
- [ ] Resumption token includes `completeListSize` and `cursor` when available
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response time < 500ms for first page (100 records)
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-004** Record & RecordHeader Entities (RecordHeader data)
- **F-006** Database Schema Mapping (query generation)
- **F-007** Database Adapters (query execution)
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-017** Metadata Format Plugin System (validate metadataPrefix)
- **F-020** Resumption Tokens & Pagination
- **F-021** Selective Harvesting (from/until filtering)

### Blocks
- **F-037** Integration Testing

## Files

```
src/Protocol/Service/Handler/ListIdentifiersHandler.php
tests/Protocol/Service/Handler/ListIdentifiersHandlerTest.php
```

## Testing Requirements

- [ ] Valid ListIdentifiers request returns headers
- [ ] Filter by from date
- [ ] Filter by until date
- [ ] Filter by from AND until (date range)
- [ ] Filter by set
- [ ] Combined filters (date range + set)
- [ ] Deleted records included with status="deleted"
- [ ] noRecordsMatch for empty results
- [ ] Pagination with resumption tokens
- [ ] badArgument for missing metadataPrefix
- [ ] cannotDisseminateFormat for unknown prefix
- [ ] badResumptionToken for expired token
- [ ] Response XML validates against XSD

## Notes

- ListIdentifiers returns the same headers as ListRecords but without the `<metadata>` element, making it significantly faster for large result sets.
