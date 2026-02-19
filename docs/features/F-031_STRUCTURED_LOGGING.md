# Feature F-031: Structured Logging

**Feature ID:** F-031  
**Priority:** MVP (MUST HAVE)  
**Phase:** 7 — Observability Context  
**Bounded Context:** Observability  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `RequestLogger` aggregate and Monolog adapter for structured JSON logging of all OAI-PMH requests, responses, errors, and security events. Includes GDPR-compliant IP anonymization, configurable log levels, log rotation, and security event logging.

## User Stories

**US-031.1:** As a **repository administrator**, I want structured JSON logs so that I can analyze request patterns and debug issues using standard log tools (ELK, Grafana Loki).

**US-031.2:** As a **GDPR compliance officer**, I want IP addresses to be anonymized in logs so that personal data is not stored without consent.

**US-031.3:** As a **security auditor**, I want authentication failures, rate limit violations, and suspicious requests logged so that I can detect potential attacks.

## Acceptance Criteria

- [ ] Structured JSON log format for all log entries
- [ ] Log entry fields for requests:
  - `timestamp` (ISO 8601 UTC)
  - `verb` (OAI-PMH verb)
  - `metadataPrefix`
  - `response_time_ms`
  - `records_returned`
  - `status` (success / error)
  - `ip` (anonymized)
  - `request_id` (UUID v4)
- [ ] Configurable log levels: DEBUG, INFO, WARNING, ERROR, CRITICAL
- [ ] Log level configurable via `logging.level`
- [ ] Log file path configurable via `logging.path`
- [ ] Log format: JSON or plain text (configurable)
- [ ] Log rotation: daily, with configurable retention (`logging.retention_days`)
- [ ] GDPR IP anonymization: mask last octet (e.g., `192.168.1.0`)
- [ ] Anonymization level configurable
- [ ] Security event logging:
  - Authentication attempts (success/failure)
  - Rate limit violations
  - Suspicious request patterns
  - Oversized requests
  - Slow connection attempts
- [ ] No sensitive data in logs (passwords, tokens, secret keys)
- [ ] PSR-3 Logger interface compatibility
- [ ] PHPStan Level 8 passes

## Technical Design

### Log Format (JSON)
```json
{
  "timestamp": "2026-02-18T12:00:00Z",
  "level": "INFO",
  "verb": "ListRecords",
  "metadataPrefix": "oai_dc",
  "from": "2025-01-01",
  "set": "biology",
  "response_time_ms": 42,
  "records_returned": 100,
  "status": "success",
  "ip": "192.168.1.0",
  "request_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

### Monolog Integration
Use Monolog as the PSR-3 logging implementation. Output to file with JSON formatter. Support additional handlers (syslog, stderr) via configuration.

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (LoggerInterface — PSR-3)
- **F-002** Configuration Management (reads `logging` section)
- **F-023** HTTP Entry Point & Middleware Pipeline (ResponseLoggerMiddleware)

### Blocks
- None (observability is passive — never blocks the request/response flow)

## Files

```
src/Observability/Aggregate/RequestLogger.php
src/Infrastructure/Observability/MonologLoggerAdapter.php
tests/Observability/Aggregate/RequestLoggerTest.php
tests/Infrastructure/Observability/MonologLoggerAdapterTest.php
```

## Testing Requirements

- [ ] Request logged with all expected fields
- [ ] Error logged with context
- [ ] Security event logged
- [ ] IP anonymization applied
- [ ] Log level filtering works
- [ ] JSON format output
- [ ] No sensitive data in log output
- [ ] Log rotation configuration

## Notes

- Observability is passive — log failures should never crash the server.
- `composer require monolog/monolog` as a dependency.
- Log destinations can be extended post-MVP (ELK, syslog, etc.).
