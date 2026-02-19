# Feature F-007: Database Adapters (MySQL & PostgreSQL)

**Feature ID:** F-007  
**Priority:** MVP (MUST HAVE)  
**Phase:** 8 — Infrastructure Adapters  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement concrete database adapter classes that fulfill the `StorageAdapterInterface` contract. The primary adapter uses Doctrine DBAL for query building and driver abstraction across MySQL 5.7+ and PostgreSQL 10+. A lightweight PDO adapter is provided as an alternative for simpler deployments. Both adapters execute queries built by the `QueryBuilder` and return mapped `Record` entities.

## User Stories

**US-007.1:** As a **repository administrator**, I want to connect the OAI-PMH server to my existing MySQL database so that my records are exposed for harvesting without migrating data.

**US-007.2:** As a **repository administrator**, I want PostgreSQL support so that I can use my preferred database platform.

**US-007.3:** As a **developer**, I want database connection errors to be handled gracefully (retry with backoff, HTTP 503) so that temporary outages don't crash the server.

## Acceptance Criteria

- [ ] `DoctrineDbalAdapter` implements `StorageAdapterInterface`
- [ ] `PdoAdapter` implements `StorageAdapterInterface` (lightweight alternative)
- [ ] MySQL 5.7+ support verified
- [ ] PostgreSQL 10+ support verified
- [ ] Database connection configuration from `database` config section
- [ ] Connection error handling with automatic retry (exponential backoff)
- [ ] Query timeout configuration (configurable, default: 30s)
- [ ] Connection pooling support via DBAL
- [ ] Database-specific query optimizations (indexes on identifier, datestamp, setSpec)
- [ ] Parameterized queries throughout (no SQL injection)
- [ ] Graceful degradation: database unavailable → HTTP 503 Service Unavailable
- [ ] PHPStan Level 8 passes

## Technical Design

### Adapter Interface Contract

```php
interface StorageAdapterInterface
{
    public function getRecord(RecordIdentifier $identifier): ?Record;
    public function listRecords(array $criteria, int $limit, int $offset): RecordCollection;
    public function listIdentifiers(array $criteria, int $limit, int $offset): RecordCollection;
    public function listSets(): SetCollection;
    public function getEarliestDatestamp(): UTCdatetime;
    public function countRecords(array $criteria): ?int;
}
```

### Connection Resilience
- Retry up to 3 times with exponential backoff (1s, 2s, 4s)
- Circuit breaker pattern (optional, post-MVP)
- Cache failures: degrade gracefully, bypass cache, don't fail request

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (StorageAdapterInterface)
- **F-002** Configuration Management (reads `database` section)
- **F-004** Record & RecordHeader Entities (return types)
- **F-005** Set Aggregate & Hierarchy (SetCollection return type)
- **F-006** Database Schema Mapping (SQL query generation)

### Blocks
- **F-003** Repository Identity (earliestDatestamp query)
- **F-011** Identify Verb Handler (via RepositoryIdentity)
- **F-014** ListIdentifiers Verb Handler (query execution)
- **F-015** ListRecords Verb Handler (query execution)
- **F-016** GetRecord Verb Handler (record retrieval)
- **F-013** ListSets Verb Handler (set retrieval)

## Files

```
src/Infrastructure/Database/DoctrineDbalAdapter.php
src/Infrastructure/Database/PdoAdapter.php
tests/Infrastructure/Database/DoctrineDbalAdapterTest.php
tests/Infrastructure/Database/PdoAdapterTest.php
```

## Testing Requirements

- [ ] DoctrineDbalAdapter connects to MySQL test database
- [ ] DoctrineDbalAdapter connects to PostgreSQL test database
- [ ] Record retrieval by identifier
- [ ] Record listing with date range filter
- [ ] Record listing with set filter
- [ ] Earliest datestamp query
- [ ] Set listing
- [ ] Connection failure handling (retry, exception)
- [ ] Query timeout handling
- [ ] Parameterized queries verified (no injection)

## Notes

- Integration tests require test database fixtures (`tests/Fixtures/database.sql`).
- Both MySQL and PostgreSQL should be in the CI test matrix.
- `composer require doctrine/dbal` as a dependency.
