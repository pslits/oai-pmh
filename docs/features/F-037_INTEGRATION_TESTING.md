# Feature F-037: Integration Testing & OAI-PMH Compliance

**Feature ID:** F-037  
**Priority:** MVP (MUST HAVE)  
**Phase:** 9 — Testing & Polish  
**Bounded Context:** Cross-cutting (all contexts)  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement comprehensive integration tests covering full round-trip OAI-PMH request/response cycles for all six verbs (Identify, ListMetadataFormats, ListSets, ListIdentifiers, ListRecords, GetRecord). All XML responses must pass XSD validation. Additionally, validate against the OAI-PMH Repository Validator tool.

## User Stories

**US-037.1:** As a **developer**, I want full end-to-end integration tests for every OAI-PMH verb so that I can verify the complete request → processing → XML response pipeline.

**US-037.2:** As a **harvester operator**, I want the server to pass the OAI-PMH Repository Validator so that I can trust the server's compliance with the protocol.

**US-037.3:** As a **developer**, I want performance baselines in my test suite so that regressions in response time are detected automatically.

## Acceptance Criteria

### Functional Tests
- [ ] Identify: full XML response with all required elements validated
- [ ] ListMetadataFormats: formats returned, XSD-valid XML
- [ ] ListSets: sets returned with hierarchy, XSD-valid XML
- [ ] ListIdentifiers: headers returned with resumption tokens, XSD-valid XML
- [ ] ListRecords: full records with metadata, XSD-valid XML
- [ ] GetRecord: single record with all content, XSD-valid XML
- [ ] Error responses: all 8 OAI-PMH error codes tested
- [ ] Resumption token flow: full pagination cycle tested
- [ ] Selective harvesting: from/until date filtering tested

### XSD Validation
- [ ] All responses validated against `OAI-PMH.xsd`
- [ ] `oai_dc` metadata validated against Dublin Core XSD
- [ ] Custom metadata format responses validated against their XSDs

### OAI-PMH Repository Validator
- [ ] Server passes the official OAI-PMH validator: http://validator.oaipmh.com/
- [ ] All protocol requirements satisfied

### Performance
- [ ] Identify response < 100ms baseline
- [ ] GetRecord response < 200ms baseline
- [ ] ListRecords (100 records) < 1s baseline
- [ ] Resumption token issuance and redemption < 500ms baseline

### Code Quality
- [ ] PHPStan Level 8 passes
- [ ] PSR-12 compliant
- [ ] Code coverage > 90% for Protocol and Repository contexts

## Dependencies

### Blocked By
- **F-011** through **F-016** (all six verb handlers)
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-018** Dublin Core Plugin
- **F-020** Resumption Tokens (pagination)
- **F-021** Selective Harvesting
- **F-022** Deleted Records Support
- **F-023** HTTP Entry Point & Middleware Pipeline

### Blocks
- **F-039** Composer Package & Release (tests must pass before release)

## Files

```
tests/Integration/Verb/IdentifyIntegrationTest.php
tests/Integration/Verb/ListMetadataFormatsIntegrationTest.php
tests/Integration/Verb/ListSetsIntegrationTest.php
tests/Integration/Verb/ListIdentifiersIntegrationTest.php
tests/Integration/Verb/ListRecordsIntegrationTest.php
tests/Integration/Verb/GetRecordIntegrationTest.php
tests/Integration/ErrorHandlingIntegrationTest.php
tests/Integration/ResumptionTokenIntegrationTest.php
tests/Integration/SelectiveHarvestingIntegrationTest.php
tests/Integration/XsdValidationTest.php
tests/Fixtures/oai-pmh.xsd
tests/Fixtures/oai_dc.xsd
tests/Fixtures/sample-responses/
```

## Testing Requirements

- [ ] Full request → response round-trip for all 6 verbs
- [ ] XSD validation of every response
- [ ] OAI-PMH error code tests (all 8 codes)
- [ ] Resumption token lifecycle
- [ ] Selective harvesting date filtering
- [ ] Deleted record responses
- [ ] Set hierarchy traversal
- [ ] Performance baselines as assertions
- [ ] At least one test using each database adapter (MySQL, PostgreSQL)

## Notes

- Use PHPUnit's `@group integration` annotation for separation from unit tests.
- OAI-PMH XSD: http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd
- OAI-PMH validator: http://validator.oaipmh.com/
- Performance baselines should be generous enough for CI environments but tight enough to catch regressions.
- Consider using Testcontainers (Docker) for MySQL/PostgreSQL in CI.
