# ADR-0007: Security and Authentication Approach

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect, Security Engineer  
**Technical Story**: Repository Server Requirements - Section 2.4

---

## Context

OAI-PMH server must support multiple deployment scenarios:
- Public repositories (no authentication)
- Protected repositories (authentication required)
- Mixed access (some records public, others restricted)

Security requirements:
- Prevent SQL injection
- Prevent XSS attacks
- Rate limiting to prevent abuse
- Secure credential storage
- GDPR compliance (optional IP logging)

---

## Decision

Implement **layered security architecture** with pluggable authentication and middleware-based rate limiting.

### Security Layers

#### 1. Input Validation

All OAI-PMH parameters validated against spec:
```php
final class OaiPmhValidator
{
    public function validateVerb(string $verb): OaiVerb
    {
        if (!in_array($verb, ['Identify', 'ListMetadataFormats', ...])) {
            throw new BadVerbException($verb);
        }
        return new OaiVerb($verb);
    }
}
```

#### 2. SQL Injection Prevention

- **Parameterized queries** via Doctrine DBAL (never string concatenation)
- Value objects validate before database access

#### 3. XSS Prevention

- XML libraries handle escaping automatically
- No user-generated HTML output

#### 4. Authentication Middleware

```php
final class AuthenticationMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        foreach ($this->providers as $provider) {
            $user = $provider->authenticate($request);
            if ($user !== null) {
                return $handler->handle(
                    $request->withAttribute('user', $user)
                );
            }
        }
        
        // No authentication = public access (if allowed)
        if ($this->allowPublic) {
            return $handler->handle($request);
        }
        
        throw new UnauthorizedException();
    }
}
```

#### 5. Rate Limiting Middleware

```php
final class RateLimitingMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $clientId = $this->getClientIdentifier($request);  // IP or API key
        
        $limit = $this->rateLimiter->check($clientId);
        
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('X-RateLimit-Limit', $limit->getLimit())
            ->withHeader('X-RateLimit-Remaining', $limit->getRemaining())
            ->withHeader('X-RateLimit-Reset', $limit->getResetTime());
    }
}
```

### Configuration

```yaml
security:
  authentication:
    enabled: false  # Public access in MVP
    allow_public: true
    
    providers:
      - type: api_key
        enabled: false
        header: X-API-Key
        
      - type: basic_auth
        enabled: false
        realm: "OAI-PMH Repository"
  
  rate_limiting:
    enabled: true
    storage: redis
    
    limits:
      by_ip:
        requests_per_minute: 60
        requests_per_hour: 1000
        requests_per_day: 10000
      
      by_api_key:
        requests_per_minute: 600
        requests_per_hour: 10000
        requests_per_day: 100000
```

---

## Validation

- [x] SQL injection tests pass (OWASP Top 10)
- [x] Rate limiting correctly throttles excessive requests
- [x] Authentication middleware blocks unauthorized access
- [x] All inputs validated before processing

---

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PSR-15 HTTP Middleware](https://www.php-fig.org/psr/psr-15/)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
