# Feature F-013: ListSets Verb Handler

**Feature ID:** F-013  
**Priority:** MVP (MUST HAVE)  
**Phase:** 3 — Protocol Context: Verb Handlers  
**Bounded Context:** Protocol  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `ListSetsHandler` that returns the set structure of the repository. Sets are optional in OAI-PMH — if the repository does not support sets, the handler returns a `noSetHierarchy` error. ListSets supports resumption tokens for repositories with many sets.

## User Stories

**US-013.1:** As a **harvester**, I want to list all available sets so that I can selectively harvest specific collections.

**US-013.2:** As a **harvester**, I want to paginate through large set lists using resumption tokens.

## Acceptance Criteria

- [ ] Returns all sets with `<setSpec>`, `<setName>`, and optional `<setDescription>`
- [ ] Returns `noSetHierarchy` error when sets are not configured
- [ ] Supports `resumptionToken` argument for pagination (when set count exceeds page size)
- [ ] `resumptionToken` is an exclusive argument
- [ ] Returns `badArgument` for illegal arguments
- [ ] Returns `badResumptionToken` for invalid/expired tokens
- [ ] Hierarchical sets displayed correctly (colon-separated)
- [ ] Response validates against OAI-PMH 2.0 XSD
- [ ] Response time < 500ms
- [ ] Response is cacheable
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-005** Set Aggregate & Hierarchy
- **F-007** Database Adapters (set retrieval)
- **F-008** OAI-PMH Request Parsing
- **F-009** OAI-PMH XML Response Assembly
- **F-010** OAI-PMH Error Handling
- **F-020** Resumption Tokens & Pagination (if set count is large)

### Blocks
- **F-037** Integration Testing

## Files

```
src/Protocol/Service/Handler/ListSetsHandler.php
tests/Protocol/Service/Handler/ListSetsHandlerTest.php
```

## Testing Requirements

- [ ] List all sets successfully
- [ ] noSetHierarchy error when sets not configured
- [ ] Pagination with resumption tokens
- [ ] badArgument for illegal arguments
- [ ] badResumptionToken for invalid tokens
- [ ] Hierarchical set display (parent:child)
- [ ] Set descriptions included when present
- [ ] Response XML validates against XSD

## Notes

- Sets are optional per OAI-PMH spec — the server must handle the case where no sets are defined.
