# Feature F-022: Deleted Records Support

**Feature ID:** F-022  
**Priority:** MVP (MUST HAVE)  
**Phase:** 2 — Repository Context (cross-cutting with Protocol)  
**Bounded Context:** Repository / Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement support for tracking and returning deleted records according to the repository's configured deletion policy (`no`, `transient`, or `persistent`). When a repository supports deleted records, deleted records appear in ListIdentifiers/ListRecords with `status="deleted"` and are returned by GetRecord with header-only content (no metadata body).

## User Stories

**US-022.1:** As a **harvester**, I want to know when records have been deleted from a repository so that I can remove them from my index.

**US-022.2:** As a **repository administrator**, I want to configure my deleted record policy so that my repository correctly indicates how it handles deletions.

## Acceptance Criteria

- [ ] Configurable `deletedRecord` policy: `no` | `transient` | `persistent`
- [ ] Identify response includes correct `<deletedRecord>` value
- [ ] When policy is `transient` or `persistent`:
  - Deleted records appear in ListIdentifiers with `status="deleted"` attribute
  - Deleted records appear in ListRecords with `status="deleted"` (header only, no metadata)
  - GetRecord for a deleted record returns the deleted record header
  - Deleted records include datestamp of deletion
- [ ] When policy is `no`:
  - Deleted records do not appear in list results
  - GetRecord for a deleted identifier → `idDoesNotExist`
- [ ] Database schema supports deletion status column (`deleted_field` in mapping config)
- [ ] Database schema supports deletion datestamp
- [ ] PHPStan Level 8 passes

## Technical Design

### OAI-PMH Specification Reference
Per OAI-PMH 2.0 Section 3.5 (Deleted Records):
- **no**: Repository does not maintain information about deletions
- **transient**: Repository maintains deletion info but not persistently/completely
- **persistent**: Repository maintains complete deletion info with no time limit

### Record XML for Deleted Record
```xml
<record>
  <header status="deleted">
    <identifier>oai:example.org:12345</identifier>
    <datestamp>2026-02-15T10:00:00Z</datestamp>
    <setSpec>dataset:climate</setSpec>
  </header>
</record>
```

## Dependencies

### Blocked By
- **F-002** Configuration Management (deletedRecord policy setting)
- **F-004** Record & RecordHeader Entities (isDeleted flag)
- **F-006** Database Schema Mapping (deleted_field mapping)

### Blocks
- **F-011** Identify Verb Handler (deletedRecord in Identify response)
- **F-014** ListIdentifiers Verb Handler (status="deleted" records)
- **F-015** ListRecords Verb Handler (status="deleted" records)
- **F-016** GetRecord Verb Handler (deleted record retrieval)

## Files

```
(Implemented within existing components)
src/Repository/Entity/Record.php              (isDeleted property)
src/Repository/Service/QueryBuilder.php       (deletion status in queries)
src/Protocol/Service/XmlSerializer.php        (status="deleted" attribute)
```

## Testing Requirements

- [ ] Policy "persistent": deleted records in ListRecords with status="deleted"
- [ ] Policy "persistent": deleted records in ListIdentifiers
- [ ] Policy "persistent": GetRecord returns deleted record header
- [ ] Policy "transient": same behavior as persistent
- [ ] Policy "no": deleted records excluded from lists
- [ ] Policy "no": GetRecord for deleted → idDoesNotExist
- [ ] Deletion datestamp preserved
- [ ] Deleted records have no metadata body
- [ ] Identify response includes correct deletedRecord value

## Notes

- The `DeletedRecord` Value Object (already implemented) validates the policy value.
- Deletion tracking typically uses a boolean/flag column in the database (`is_deleted`).
