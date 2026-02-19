# Feature F-006: Database Schema Mapping

**Feature ID:** F-006  
**Priority:** MVP (MUST HAVE)  
**Phase:** 2 — Repository Context: Identity & Core Entities  
**Bounded Context:** Repository  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `SchemaMapping` aggregate that translates YAML-based configuration into SQL queries. This is the highest-complexity component in the system — it bridges the gap between arbitrary database schemas and the OAI-PMH data model. It parses the `mapping` configuration, validates it against the actual database, builds parameterized SQL queries from filter criteria, and maps result rows to `Record` entities.

## User Stories

**US-006.1:** As a **repository administrator**, I want to define how my existing database maps to OAI-PMH records (identifier column, datestamp column, set table) in configuration, so that I don't need to modify my database schema.

**US-006.2:** As the **server application**, I want to generate safe, parameterized SQL from the schema mapping so that database queries are correct and injection-proof.

**US-006.3:** As a **developer integrating** with DSpace or EPrints, I want the mapping system to support views and multi-table joins so that complex database schemas can be accommodated.

## Acceptance Criteria

- [ ] Parses `mapping` configuration section successfully
- [ ] Validates that referenced table/column names exist in the database at startup
- [ ] Generates parameterized SQL queries (no string concatenation of user input)
- [ ] Supports mapping modes:
  - Single table: `record_table: records`
  - Database view: `record_table: v_oai_records`
  - Multi-table JOIN: `joins: [{table: sets, on: records.id = set_memberships.record_id}]`
- [ ] Builds filter queries for:
  - Date range: `datestamp BETWEEN ? AND ?`
  - Set membership: via JOIN condition
  - Deletion status
  - Identifier lookup (GetRecord)
- [ ] Pagination: `LIMIT :limit OFFSET :offset`
- [ ] Supports both MySQL and PostgreSQL SQL dialects
- [ ] Maps database result rows to `Record` entities via `RowMapper`
- [ ] `MappingValidationException` thrown when table/column does not exist
- [ ] Per-format metadata field mapping (e.g., `oai_dc.title → dc_title` column)
- [ ] PHPStan Level 8 passes

## Technical Design

### Mapping Configuration Example

```yaml
mapping:
  record_table: items
  identifier_field: item_id
  datestamp_field: last_modified
  deleted_field: is_deleted

  sets:
    table: collections
    spec_field: collection_code
    name_field: collection_name
    mapping_table: item_collections

  metadata_formats:
    oai_dc:
      fields:
        title: dc_title
        creator: dc_creator
        subject: dc_subject
```

### Components

| Component | Responsibility |
|-----------|----------------|
| `SchemaMapping` | Parses config, validates against DB, entry point |
| `QueryBuilder` | Builds parameterized SQL from mapping + filters |
| `RowMapper` | Transforms database rows to Record entities |

### SQL Generation Rules
- All queries use parameterized placeholders (PDO/DBAL bindings)
- Must use Doctrine DBAL platform abstraction for dialect differences
- Fetch `pageSize + 1` rows to detect if more pages exist

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (StorageAdapterInterface)
- **F-002** Configuration Management (reads `mapping` section)
- **F-004** Record & RecordHeader Entities (RowMapper produces Records)
- **F-005** Set Aggregate & Hierarchy (set mapping tables)

### Blocks
- **F-007** Database Adapters (QueryBuilder consumed by adapters)
- **F-011** Identify Verb Handler (earliestDatestamp query via mapping)
- **F-014** ListIdentifiers Verb Handler (query by mapping)
- **F-015** ListRecords Verb Handler (query by mapping)
- **F-016** GetRecord Verb Handler (identifier lookup via mapping)

## Files

```
src/Repository/Aggregate/SchemaMapping.php
src/Repository/Service/QueryBuilder.php
src/Repository/Service/RowMapper.php
src/Repository/Exception/MappingValidationException.php
tests/Repository/Aggregate/SchemaMappingTest.php
tests/Repository/Service/QueryBuilderTest.php
tests/Repository/Service/RowMapperTest.php
```

## Testing Requirements

- [ ] Parse valid mapping configuration
- [ ] Reject invalid configuration (missing required fields)
- [ ] Generate correct SELECT SQL for single table
- [ ] Generate correct SELECT SQL with JOINs
- [ ] Generate correct WHERE clause for date range filter
- [ ] Generate correct WHERE clause for set filter
- [ ] Generate correct WHERE clause for identifier lookup
- [ ] Parameterized queries (no raw user input in SQL)
- [ ] Pagination (LIMIT/OFFSET)
- [ ] Row mapping to Record entity
- [ ] MySQL and PostgreSQL dialect differences handled
- [ ] Validation against missing table/column names

## Notes

- This is the **highest-complexity** component. Consider a spike/prototype in Phase 0.
- Doctrine DBAL platform abstraction handles MySQL vs. PostgreSQL differences.
- `EXPLAIN` analysis should be documented for common query patterns.
