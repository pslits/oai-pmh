# Feature F-004: Record & RecordHeader Entities

**Feature ID:** F-004  
**Priority:** MVP (MUST HAVE)  
**Phase:** 2 — Repository Context: Identity & Core Entities  
**Bounded Context:** Repository  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `Record` entity and `RecordHeader` value object that represent OAI-PMH metadata records. A Record contains an identifier, datestamp, set memberships, deletion status, and optionally the raw metadata XML. The RecordHeader is the lightweight subset used by the ListIdentifiers verb (no metadata body). A `RecordCollection` aggregate provides type-safe, paginated collections of records.

## User Stories

**US-004.1:** As a **harvester**, I want to retrieve records with complete headers (identifier, datestamp, sets) and metadata so that I can ingest them into my system.

**US-004.2:** As the **ListIdentifiers verb handler**, I want access to record headers only (without loading metadata) so that responses are fast and lightweight.

**US-004.3:** As the **server application**, I want records to lazy-load their metadata so that memory is not wasted when only headers are needed.

## Acceptance Criteria

- [ ] `Record` entity with properties:
  - `identifier: RecordIdentifier`
  - `datestamp: UTCdatetime`
  - `setSpecs: SetSpec[]` (zero or more)
  - `isDeleted: bool`
  - `metadata: ?string` (raw XML fragment, null for deleted records)
- [ ] `RecordHeader` with properties:
  - `identifier: RecordIdentifier`
  - `datestamp: UTCdatetime`
  - `setSpecs: SetSpec[]`
  - `isDeleted: bool`
- [ ] `RecordCollection` aggregate with properties:
  - `items: Record[]` (current page)
  - `totalCount: ?int` (completeListSize, may be null)
  - `cursor: int` (current offset position)
  - `hasMorePages: bool`
- [ ] `RecordCollection` implements `Countable` and `IteratorAggregate`
- [ ] Deleted records carry header only — `metadata` is null
- [ ] Records belong to zero or more sets
- [ ] Metadata is lazy-loaded (only populated when serialization is requested)
- [ ] Record uses existing Value Objects (RecordIdentifier, UTCdatetime, SetSpec)
- [ ] PHPStan Level 8 passes

## Technical Design

### Domain Rules
- Every record has a unique `identifier` (typically OAI format: `oai:domain:id`)
- Every record has a `datestamp` reflecting last modification (UTC)
- Deleted records retain header but have null metadata
- Record metadata is a raw XML fragment string — serialization is handled by the Metadata Serialization context

### RecordCollection Pagination
The collection wraps a single page of results plus metadata for constructing resumption tokens:
- `totalCount` may be null if estimation is disabled (performance: avoids COUNT(*) on millions of rows)
- `hasMorePages` determined by fetching `pageSize + 1` rows

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (RecordInterface, Value Objects)

### Blocks
- **F-006** Database Schema Mapping (maps DB rows to Record entities)
- **F-014** ListIdentifiers Verb Handler (uses RecordHeader)
- **F-015** ListRecords Verb Handler (uses Record + RecordCollection)
- **F-016** GetRecord Verb Handler (uses Record)
- **F-020** Resumption Tokens & Pagination (uses RecordCollection pagination metadata)
- **F-022** Deleted Records Support (uses isDeleted flag)

## Files

```
src/Repository/Entity/Record.php
src/Repository/Entity/RecordHeader.php
src/Repository/Aggregate/RecordCollection.php
tests/Repository/Entity/RecordTest.php
tests/Repository/Entity/RecordHeaderTest.php
tests/Repository/Aggregate/RecordCollectionTest.php
```

## Testing Requirements

- [ ] Record construction with valid data
- [ ] Record with deleted status has null metadata
- [ ] Record with zero sets
- [ ] Record with multiple sets
- [ ] RecordHeader contains header-only data
- [ ] RecordCollection iteration and counting
- [ ] RecordCollection pagination metadata (totalCount, cursor, hasMorePages)
- [ ] Immutability of Record and RecordHeader

## Notes

- `Record` implements `RecordInterface` from `OaiPmh\Repository\Contract`.
- Access level (public/restricted) may be added as a property for F-029 (Record-Level Access Control).
