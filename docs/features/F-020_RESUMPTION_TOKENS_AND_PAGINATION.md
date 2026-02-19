# Feature F-020: Resumption Tokens & Pagination

**Feature ID:** F-020  
**Priority:** MVP (MUST HAVE)  
**Phase:** 5 — Flow Control Context  
**Bounded Context:** Flow Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `ResumptionToken` aggregate and `Paginator` service that manage pagination of large result sets across multiple requests. Resumption tokens encapsulate the complete query context (metadataPrefix, from, until, set, cursor position) so that subsequent pages can be retrieved without re-specifying the original query parameters. The token format is configurable (JWT for stateless, Redis for stateful).

## User Stories

**US-020.1:** As a **harvester**, I want to paginate through large result sets using resumption tokens so that I can harvest millions of records without timeouts or memory issues.

**US-020.2:** As a **repository administrator**, I want to configure page size and token lifetime so that I can balance performance and resource usage.

**US-020.3:** As a **harvester**, I want expired tokens to return a clear `badResumptionToken` error so that I know to restart my harvest.

## Acceptance Criteria

- [ ] `ResumptionToken` aggregate encapsulates:
  - `tokenValue: string` (opaque token string)
  - `metadataPrefix: MetadataPrefix`
  - `from: ?UTCdatetime`
  - `until: ?UTCdatetime`
  - `set: ?SetSpec`
  - `cursor: int`
  - `pageSize: int`
  - `completeListSize: ?int`
  - `expiresAt: DateTimeImmutable`
- [ ] Token creation with all query context
- [ ] Token decoding restores full query context
- [ ] Token validation checks expiration and integrity
- [ ] Empty token creation signals last page (no value, but `completeListSize` and `cursor` present)
- [ ] Token lifetime configurable (default: 24 hours)
- [ ] Page size configurable (default: 100 records)
- [ ] `Paginator` service:
  - Calculates page offset from cursor
  - Determines if more pages exist (fetch pageSize+1 strategy)
  - Decides when resumption token is needed
- [ ] Token storage adapters:
  - JWT (HMAC-SHA256, stateless) — primary
  - Redis (UUID with stored state) — fallback
- [ ] `badResumptionToken` error for invalid/expired tokens
- [ ] `resumptionToken` is exclusive argument (cannot combine with other params)
- [ ] `completeListSize` and `cursor` returned as token attributes
- [ ] Supported in ListRecords, ListIdentifiers, and ListSets verbs
- [ ] PHPStan Level 8 passes

## Technical Design

### Token Format (per ADR-001)
**Primary: JWT (stateless)**
- Claims contain all query context (prefix, from, until, set, cursor, pageSize)
- Signed with HMAC-SHA256 (secret from configuration)
- Expiration encoded in JWT `exp` claim
- No storage required for validation

**Fallback: Redis (stateful)**
- Random UUID as token value
- State stored in Redis with TTL
- Required for repos without JWT support

### Pagination Strategy
- Fetch `pageSize + 1` rows to detect if more pages exist (avoids expensive COUNT query)
- `completeListSize` may be estimated or null for very large datasets
- Cursor-based pagination preferred over OFFSET for large datasets (post-MVP)

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (TokenStorageInterface)
- **F-002** Configuration Management (reads `resumption` section)

### Blocks
- **F-013** ListSets Verb Handler (pagination support)
- **F-014** ListIdentifiers Verb Handler (pagination support)
- **F-015** ListRecords Verb Handler (pagination support)

## Files

```
src/FlowControl/Aggregate/ResumptionToken.php
src/FlowControl/Service/Paginator.php
src/FlowControl/Contract/TokenStorageInterface.php
src/Infrastructure/FlowControl/JwtTokenAdapter.php
src/Infrastructure/FlowControl/RedisTokenAdapter.php
tests/FlowControl/Aggregate/ResumptionTokenTest.php
tests/FlowControl/Service/PaginatorTest.php
tests/Infrastructure/FlowControl/JwtTokenAdapterTest.php
tests/Infrastructure/FlowControl/RedisTokenAdapterTest.php
```

## Testing Requirements

- [ ] Create token with full query context
- [ ] Decode token restores exact context
- [ ] Expired token detected
- [ ] Invalid/tampered token detected
- [ ] Empty token creation (last page)
- [ ] Page offset calculation
- [ ] hasMorePages detection (pageSize+1 strategy)
- [ ] JWT token roundtrip (create → encode → decode → validate)
- [ ] Redis token roundtrip
- [ ] Token lifetime configuration respected
- [ ] Page size configuration respected

## Notes

- JWT tokens are preferred for scalability (no server-side state).
- Redis tokens are the fallback for shared hosting without JWT library.
- Token format decision should be documented in ADR-001.
