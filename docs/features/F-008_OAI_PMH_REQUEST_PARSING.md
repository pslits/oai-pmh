# Feature F-008: OAI-PMH Request Parsing & Validation

**Feature ID:** F-008  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Request Parsing & Response Assembly  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Complete the `OaiRequest` aggregate that parses and validates incoming OAI-PMH requests from HTTP query strings. The aggregate validates the verb, required/optional arguments per verb, argument exclusivity rules, date granularity matching, and produces typed, validated request objects or appropriate OAI-PMH error events.

## User Stories

**US-008.1:** As a **harvester**, I want clear error messages when I send invalid OAI-PMH requests so that I can fix my harvesting configuration.

**US-008.2:** As the **server**, I want to validate all incoming requests against the OAI-PMH 2.0 specification so that only valid requests reach the verb handlers.

## Acceptance Criteria

- [ ] Parses HTTP query string into typed OaiRequest object
- [ ] Validates required arguments per verb:

| Verb | Required | Optional | Exclusive |
|------|----------|----------|-----------|
| Identify | — | — | — |
| ListMetadataFormats | — | identifier | — |
| ListSets | — | resumptionToken | yes |
| ListIdentifiers | metadataPrefix | from, until, set, resumptionToken | resumptionToken |
| ListRecords | metadataPrefix | from, until, set, resumptionToken | resumptionToken |
| GetRecord | identifier, metadataPrefix | — | — |

- [ ] Detects `badVerb` — unknown or missing verb
- [ ] Detects `badArgument`:
  - Missing required arguments
  - Illegal (extra) arguments
  - Repeated arguments
  - Mutually exclusive arguments (resumptionToken with others)
- [ ] Validates `from`/`until` granularity matches repository granularity
- [ ] Validates `from` is not later than `until`
- [ ] Multiple errors can be reported in a single response
- [ ] Request is immutable after creation (value object semantics)
- [ ] PHPStan Level 8 passes

## Technical Design

### Error Detection Priority
1. `badVerb` — check first (unknown/missing verb)
2. `badArgument` — check arguments for the detected verb
3. Granularity/date validation — check date format and range

### OAI-PMH Error Response Rules
- Multiple `<error>` elements may appear in one response
- No record content when errors are present
- Request element still echoes back legal arguments

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (OaiVerb Value Object already exists)
- **F-002** Configuration Management (granularity setting for date validation)

### Blocks
- **F-009** OAI-PMH XML Response Assembly (OaiRequest is echoed in response)
- **F-010** OAI-PMH Error Handling (errors detected during parsing)
- **F-011** through **F-016** All Verb Handlers (consume validated OaiRequest)
- **F-023** HTTP Entry Point & Middleware Pipeline (OaiRequest created in middleware)

## Files

```
src/Protocol/Aggregate/OaiRequest.php
src/Protocol/Service/RequestDispatcher.php
tests/Protocol/Aggregate/OaiRequestTest.php
tests/Protocol/Service/RequestDispatcherTest.php
```

## Testing Requirements

- [ ] Valid request for each of the 6 verbs
- [ ] Missing verb → badVerb error
- [ ] Unknown verb → badVerb error
- [ ] Missing required argument → badArgument error
- [ ] Extra/illegal argument → badArgument error
- [ ] Repeated argument → badArgument error
- [ ] resumptionToken with other arguments → badArgument error
- [ ] from > until → badArgument error
- [ ] Granularity mismatch → badArgument error
- [ ] Multiple simultaneous errors detected
- [ ] Immutability after construction

## Notes

- A partial implementation exists in `src/Domain/Aggregate/OaiRequest.php` — to be completed and moved to `src/Protocol/Aggregate/`.
- The `RequestDispatcher` routes validated requests to the appropriate `VerbHandlerInterface`.
