# Feature F-025: HTTPS Enforcement

**Feature ID:** F-025  
**Priority:** MVP (MUST HAVE)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement HTTPS enforcement middleware that rejects or redirects HTTP requests when `security.force_https` is enabled. Includes HSTS (HTTP Strict Transport Security) header support for enhanced security.

## User Stories

**US-025.1:** As a **repository administrator**, I want to enforce HTTPS-only access so that metadata transmission is secure, especially for sensitive repository data.

**US-025.2:** As a **harvester**, I want to be redirected to HTTPS if I accidentally use HTTP so that my requests are automatically secured.

## Acceptance Criteria

- [ ] Configuration option `security.force_https: true/false`
- [ ] When enabled: HTTP requests rejected with HTTP 403 Forbidden or HTTP 301 redirect to HTTPS
- [ ] When disabled: HTTP and HTTPS both accepted
- [ ] HSTS header support: `Strict-Transport-Security: max-age=31536000; includeSubDomains` (configurable)
- [ ] HSTS configurable (enable/disable, max-age value)
- [ ] Middleware runs early in pipeline (before authentication/rate limiting)
- [ ] Log rejected HTTP requests as security events
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-002** Configuration Management (reads `security.force_https`)
- **F-023** HTTP Entry Point & Middleware Pipeline (middleware slot)

### Blocks
- None (standalone security feature)

## Files

```
src/AccessControl/Middleware/HttpsEnforcementMiddleware.php
tests/AccessControl/Middleware/HttpsEnforcementMiddlewareTest.php
```

## Testing Requirements

- [ ] HTTPS request passes through when force_https enabled
- [ ] HTTP request rejected with 403 when force_https enabled
- [ ] HTTP request passes through when force_https disabled
- [ ] HSTS header present when enabled
- [ ] HSTS header absent when disabled
- [ ] Security event logged for rejected requests

## Notes

- This was added as a NEW MVP requirement based on customer feedback for secure metadata transmission.
- The redirect vs. reject behavior (301 vs. 403) should be configurable.
