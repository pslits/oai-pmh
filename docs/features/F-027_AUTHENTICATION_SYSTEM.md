# Feature F-027: Authentication System

**Feature ID:** F-027  
**Priority:** MVP (skeleton) / Post-MVP (full implementations)  
**Phase:** 6 — Access Control Context  
**Bounded Context:** Access Control  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the pluggable authentication system with `AuthenticationProviderInterface` and middleware. For MVP, the system defaults to public access (no authentication required) but the middleware and provider interface are present for extensibility. Post-MVP adds concrete providers for HTTP Basic Auth, API keys, and enterprise SSO.

## User Stories

**US-027.1:** As a **repository administrator**, I want public access by default so that harvesters can access my repository without credentials (standard OAI-PMH behavior).

**US-027.2:** As an **enterprise administrator** (post-MVP), I want to require authentication for my OAI-PMH endpoint so that only authorized harvesters can access sensitive metadata.

**US-027.3:** As a **plugin developer** (post-MVP), I want a clear authentication provider interface so that I can integrate my organization's SSO system.

## Acceptance Criteria

### MVP
- [ ] `AuthenticationProviderInterface` defined
- [ ] `AuthenticationMiddleware` present in pipeline
- [ ] Default: public access mode (middleware passes all requests)
- [ ] Authentication enable/disable via `security.authentication.enabled` config
- [ ] Failed authentication → HTTP 401 Unauthorized (not an OAI-PMH error code)
- [ ] PHPStan Level 8 passes

### Post-MVP
- [ ] `BasicAuthProvider` — HTTP Basic Authentication
- [ ] `ApiKeyProvider` — Bearer token / API key in header or query
- [ ] Multiple auth providers active simultaneously
- [ ] Configuration defines auth requirements per verb (optional)
- [ ] OAuth2 / SAML / LDAP provider interface documented
- [ ] Authentication success/failure logged as security events

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (AuthenticationProviderInterface)
- **F-002** Configuration Management (reads `security.authentication`)
- **F-023** HTTP Entry Point & Middleware Pipeline (middleware slot)

### Blocks
- **F-029** Record-Level Access Control (user identity from authentication)

## Files

```
src/AccessControl/Contract/AuthenticationProviderInterface.php
src/AccessControl/Aggregate/Authenticator.php
src/AccessControl/Middleware/AuthenticationMiddleware.php
tests/AccessControl/Aggregate/AuthenticatorTest.php
tests/AccessControl/Middleware/AuthenticationMiddlewareTest.php
```

### Post-MVP Files
```
src/AccessControl/Provider/BasicAuthProvider.php
src/AccessControl/Provider/ApiKeyProvider.php
tests/AccessControl/Provider/BasicAuthProviderTest.php
tests/AccessControl/Provider/ApiKeyProviderTest.php
```

## Testing Requirements

### MVP
- [ ] Public access mode passes all requests
- [ ] Authentication disabled by default
- [ ] Middleware present in pipeline but transparent

### Post-MVP
- [ ] Basic Auth: valid credentials → authenticated
- [ ] Basic Auth: invalid credentials → 401
- [ ] API Key: valid key → authenticated
- [ ] API Key: missing/invalid key → 401
- [ ] Multiple providers: first match wins
- [ ] Auth events logged

## Notes

- OAI-PMH 2.0 specification does not define authentication — it's an extension.
- HTTP 401 is used for auth failures (not OAI-PMH error codes).
- Authentication is optional for most OAI-PMH deployments.
