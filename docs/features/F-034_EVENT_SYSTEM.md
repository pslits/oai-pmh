# Feature F-034: Event System (PSR-14 Hooks)

**Feature ID:** F-034  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** Cross-cutting  
**Bounded Context:** Cross-cutting (all contexts)  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement a PSR-14 Event Dispatcher for extensibility hooks throughout the request lifecycle. Events allow third-party code to observe and react to OAI-PMH operations without modifying core code. Includes request, record, and system events.

## User Stories

**US-034.1:** As a **plugin developer**, I want to hook into the request lifecycle (before/after request, before/after record serialization) so that I can add custom behavior without modifying core code.

**US-034.2:** As a **repository administrator**, I want to register event listeners in configuration so that I can add custom processing without coding.

## Acceptance Criteria

- [ ] PSR-14 Event Dispatcher integrated
- [ ] Request events:
  - `oai.request.before` — before request processing
  - `oai.request.after` — after response generated
- [ ] Record events:
  - `oai.record.before_load` — before fetching record
  - `oai.record.after_load` — after record loaded
  - `oai.record.before_serialize` — before metadata serialization
  - `oai.record.after_serialize` — after metadata XML generated
- [ ] System events:
  - `oai.cache.miss` — cache miss occurred
  - `oai.error` — error occurred
  - `oai.rate_limit` — rate limit triggered
- [ ] Listeners registered via configuration or PHP attributes
- [ ] Event priority for ordering multiple listeners
- [ ] Example event listener provided
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-023** HTTP Entry Point & Middleware Pipeline (event dispatch points)

### Blocks
- None (extensibility feature)

## Files

```
src/Infrastructure/Event/PsrEventDispatcher.php
src/Protocol/Event/RequestBeforeEvent.php
src/Protocol/Event/RequestAfterEvent.php
src/Repository/Event/RecordLoadedEvent.php
src/MetadataSerialization/Event/RecordSerializedEvent.php
tests/Infrastructure/Event/PsrEventDispatcherTest.php
```

## Testing Requirements

- [ ] Events dispatched at correct lifecycle points
- [ ] Listeners receive correct event data
- [ ] Priority ordering works
- [ ] Listener errors don't crash the server
- [ ] Example listener functions correctly

## Notes

- PSR-14 Event Dispatcher standard: https://www.php-fig.org/psr/psr-14/
- Consider `symfony/event-dispatcher` as the implementation library.
- Events are passive — listener failures should log but never block the request.
