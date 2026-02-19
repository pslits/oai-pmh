# Feature F-023: HTTP Entry Point & Middleware Pipeline

**Feature ID:** F-023  
**Priority:** MVP (MUST HAVE)  
**Phase:** 8 — Infrastructure Adapters  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the HTTP entry point (`public/index.php`), application kernel, router, and PSR-15 middleware pipeline. The middleware pipeline processes each request through an ordered sequence of middleware (security, validation, dispatch, logging) before reaching the verb handlers. The router maps `/oai` to the OAI-PMH pipeline and `/health` and `/metrics` to operational endpoints.

## User Stories

**US-023.1:** As a **web server administrator**, I want a standard `public/index.php` document root so that I can configure Nginx/Apache to serve the application.

**US-023.2:** As the **application**, I want an ordered middleware pipeline so that security (auth, rate limit, HTTPS) is enforced before business logic runs.

**US-023.3:** As a **developer**, I want the bootstrap sequence to be clear and auditable so that I can debug startup issues.

## Acceptance Criteria

- [ ] `public/index.php` serves as web root entry point
- [ ] `Kernel.php` bootstraps the application:
  1. Load Composer autoload
  2. Load Configuration (YAML)
  3. Build MetadataFormatRegistry
  4. Build SchemaMapping (validate against DB)
  5. Wire infrastructure adapters (DB, cache)
  6. Build middleware pipeline
  7. Handle request → send response
- [ ] `Router.php` routes paths:
  - `/oai` → OAI-PMH middleware pipeline
  - `/health` → Health check endpoint
  - `/metrics` → Prometheus metrics endpoint
- [ ] PSR-15 middleware pipeline in order:
  1. `HttpsEnforcementMiddleware`
  2. `RequestSizeMiddleware`
  3. `AuthenticationMiddleware`
  4. `RateLimitMiddleware`
  5. `OaiRequestMiddleware` (parse + validate)
  6. `VerbDispatchMiddleware` (route to handler)
  7. `ResponseLoggerMiddleware` (log + metrics)
- [ ] HTTP response headers:
  - `Content-Type: text/xml; charset=UTF-8` for OAI-PMH responses
  - `Cache-Control` headers for cacheable responses
- [ ] Startup validation: fail-fast with clear errors for misconfigurations
- [ ] PHPStan Level 8 passes

## Technical Design

### Middleware Pipeline Architecture
```
HTTP Request
  → HttpsEnforcementMiddleware
    → RequestSizeMiddleware
      → AuthenticationMiddleware
        → RateLimitMiddleware
          → OaiRequestMiddleware (parse verb/args)
            → VerbDispatchMiddleware (route to handler)
              → ResponseLoggerMiddleware
                → HTTP Response
```

### Error Handling
- Startup errors (config, DB connection) → HTTP 503 with plain text error
- Runtime errors in middleware → appropriate HTTP status code or OAI-PMH error
- Unhandled exceptions → HTTP 500 with generic error (no stack trace in production)

## Dependencies

### Blocked By
- **F-002** Configuration Management (kernel loads config)
- **F-008** OAI-PMH Request Parsing (OaiRequestMiddleware)
- **F-017** Metadata Format Plugin System (registry built at startup)

### Blocks
- **F-025** HTTPS Enforcement (middleware in pipeline)
- **F-026** Request Size Validation (middleware in pipeline)
- **F-027** Authentication System (middleware in pipeline)
- **F-028** Rate Limiting (middleware in pipeline)
- **F-031** Structured Logging (ResponseLoggerMiddleware)
- **F-037** Integration Testing (full request/response cycle)

## Files

```
public/index.php
src/Infrastructure/Http/Kernel.php
src/Infrastructure/Http/Router.php
src/Infrastructure/Http/MiddlewarePipeline.php
tests/Infrastructure/Http/KernelTest.php
tests/Infrastructure/Http/RouterTest.php
tests/Infrastructure/Http/MiddlewarePipelineTest.php
```

## Testing Requirements

- [ ] Kernel bootstrap sequence completes with valid config
- [ ] Kernel fails fast with invalid configuration
- [ ] Router routes /oai to OAI-PMH pipeline
- [ ] Router routes /health to health check
- [ ] Router returns 404 for unknown paths
- [ ] Middleware pipeline executes in correct order
- [ ] Response Content-Type header correct
- [ ] Unhandled exception returns 500 (no stack trace)

## Notes

- The entry point should be compatible with both Apache (`.htaccess`) and Nginx (`try_files`).
- PHP built-in server supported for development: `php -S localhost:8080 -t public/`.
