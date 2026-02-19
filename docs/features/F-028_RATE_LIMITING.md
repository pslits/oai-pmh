# Feature F-028: Rate Limiting

**Feature ID:** F-028  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement rate limiting middleware and aggregate that tracks and enforces request rate limits per IP address and/or API key. Uses a fixed window counter algorithm (token bucket for post-MVP). Rate limit information is communicated via standard HTTP headers and HTTP 429 responses.

## User Stories

**US-028.1:** As a **repository administrator**, I want to limit request rates per IP to prevent abuse and ensure fair resource usage across harvesters.

**US-028.2:** As a **harvester**, I want clear rate limit headers so that I can adjust my harvesting speed to stay within limits.

## Acceptance Criteria

- [ ] `RateLimiter` aggregate with fixed window counter algorithm
- [ ] Rate limiting by IP address
- [ ] Rate limiting by API key (when authentication enabled)
- [ ] Configurable limits:
  - Requests per minute (default: 60)
  - Requests per hour (default: 1,000)
  - Requests per day (optional)
- [ ] Separate limits per IP and per API key
- [ ] Rate limit headers in all responses:
  - `X-RateLimit-Limit: 1000`
  - `X-RateLimit-Remaining: 994`
  - `X-RateLimit-Reset: 1708350000` (Unix timestamp)
- [ ] HTTP 429 Too Many Requests when limit exceeded
- [ ] `Retry-After` header when rate limited
- [ ] Rate limit counters stored in Redis (or cache backend)
- [ ] Rate limit violations logged as security events
- [ ] Rate limit violations tracked as metrics
- [ ] Configuration via `security.rate_limiting` section
- [ ] PHPStan Level 8 passes

## Technical Design

### Algorithm: Fixed Window Counter
Simple, predictable, easy to implement. Track request count per window (minute/hour).

### Counter Storage
Redis INCR + EXPIRE for atomic, distributed counter management:
```
key: "ratelimit:{ip}:{window_start}"
TTL: window duration (60s for per-minute, 3600s for per-hour)
```

## Dependencies

### Blocked By
- **F-002** Configuration Management (reads `security.rate_limiting`)
- **F-023** HTTP Entry Point & Middleware Pipeline (middleware slot)
- **F-024** Response Caching (Redis infrastructure for counters)

### Blocks
- **F-032** Prometheus Metrics (rate limit violation metric)

## Files

```
src/AccessControl/Aggregate/RateLimiter.php
src/AccessControl/Middleware/RateLimitMiddleware.php
src/Infrastructure/AccessControl/RedisRateLimitAdapter.php
tests/AccessControl/Aggregate/RateLimiterTest.php
tests/AccessControl/Middleware/RateLimitMiddlewareTest.php
```

## Testing Requirements

- [ ] Request within limit passes through
- [ ] Request exceeding limit returns 429
- [ ] Rate limit headers present in responses
- [ ] Retry-After header when rate limited
- [ ] Per-IP tracking
- [ ] Per-API-key tracking
- [ ] Window reset after time period
- [ ] Configuration respected
- [ ] Rate limit violation logged

## Notes

- Token bucket algorithm (smoother rate control) is a post-MVP enhancement.
- Redis is the recommended backend for distributed rate limiting.
