# Feature F-003: Repository Identity

**Feature ID:** F-003  
**Priority:** MVP (MUST HAVE)  
**Phase:** 2 — Repository Context: Identity & Core Entities  
**Bounded Context:** Repository  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Complete the `RepositoryIdentity` aggregate that holds all repository information required for the OAI-PMH Identify verb response. This includes repository name, base URL, protocol version, admin emails, earliest datestamp, deleted record policy, granularity, optional compression support, and optional description elements. The aggregate loads its data from configuration and computes `earliestDatestamp` by querying the database.

## User Stories

**US-003.1:** As a **harvester**, I want to call the Identify verb and receive complete repository information so that I can configure my harvesting strategy (date granularity, deleted record policy, etc.).

**US-003.2:** As a **repository administrator**, I want to configure my repository's identity (name, email, URL) in a YAML file so that the Identify response reflects my institution's details.

## Acceptance Criteria

- [ ] `RepositoryIdentity` aggregate exposes all Identify response properties:
  - `repositoryName: RepositoryName`
  - `baseUrl: BaseURL`
  - `protocolVersion: ProtocolVersion` (always "2.0")
  - `adminEmails: EmailCollection` (at least one)
  - `earliestDatestamp: UTCdatetime`
  - `deletedRecord: DeletedRecord`
  - `granularity: Granularity`
  - `compressions: string[]` (optional: gzip, deflate)
  - `descriptions: DescriptionCollection` (optional)
- [ ] Loads from `Configuration → repository` section
- [ ] Computes `earliestDatestamp` by querying database (lazy, cached)
- [ ] Uses existing Value Objects (no primitive types for domain concepts)
- [ ] Serializable to the Identify XML response format
- [ ] Immutable after construction
- [ ] PHPStan Level 8 passes

## Technical Design

### OAI-PMH Specification Reference
Per OAI-PMH 2.0 Section 4.2 (Identify), the Identify response must include:
- `repositoryName` (required)
- `baseURL` (required)
- `protocolVersion` = "2.0" (required)
- `earliestDatestamp` (required)
- `deletedRecord` (required): no | transient | persistent
- `granularity` (required): YYYY-MM-DD | YYYY-MM-DDThh:mm:ssZ
- `adminEmail` (required, one or more)
- `compression` (optional)
- `description` (optional, repeatable)

### Earliest Datestamp

The `earliestDatestamp` is the oldest `datestamp` in the repository. It must be queried from the database via `StorageAdapterInterface` and cached to avoid repeated queries.

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (interfaces, Value Objects)
- **F-002** Configuration Management (reads `repository` section)

### Blocks
- **F-011** Identify Verb Handler (uses this aggregate to build response)

## Files

```
src/Repository/Aggregate/RepositoryIdentity.php
tests/Repository/Aggregate/RepositoryIdentityTest.php
```

## Testing Requirements

- [ ] Construction with valid configuration data succeeds
- [ ] All properties accessible via getters
- [ ] Uses existing Value Objects (BaseURL, RepositoryName, etc.)
- [ ] earliestDatestamp lazy-loading from database
- [ ] Immutability verified
- [ ] Missing required configuration fields throw exception

## Notes

- A partial implementation exists in `src/Domain/Aggregate/RepositoryIdentity.php` — this should be completed and moved to `src/Repository/Aggregate/`.
- `protocolVersion` is always "2.0" — hardcoded, not configurable.
