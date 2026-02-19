# Feature F-029: Record-Level Access Control

**Feature ID:** F-029  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement record-level access control that restricts access to sensitive metadata based on the authenticated user's identity. Anonymous users see only public records; authenticated users see public records plus those they are authorized to access. Restricted records are silently excluded from list results and return `idDoesNotExist` for GetRecord.

## User Stories

**US-029.1:** As a **repository administrator**, I want to mark certain records as restricted so that sensitive metadata is only accessible to authorized users.

**US-029.2:** As a **harvester with credentials**, I want to access restricted records that I'm authorized to view, in addition to public records.

## Acceptance Criteria

- [ ] Records can be marked as public or restricted in database
- [ ] `AccessControl` aggregate checks user identity + record access flags
- [ ] `RecordAccessFilter` service filters `RecordCollection` by access level
- [ ] Anonymous users → public records only
- [ ] Authenticated users → public + authorized restricted records
- [ ] GetRecord on unauthorized record → `idDoesNotExist` (not a distinct error)
- [ ] ListRecords/ListIdentifiers silently exclude unauthorized records
- [ ] Access control rules configurable
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-004** Record & RecordHeader Entities (access level property)
- **F-006** Database Schema Mapping (access level column mapping)
- **F-027** Authentication System (user identity for authorization)

### Blocks
- None (standalone security enhancement)

## Files

```
src/AccessControl/Aggregate/AccessControl.php
src/AccessControl/Service/RecordAccessFilter.php
tests/AccessControl/Aggregate/AccessControlTest.php
tests/AccessControl/Service/RecordAccessFilterTest.php
```

## Testing Requirements

- [ ] Anonymous user sees only public records
- [ ] Authenticated user sees public + authorized records
- [ ] GetRecord on restricted record → idDoesNotExist for anonymous
- [ ] GetRecord on authorized restricted record → success for authenticated user
- [ ] ListRecords excludes restricted records for anonymous
- [ ] ListRecords includes authorized restricted records for authenticated user
- [ ] Access filter applied post-query (dual-touch pattern)

## Notes

- Access control is applied as a post-query filter, not in SQL — this ensures clean separation between Repository and Access Control contexts.
- The dual-touch pattern: Access Control runs both before (auth, rate limit) and after (record filtering) the main processing pipeline.
