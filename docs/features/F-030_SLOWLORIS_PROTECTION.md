# Feature F-030: Slowloris Protection

**Feature ID:** F-030  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement connection timeout protection against Slowloris-style DDoS attacks that exhaust server resources by sending data very slowly. Includes configurable connection timeout, slow header detection, and automatic connection termination with security event logging.

## User Stories

**US-030.1:** As a **DevOps engineer**, I want automatic detection and termination of slow connections so that Slowloris attacks cannot exhaust server resources.

## Acceptance Criteria

- [ ] Connection timeout configurable (default: 30 seconds)
- [ ] Slow header transmission detected
- [ ] Automatic connection termination for slow connections
- [ ] Slow connection attempts logged as security events
- [ ] Graceful termination (send appropriate error response when possible)
- [ ] Configuration via `security` section
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-002** Configuration Management (timeout config)
- **F-023** HTTP Entry Point & Middleware Pipeline

### Blocks
- None (standalone security feature)

## Files

```
src/AccessControl/Middleware/SlowlorisProtectionMiddleware.php
tests/AccessControl/Middleware/SlowlorisProtectionMiddlewareTest.php
```

## Testing Requirements

- [ ] Normal-speed request completes successfully
- [ ] Slow connection terminated after timeout
- [ ] Security event logged
- [ ] Timeout configurable

## Notes

- Slowloris protection is often better handled at the web server level (Nginx/Apache configuration). This PHP-level protection is a defense-in-depth measure.
- This is a SHOULD HAVE requirement and may be deferred to post-MVP.
