# Feature F-015: ListRecords Verb Handler

**Feature ID:** F-015  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `ListRecordsHandler` that returns full records including headers and serialized metadata. This is the primary verb used by harvesters to retrieve complete metadata records. Supports selective harvesting (from/until, set), metadata format selection, and resumption tokens for paginating large result sets.

## User Stories

**US-015.1:** As a **harvester**, I want to retrieve complete records with metadata so that I can ingest them into my repository/portal.

**US-015.2:** As a **harvester**, I want to incrementally harvest only records changed since my last harvest (using from/until) to minimize network traffic and processing time.

**US-015.3:** As a **harvester**, I want to paginate through millions of records using resumption tokens so that I can complete a full harvest without timeouts.

## Acceptance Criteria

- [ ] Requires `metadataPrefix` argument (unless resumptionToken present)
- [ ] Returns full records: header (`<identifier>`, `<datestamp>`, `<setSpec>`) + `<metadata>` element
- [ ] Metadata serialized by requested format plugin (e.g., oai_dc → Dublin Core XML)
- [ ] Deleted records returned with `status="deleted"` (header only, no metadata)
- [ ] Supports optional `from` and `until` date arguments
- [ ] Supports optional `set` argument
- [ ] Supports `resumptionToken` (exclusive)
- [ ] Returns `noRecordsMatch` when no results found
- [ ] Returns `badArgument` for invalid/missing arguments
- [ ] Returns `cannotDisseminateFormat` for unsupported metadataPrefix
- [ ] Returns `badResumptionToken` for invalid/expired tokens
- [ ] Resumption token includes `completeListSize` and `cursor` when available
- [ ] Page size configurable (default: 100)
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response time < 500ms for first page
- [ ] Throughput: ≥ 1,000 records/second
- [ ] PHPStan Level 8 passes

## Technical Design

### Data Flow
1. Receive validated `OaiRequest` (verb=ListRecords)
2. Check cache → return cached response if hit
3. If resumptionToken: decode token, restore query context
4. Build query criteria (metadataPrefix, from, until, set)
5. Query database via SchemaMapping/StorageAdapter → RecordCollection
6. Apply access control filter (remove restricted records)
7. For each record: serialize metadata via MetadataSerializer
8. If more pages: create resumption token
9. Build OaiResponse with ListRecords content
10. Cache response
11. Log request + metrics

### Performance Considerations
- Metadata lazy-loaded only for the current page
- XML streaming (XMLWriter) for very large pages (post-MVP)
- Database cursor-based pagination preferred over OFFSET for very large datasets

## Dependencies

### Blocked By
- **F-004** Record & RecordHeader Entities
- **F-006** Database Schema Mapping
- **F-007** Database Adapters
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-017** Metadata Format Plugin System (MetadataFormatRegistry)
- **F-018** Dublin Core Plugin (default format)
- **F-020** Resumption Tokens & Pagination
- **F-021** Selective Harvesting (from/until filtering)
- **F-022** Deleted Records Support (status="deleted" records)

### Blocks
- **F-037** Integration Testing

## Files

```
src/Protocol/Service/Handler/ListRecordsHandler.php
tests/Protocol/Service/Handler/ListRecordsHandlerTest.php
```

## Testing Requirements

- [ ] Valid ListRecords request returns full records with metadata
- [ ] Metadata serialized in requested format
- [ ] Filter by from date
- [ ] Filter by until date
- [ ] Filter by set
- [ ] Combined filters
- [ ] Deleted records with status="deleted" (no metadata body)
- [ ] noRecordsMatch for empty results
- [ ] Pagination with resumption tokens (multi-page harvest)
- [ ] Resumption token context preserved across pages
- [ ] badArgument for missing metadataPrefix
- [ ] cannotDisseminateFormat for unknown prefix
- [ ] badResumptionToken for expired token
- [ ] Page size configurable
- [ ] Response XML validates against XSD

## Notes

- ListRecords is the most resource-intensive verb — caching and performance tuning are critical.
- This is the core verb for full harvesting workflows.
