# Feature F-026: Request Size Validation

**Feature ID:** F-026  
**Priority:** MVP (MUST HAVE)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement request size validation middleware that rejects requests with excessively large query strings or HTTP headers. This protects against URL-based attacks and malformed harvester requests.

## User Stories

**US-026.1:** As the **server**, I want to reject oversized requests so that URL-based DDoS attacks and malformed requests are blocked before reaching business logic.

## Acceptance Criteria

- [ ] Query string size limit: reject requests > 2KB (configurable)
- [ ] HTTP header size limit: reject requests > 8KB (optional, configurable)
- [ ] Oversized requests return OAI-PMH `badArgument` error with explanation
- [ ] Suspicious oversized requests logged as security events
- [ ] Middleware runs early in pipeline (after HTTPS, before auth)
- [ ] Size limits configurable via `security` config section
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-002** Configuration Management (reads size limit config)
- **F-010** OAI-PMH Error Handling (badArgument error)
- **F-023** HTTP Entry Point & Middleware Pipeline (middleware slot)

### Blocks
- None (standalone security feature)

## Files

```
src/AccessControl/Middleware/RequestSizeMiddleware.php
tests/AccessControl/Middleware/RequestSizeMiddlewareTest.php
```

## Testing Requirements

- [ ] Normal-sized request passes through
- [ ] Query string > 2KB rejected with badArgument
- [ ] Header > 8KB rejected (if enabled)
- [ ] Size limits configurable
- [ ] Security event logged for oversized requests

## Notes

- This was added as a NEW MVP requirement to prevent URL-based DDoS attacks.
