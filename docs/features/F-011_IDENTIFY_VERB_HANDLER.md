# Feature F-011: Identify Verb Handler

**Feature ID:** F-011  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `IdentifyHandler` service that processes the Identify verb and returns complete repository information in a valid OAI-PMH XML response. The Identify verb is the simplest verb and serves as the first end-to-end validation of the full request/response pipeline.

## User Stories

**US-011.1:** As a **harvester**, I want to call `verb=Identify` and receive the repository's name, base URL, admin email, protocol version, earliest datestamp, deleted record policy, and granularity so that I can configure my harvesting parameters.

## Acceptance Criteria

- [ ] Identify verb takes no arguments (any extra arguments → `badArgument`)
- [ ] Response includes all required elements:
  - `<repositoryName>`
  - `<baseURL>`
  - `<protocolVersion>2.0</protocolVersion>`
  - `<adminEmail>` (one or more)
  - `<earliestDatestamp>`
  - `<deletedRecord>` (no | transient | persistent)
  - `<granularity>` (YYYY-MM-DD | YYYY-MM-DDThh:mm:ssZ)
- [ ] Response includes optional elements when configured:
  - `<compression>` (gzip, deflate)
  - `<description>` (oai-identifier, eprints, friends, etc.)
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response is cacheable (Identify rarely changes)
- [ ] Response time < 100ms (performance target)
- [ ] PHPStan Level 8 passes

## Technical Design

### Handler Contract

```php
interface VerbHandlerInterface
{
    public function handle(OaiRequest $request): OaiResponse;
}
```

### Data Flow
1. Receive validated `OaiRequest` (verb=Identify)
2. Check cache → return cached response if hit
3. Load `RepositoryIdentity` aggregate
4. Build `OaiResponse` with Identify content
5. Cache response
6. Return response

## Dependencies

### Blocked By
- **F-003** Repository Identity (provides all Identify data)
- **F-008** OAI-PMH Request Parsing (provides validated OaiRequest)
- **F-009** OAI-PMH XML Response Assembly (builds response XML)
- **F-010** OAI-PMH Error Handling (badArgument for extra args)

### Blocks
- **F-037** Integration Testing (first verb to test end-to-end)

## Files

```
src/Protocol/Service/Handler/IdentifyHandler.php
tests/Protocol/Service/Handler/IdentifyHandlerTest.php
```

## Testing Requirements

- [ ] Valid Identify request returns complete repository info
- [ ] Identify with extra arguments returns badArgument error
- [ ] All required XML elements present in response
- [ ] Optional elements present when configured
- [ ] Response XML validates against OAI-PMH XSD
- [ ] Caching behavior verified

## Notes

- The Identify verb is the simplest verb and validates the full pipeline: HTTP → Middleware → Handler → RepositoryIdentity → OaiResponse → XML.
- This is recommended as the first verb to implement.
