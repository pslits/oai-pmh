# Feature F-021: Selective Harvesting (Date Filtering)

**Feature ID:** F-021  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context (cross-cutting with Repository)  
**Bounded Context:** Protocol / Repository  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement date-based selective harvesting using `from` and `until` parameters in ListRecords and ListIdentifiers verbs. Dates must conform to the repository's declared granularity (day-level or second-level). This enables incremental harvesting — harvesters can request only records modified since their last harvest.

## User Stories

**US-021.1:** As a **harvester**, I want to specify `from` and `until` dates so that I only retrieve records modified within a specific time range, enabling efficient incremental harvesting.

**US-021.2:** As the **server**, I want to validate date arguments against the repository's granularity setting so that invalid date formats are rejected with clear errors.

## Acceptance Criteria

- [ ] `from` parameter: harvest records modified on or after this date
- [ ] `until` parameter: harvest records modified on or before this date
- [ ] Both `from` and `until` are optional
- [ ] Dates are in UTC (ISO 8601 format)
- [ ] Granularity enforced per configuration:
  - Day-level: `YYYY-MM-DD`
  - Second-level: `YYYY-MM-DDThh:mm:ssZ`
- [ ] Granularity mismatch → `badArgument` error
- [ ] `from` later than `until` → `badArgument` error
- [ ] `noRecordsMatch` when no records in date range
- [ ] Edge cases handled:
  - `from` only (no upper bound)
  - `until` only (no lower bound)
  - Same date for `from` and `until` (single day/second)
  - Future dates (valid but may return empty results)
- [ ] Datestamp stored and returned for all records
- [ ] Date range translated to SQL `BETWEEN` clause via SchemaMapping
- [ ] PHPStan Level 8 passes

## Technical Design

### Granularity Mapping
| Config Granularity | Date Format | SQL Comparison |
|--------------------|-------------|----------------|
| `YYYY-MM-DD` | `2026-01-15` | `DATE(datestamp) BETWEEN ? AND ?` |
| `YYYY-MM-DDThh:mm:ssZ` | `2026-01-15T10:30:00Z` | `datestamp BETWEEN ? AND ?` |

### Inclusive Ranges
Per OAI-PMH spec, both `from` and `until` are inclusive bounds.

## Dependencies

### Blocked By
- **F-002** Configuration Management (granularity setting)
- **F-006** Database Schema Mapping (date range SQL generation)
- **F-008** OAI-PMH Request Parsing (from/until parameter parsing)

### Blocks
- **F-014** ListIdentifiers Verb Handler (from/until support)
- **F-015** ListRecords Verb Handler (from/until support)

## Files

```
(Implemented within existing components — no separate files)
src/Protocol/Aggregate/OaiRequest.php       (date validation logic)
src/Repository/Service/QueryBuilder.php     (date range SQL clause)
```

## Testing Requirements

- [ ] Filter by `from` date returns correct records
- [ ] Filter by `until` date returns correct records
- [ ] Combined `from` AND `until` returns range
- [ ] Granularity mismatch rejected (day-level repo gets second-level date)
- [ ] `from` > `until` rejected
- [ ] `from` only (open-ended upper bound)
- [ ] `until` only (open-ended lower bound)
- [ ] noRecordsMatch for date range with no records
- [ ] Inclusive bounds verified (boundary records included)

## Notes

- Selective harvesting is the primary mechanism for incremental harvesting workflows.
- The `UTCdatetime` Value Object (already implemented) handles date parsing and validation.
