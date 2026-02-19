# Feature F-005: Set Aggregate & Hierarchy

**Feature ID:** F-005  
**Priority:** MVP (MUST HAVE)  
**Phase:** 2 — Repository Context: Identity & Core Entities  
**Bounded Context:** Repository  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `Set` aggregate and `SetCollection` for representing OAI-PMH organizational sets. Sets allow records to be grouped into collections or categories that harvesters can selectively harvest. Sets support hierarchical organization using colon-separated `setSpec` values (e.g., `dataset:climate:temperature`).

## User Stories

**US-005.1:** As a **harvester**, I want to list all available sets so that I can choose which subsets of records to harvest.

**US-005.2:** As a **harvester**, I want to filter ListRecords/ListIdentifiers by set so that I only retrieve records from a specific collection.

**US-005.3:** As a **repository administrator**, I want to define sets that map to my database's organizational structure (collections, departments, categories).

## Acceptance Criteria

- [ ] `Set` aggregate with properties:
  - `setSpec: SetSpec` (unique identifier, e.g., "biology")
  - `setName: string` (human-readable name)
  - `setDescriptions: DescriptionCollection` (optional)
- [ ] `SetCollection` implements `Countable` and `IteratorAggregate`
- [ ] Hierarchical sets supported (colon separator: `parent:child:grandchild`)
- [ ] Sets configured via `mapping.sets` configuration section
- [ ] Records can belong to zero or more sets
- [ ] ListSets verb returns all sets with spec, name, and optional description
- [ ] ListRecords/ListIdentifiers support `set` parameter for selective harvesting
- [ ] `noSetHierarchy` error when sets are not configured
- [ ] PHPStan Level 8 passes

## Technical Design

### OAI-PMH Specification Reference
Per OAI-PMH 2.0 Section 2.6 (Set), sets are optional organizational groupings:
- `setSpec` — unique colon-separated identifier
- `setName` — human-readable name
- `setDescription` — optional, may contain Dublin Core or custom XML

### Database Mapping
Sets are mapped from the database through configuration:
```yaml
mapping:
  sets:
    table: collections
    spec_field: collection_code
    name_field: collection_name
    mapping_table: item_collections  # junction table for record-to-set
```

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (SetSpec Value Object already exists)
- **F-002** Configuration Management (reads `mapping.sets` section)

### Blocks
- **F-006** Database Schema Mapping (set table/junction table mapping)
- **F-013** ListSets Verb Handler (lists all sets)
- **F-014** ListIdentifiers Verb Handler (set filter parameter)
- **F-015** ListRecords Verb Handler (set filter parameter)

## Files

```
src/Repository/Aggregate/Set.php
src/Repository/Aggregate/SetCollection.php
tests/Repository/Aggregate/SetTest.php
tests/Repository/Aggregate/SetCollectionTest.php
```

## Testing Requirements

- [ ] Set construction with valid setSpec and setName
- [ ] Set with optional descriptions
- [ ] SetCollection iteration and counting
- [ ] Hierarchical setSpec validation (colon-separated)
- [ ] Empty SetCollection (no sets configured)
- [ ] SetSpec uniqueness within collection

## Notes

- The existing `SetSpec` Value Object handles validation of the setSpec format.
- Description elements within sets may use Dublin Core or custom XML schemas.
