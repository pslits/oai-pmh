# Feature F-009: OAI-PMH XML Response Assembly

**Feature ID:** F-009  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Request Parsing & Response Assembly  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `OaiResponse` aggregate and `XmlSerializer` service that construct well-formed OAI-PMH 2.0 XML response envelopes. The response builder provides a fluent API for setting verb-specific content (Identify, ListRecords, etc.), adding error elements, and serializing to a valid XML string with correct namespaces, encoding, and structure.

## User Stories

**US-009.1:** As a **harvester**, I want all OAI-PMH responses to be well-formed XML that validates against the OAI-PMH 2.0 XSD so that my parsing tools work reliably.

**US-009.2:** As the **server**, I want a clean API for building responses per verb so that each verb handler doesn't need to handle XML construction directly.

## Acceptance Criteria

- [ ] `OaiResponse` provides builder API:
  - `createEnvelope(DateTimeImmutable, OaiRequest)`
  - `setIdentifyContent(RepositoryIdentity)`
  - `setListRecordsContent(RecordCollection, ?ResumptionToken)`
  - `setListIdentifiersContent(RecordCollection, ?ResumptionToken)`
  - `setListSetsContent(SetCollection, ?ResumptionToken)`
  - `setListMetadataFormatsContent(array)`
  - `setGetRecordContent(Record)`
  - `addError(OaiErrorCode, string)`
  - `serialize(): string`
- [ ] `responseDate` always in UTC (ISO 8601 with `Z` suffix)
- [ ] Request element echoes back verb and all legal arguments
- [ ] XML namespaces correctly declared (`http://www.openarchives.org/OAI/2.0/`)
- [ ] `xsi:schemaLocation` points to official XSD
- [ ] UTF-8 encoding enforced (`<?xml version="1.0" encoding="UTF-8"?>`)
- [ ] Multiple `<error>` elements supported in a single response
- [ ] No record content when errors are present
- [ ] Only one verb content section per response
- [ ] Output validates against OAI-PMH 2.0 XSD schema
- [ ] PHPStan Level 8 passes

## Technical Design

### XML Generation Strategy
Per ADR-006: Use `DOMDocument` for correctness and namespace handling. `XMLWriter` as alternative for streaming large responses (post-MVP optimization).

### Response Structure
```xml
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>2026-02-18T12:00:00Z</responseDate>
  <request verb="ListRecords" metadataPrefix="oai_dc">https://example.org/oai</request>
  <ListRecords>
    <!-- verb-specific content -->
  </ListRecords>
</OAI-PMH>
```

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts
- **F-008** OAI-PMH Request Parsing (OaiRequest echoed in response)
- **F-010** OAI-PMH Error Handling (OaiErrorCode for error elements)

### Blocks
- **F-011** through **F-016** All Verb Handlers (produce OaiResponse)
- **F-037** Integration Testing (validates XML output against XSD)

## Files

```
src/Protocol/Aggregate/OaiResponse.php
src/Protocol/Service/XmlSerializer.php
tests/Protocol/Aggregate/OaiResponseTest.php
tests/Protocol/Service/XmlSerializerTest.php
```

## Testing Requirements

- [ ] Create envelope with correct responseDate and request echo
- [ ] Set content for each verb type
- [ ] Add single error element
- [ ] Add multiple error elements
- [ ] Error response has no record content
- [ ] Serialized XML is well-formed
- [ ] Serialized XML validates against OAI-PMH 2.0 XSD
- [ ] UTF-8 encoding declaration present
- [ ] Namespace declarations correct
- [ ] Resumption token element included when provided

## Notes

- The XSD schema file should be included in the test fixtures for automated XML validation.
- Large response streaming via XMLWriter is a post-MVP performance optimization.
