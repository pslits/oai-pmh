# Feature F-033: Health Check Endpoint

**Feature ID:** F-033  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** 7 — Observability Context  
**Bounded Context:** Observability  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `HealthChecker` aggregate and `/health` endpoint that reports the health status of the OAI-PMH server and its dependencies (database, cache, disk). Returns a JSON response with individual check results and an overall status (healthy, degraded, unhealthy).

## User Stories

**US-033.1:** As a **DevOps engineer**, I want a health check endpoint so that my load balancer and monitoring system can detect when the server is unhealthy.

## Acceptance Criteria

- [ ] `/health` endpoint returns JSON health report
- [ ] Health checks:
  - Database connectivity (with response time)
  - Cache connectivity (if enabled, with response time)
  - Disk space (for logs, cache files)
- [ ] Overall status: `healthy` | `degraded` | `unhealthy`
  - Healthy: all checks pass
  - Degraded: non-critical check fails (e.g., cache down)
  - Unhealthy: critical check fails (e.g., database down)
- [ ] HTTP status codes:
  - 200 for healthy or degraded
  - 503 for unhealthy
- [ ] Health check does not require authentication
- [ ] Health check endpoint path configurable
- [ ] PHPStan Level 8 passes

## Technical Design

### Response Format
```json
{
  "status": "healthy",
  "timestamp": "2026-02-18T12:00:00Z",
  "checks": {
    "database": { "status": "healthy", "response_time_ms": 5 },
    "cache": { "status": "healthy", "response_time_ms": 1 },
    "disk": { "status": "healthy", "free_space_gb": 120 }
  }
}
```

## Dependencies

### Blocked By
- **F-007** Database Adapters (database connectivity check)
- **F-023** HTTP Entry Point & Middleware Pipeline (`/health` route)
- **F-024** Response Caching (cache connectivity check)

### Blocks
- None (standalone operational feature)

## Files

```
src/Observability/Aggregate/HealthChecker.php
tests/Observability/Aggregate/HealthCheckerTest.php
```

## Testing Requirements

- [ ] All checks healthy → status "healthy", HTTP 200
- [ ] Cache down → status "degraded", HTTP 200
- [ ] Database down → status "unhealthy", HTTP 503
- [ ] JSON response format correct
- [ ] Response time measurements included
- [ ] Disk space check

## Notes

- Health check should be lightweight — no expensive queries.
- Kubernetes liveness/readiness probe compatible.
