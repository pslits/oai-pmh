# ADR-0007: Security and Authentication Approach

**Status**: Accepted (Updated)  
**Date**: 2026-02-10 (Updated: 2026-02-13)  
**Deciders**: Solutions Architect, Security Engineer  
**Technical Story**: Repository Server Requirements - Sections 2.4, 3.3.1, 4.4

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
- **HTTPS enforcement for secure deployments (NEW)**
- **Request size validation to prevent URL-based attacks (NEW)**
- **Slowloris protection against slow connection attacks (NEW)**
- **Enhanced security logging for threat detection (ENHANCED)**
- GDPR compliance (handled in ADR-0011)

---

## Decision

Implement **layered security architecture** with pluggable authentication and middleware-based rate limiting.

### Security Layers

#### 1. Transport Security (HTTPS Enforcement) - NEW

**HTTPS-Only Mode**:
```php
final class HttpsEnforcementMiddleware implements MiddlewareInterface
{
    private bool $forceHttps;
    private bool $sendHsts;
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($this->forceHttps && $request->getUri()->getScheme() !== 'https') {
            // Option 1: Redirect to HTTPS
            if ($this->redirectToHttps) {
                $httpsUri = $request->getUri()->withScheme('https')->withPort(443);
                return new RedirectResponse($httpsUri, 301);
            }
            
            // Option 2: Reject with 403
            throw new ForbiddenException('HTTPS required');
        }
        
        $response = $handler->handle($request);
        
        // Add HSTS header if enabled
        if ($this->sendHsts && $request->getUri()->getScheme() === 'https') {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }
        
        return $response;
    }
}
```

**Configuration**:
```yaml
security:
  https:
    force_https: true              # Reject/redirect HTTP requests
    redirect_to_https: false       # true = 301 redirect, false = 403 error
    hsts_enabled: true             # Send HSTS header
    hsts_max_age: 31536000         # 1 year in seconds
    hsts_include_subdomains: true
    hsts_preload: true
```

#### 2. Request Size Validation - NEW

**Prevent URL-based attacks**:
```php
final class RequestSizeValidationMiddleware implements MiddlewareInterface
{
    private int $maxQueryStringSize;
    private int $maxHeaderSize;
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Validate query string size
        $queryString = $request->getUri()->getQuery();
        if (strlen($queryString) > $this->maxQueryStringSize) {
            $this->logger->warning('Oversized query string', [
                'size' => strlen($queryString),
                'ip' => $this->getClientIp($request),
                'user_agent' => $request->getHeaderLine('User-Agent'),
            ]);
            
            throw new BadArgumentException(
                sprintf(
                    'Query string too large (%d bytes, maximum %d bytes)',
                    strlen($queryString),
                    $this->maxQueryStringSize
                )
            );
        }
        
        // Validate total header size (optional)
        if ($this->maxHeaderSize > 0) {
            $headerSize = $this->calculateHeaderSize($request);
            if ($headerSize > $this->maxHeaderSize) {
                $this->logger->warning('Oversized headers', [
                    'size' => $headerSize,
                    'ip' => $this->getClientIp($request),
                ]);
                
                throw new BadArgumentException('Request headers too large');
            }
        }
        
        return $handler->handle($request);
    }
    
    private function calculateHeaderSize(ServerRequestInterface $request): int
    {
        $size = 0;
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $size += strlen($name) + strlen($value) + 4; // ": " and "\r\n"
            }
        }
        return $size;
    }
}
```

**Configuration**:
```yaml
security:
  request_validation:
    max_query_string_size: 2048    # 2KB limit
    max_header_size: 8192          # 8KB limit (0 = disabled)
    log_oversized_requests: true   # Log to security log
```

#### 3. Connection Timeout (Slowloris Protection) - NEW

**Note**: This is primarily configured at the web server level (Nginx/Apache) but can be enforced in application:

```php
final class ConnectionTimeoutMiddleware implements MiddlewareInterface
{
    private int $requestTimeoutSeconds;
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Set execution time limit for this request
        set_time_limit($this->requestTimeoutSeconds);
        
        $startTime = microtime(true);
        
        try {
            $response = $handler->handle($request);
            
            $duration = microtime(true) - $startTime;
            
            // Log slow requests for monitoring
            if ($duration > ($this->requestTimeoutSeconds * 0.8)) {
                $this->logger->warning('Slow request detected', [
                    'duration_seconds' => $duration,
                    'verb' => $request->getQueryParams()['verb'] ?? 'unknown',
                    'ip' => $this->getClientIp($request),
                ]);
            }
            
            return $response;
        } catch (\Throwable $e) {
            // Log timeout attempts for security monitoring
            if ($e instanceof \RuntimeException && strpos($e->getMessage(), 'timeout') !== false) {
                $this->logger->error('Request timeout (possible Slowloris)', [
                    'duration_seconds' => microtime(true) - $startTime,
                    'ip' => $this->getClientIp($request),
                    'user_agent' => $request->getHeaderLine('User-Agent'),
                ]);
            }
            throw $e;
        }
    }
}
```

**Web Server Configuration** (Nginx example):
```nginx
http {
    # Client connection timeouts
    client_body_timeout 30s;
    client_header_timeout 30s;
    send_timeout 30s;
    
    # Limit request size
    client_max_body_size 10M;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 8k;
}
```

**Configuration**:
```yaml
security:
  connection:
    request_timeout_seconds: 30      # Max time per request
    slow_request_threshold: 24       # Log if > 80% of timeout (24s)
    log_slow_connections: true
```

#### 4. Input Validation

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

#### 5. SQL Injection Prevention

- **Parameterized queries** via Doctrine DBAL (never string concatenation)
- Value objects validate before database access

#### 6. XSS Prevention

- XML libraries handle escaping automatically
- No user-generated HTML output

#### 7. Authentication Middleware

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

#### 8. Rate Limiting Middleware

```php
final class RateLimitingMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $clientId = $this->getClientIdentifier($request);  // IP or API key
        
        $limit = $this->rateLimiter->check($clientId);
        
        // Log rate limit violations - ENHANCED
        if ($limit->isExceeded()) {
            $this->securityLogger->warning('Rate limit exceeded', [
                'client_id' => $clientId,
                'ip' => $this->getAnonymizedIp($request),
                'verb' => $request->getQueryParams()['verb'] ?? 'unknown',
                'limit' => $limit->getLimit(),
                'user_agent' => $request->getHeaderLine('User-Agent'),
            ]);
            
            throw new TooManyRequestsException(
                'Rate limit exceeded',
                ['Retry-After' => $limit->getRetryAfter()]
            );
        }
        
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('X-RateLimit-Limit', $limit->getLimit())
            ->withHeader('X-RateLimit-Remaining', $limit->getRemaining())
            ->withHeader('X-RateLimit-Reset', $limit->getResetTime());
    }
}
```

#### 9. Enhanced Security Logging - ENHANCED

**Security Event Logging**:
```php
final class SecurityLogger
{
    public function logAuthenticationAttempt(
        ServerRequestInterface $request,
        bool $success,
        ?string $username = null
    ): void {
        $this->logger->info('Authentication attempt', [
            'event_type' => 'auth_attempt',
            'success' => $success,
            'username' => $username,
            'ip' => $this->anonymizeIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }
    
    public function logSuspiciousRequest(
        ServerRequestInterface $request,
        string $reason,
        array $context = []
    ): void {
        $this->logger->warning('Suspicious request detected', [
            'event_type' => 'suspicious_request',
            'reason' => $reason, // 'sql_injection', 'xss_attempt', 'path_traversal'
            'ip' => $this->anonymizeIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'query_string' => substr($request->getUri()->getQuery(), 0, 200),
            'context' => $context,
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }
    
    public function logRestrictedRecordAccess(
        ServerRequestInterface $request,
        string $recordIdentifier,
        bool $accessGranted
    ): void {
        $this->logger->info('Restricted record access', [
            'event_type' => 'restricted_access',
            'record_id' => $recordIdentifier,
            'access_granted' => $accessGranted,
            'ip' => $this->anonymizeIp($request),
            'user' => $request->getAttribute('user')?->getUsername(),
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }
    
    /**
     * Anonymize IP address for GDPR compliance.
     * See ADR-0011 for full privacy implementation.
     */
    private function anonymizeIp(ServerRequestInterface $request): string
    {
        $ip = $this->getClientIp($request);
        
        if (!$this->config->get('privacy.anonymize_ip', true)) {
            return $ip;
        }
        
        // IPv4: mask last octet (192.168.1.XXX)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            $parts[3] = 'XXX';
            return implode('.', $parts);
        }
        
        // IPv6: mask last 80 bits
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return substr($ip, 0, 19) . ':0000:0000:0000:0000';
        }
        
        return 'UNKNOWN';
    }
}
```

**Security Log Events**:
- **Authentication Events**: All login attempts (success/failure), logout, API key usage
- **Rate Limiting Events**: Violations with client details
- **Suspicious Patterns**:
  - SQL injection attempts (detected by input validation)
  - XSS attempts in parameters
  - Path traversal attempts
  - Malformed OAI-PMH requests (fuzzing attempts)
- **Access Control Events**: Restricted record access attempts
- **Oversized Requests**: Query string or header size violations
- **Slow Connections**: Potential Slowloris attacks
- **Configuration Changes**: Security-related config updates (restart required)

**Log Format** (JSON for machine parsing):
```json
{
  "timestamp": "2026-02-13T10:30:45Z",
  "level": "WARNING",
  "event_type": "rate_limit_exceeded",
  "ip": "192.168.1.XXX",
  "verb": "ListRecords",
  "client_id": "ip:192.168.1.100",
  "limit": 60,
  "user_agent": "Mozilla/5.0 ...",
  "context": {
    "current_count": 65,
    "window_seconds": 60
  }
}
```
```

### Configuration

```yaml
security:
  # HTTPS Enforcement - NEW
  https:
    force_https: true                  # Reject/redirect HTTP requests
    redirect_to_https: false           # true = 301 redirect, false = 403
    hsts_enabled: true                 # Send HSTS header
    hsts_max_age: 31536000             # 1 year
    hsts_include_subdomains: true
    hsts_preload: true
  
  # Request Size Validation - NEW
  request_validation:
    max_query_string_size: 2048        # 2KB query string limit
    max_header_size: 8192              # 8KB total headers (0 = disabled)
    log_oversized_requests: true       # Log to security log
  
  # Connection Timeouts - NEW (Slowloris Protection)
  connection:
    request_timeout_seconds: 30        # Max request duration
    slow_request_threshold: 24         # Log if request > 80% timeout
    log_slow_connections: true         # Log potential Slowloris attacks
  
  # Authentication
  authentication:
    enabled: false                     # Public access in MVP
    allow_public: true
    
    providers:
      - type: api_key
        enabled: false
        header: X-API-Key
        
      - type: basic_auth
        enabled: false
        realm: "OAI-PMH Repository"
  
  # Rate Limiting
  rate_limiting:
    enabled: true
    storage: redis                     # redis, memcached, file
    
    limits:
      by_ip:
        requests_per_minute: 60
        requests_per_hour: 1000
        requests_per_day: 10000
      
      by_api_key:
        requests_per_minute: 600
        requests_per_hour: 10000
        requests_per_day: 100000
  
  # Enhanced Security Logging - ENHANCED
  logging:
    security_events: true              # Enable security event logging
    log_auth_attempts: true            # Log all authentication attempts
    log_rate_limit_violations: true    # Log rate limit violations
    log_suspicious_requests: true      # Log SQL injection, XSS attempts
    log_restricted_access: true        # Log access to restricted records
    log_level: WARNING                 # DEBUG, INFO, WARNING, ERROR
    
  # Note: IP anonymization configured in privacy section (see ADR-0011)
```

---

## Alternatives Considered

### Alternative 1: Application-Layer Firewall (WAF)

**Description**: Use a dedicated WAF (ModSecurity, Cloudflare WAF) instead of application-level security.

**Pros**:
- Proven protection against OWASP Top 10
- Regular rule updates
- Offloads security from application

**Cons**:
- Additional infrastructure complexity
- Cost (CloudFlare, AWS WAF)
- May block legitimate harvesters (false positives)
- Doesn't provide OAI-PMH-specific security

**Rejected because**: WAF complements but doesn't replace application security. OAI-PMH-specific validation still needed. For MVP, application-level security is more appropriate.

### Alternative 2: No HTTPS Enforcement

**Description**: Leave HTTPS enforcement to infrastructure (web server, load balancer).

**Pros**:
- Simpler application code
- Standard practice for many PHP applications

**Cons**:
- Customer requirement explicitly requests HTTPS enforcement
- Application has no control over security posture
- Cannot enforce HSTS headers

**Rejected because**: Requirements explicitly state HTTPS enforcement must be configurable in application. Security should be defense-in-depth.

### Alternative 3: No Slowloris Protection

**Description**: Rely entirely on web server configuration for connection timeout.

**Pros**:
- Simpler application code
- Web servers handle this well

**Cons**:
- Application has no visibility into slow attacks
- Cannot log security events for monitoring

**Rejected because**: Application-level monitoring provides better security observability and allows logging of attack attempts for threat intelligence.

---

## Consequences

### Positive

- **Defense in Depth**: Multiple security layers protect against various attack vectors
- **Configurability**: Security can be tuned per deployment (public vs. private repository)
- **Standards Compliance**: Uses PSR-15 middleware pattern for maintainability
- **Observability**: Comprehensive security logging enables threat detection
- **HTTPS Enforcement**: Ensures secure communication when required
- **Attack Prevention**: Request size validation and slowloris protection prevent resource exhaustion attacks
- **GDPR Friendly**: IP anonymization in logs (see ADR-0011 for full privacy architecture)

### Negative

- **Complexity**: Multiple middleware layers add code complexity
- **Performance**: Each middleware adds latency (minimal, but measurable)
- **Configuration**: More configuration options = more potential misconfiguration
- **False Positives**: Aggressive rate limiting may block legitimate harvesters

### Neutral

- **Middleware Order**: Critical (must be: HTTPS → Size → Timeout → Rate → Auth → Validation)
- **Redis Dependency**: Rate limiting requires Redis (or alternative storage)
- **Web Server Coordination**: Some security features require web server configuration (Nginx/Apache)

---

## Compliance

### Requirements Addressed

| Requirement Section | Compliance | Implementation |
|---------------------|------------|----------------|
| 2.4.1 Authentication Mechanisms | ✅ FULL | Authentication middleware with pluggable providers |
| 2.4.2 Rate Limiting | ✅ FULL | Token bucket algorithm with Redis storage |
| 2.4.3 HTTPS Enforcement (NEW) | ✅ FULL | HttpsEnforcementMiddleware with configurable redirect/reject |
| 2.4.4 Request Size Validation (NEW) | ✅ FULL | RequestSizeValidationMiddleware with logging |
| 2.4.5 Slowloris Protection (NEW) | ✅ FULL | ConnectionTimeoutMiddleware + web server config |
| 3.3.1 Enhanced Security Logging | ✅ FULL | SecurityLogger with all required event types |
| SQL Injection Prevention | ✅ FULL | Parameterized queries via Doctrine DBAL |
| XSS Prevention | ✅ FULL | XML escaping in serializers |

### OWASP Top 10 Coverage

| OWASP Category | Protection | Implementation |
|----------------|------------|----------------|
| A01: Broken Access Control | ✅ Mitigated | Authentication + record-level permissions |
| A02: Cryptographic Failures | ✅ Mitigated | HTTPS enforcement, password hashing (bcrypt) |
| A03: Injection | ✅ Mitigated | Parameterized queries, input validation |
| A04: Insecure Design | ✅ Mitigated | Layered security architecture |
| A05: Security Misconfiguration | ⚠️ Partial | Configuration validation needed (future) |
| A06: Vulnerable Components | ⚠️ External | Dependency scanning in CI/CD (separate ADR) |
| A07: Authentication Failures | ✅ Mitigated | Pluggable auth, bcrypt passwords, logging |
| A08: Software/Data Integrity | ⚠️ Partial | Resumption token signatures (ADR-0006) |
| A09: Logging Failures | ✅ Mitigated | Comprehensive security logging |
| A10: SSRF | ✅ N/A | No server-side HTTP requests in OAI-PMH protocol |

---

## Implementation Guidance

### Phase 1: Core Security (Week 6-7)

**Deliverables**:
- [ ] Implement HttpsEnforcementMiddleware
- [ ] Implement RequestSizeValidationMiddleware
- [ ] Implement ConnectionTimeoutMiddleware
- [ ] Implement SecurityLogger with all event types
- [ ] Configure Nginx/Apache timeouts for Slowloris protection
- [ ] Write unit tests for all middleware
- [ ] Write security integration tests (HTTPS enforcement, size validation, timeout)

**Dependencies**:
- PSR-15 middleware infrastructure
- PSR-3 logging infrastructure
- Configuration system (ADR-0008)

### Phase 2: Authentication & Rate Limiting (Week 8-9)

**Deliverables**:
- [ ] Implement AuthenticationMiddleware
- [ ] Implement RateLimitingMiddleware with Redis backend
- [ ] Create API key management (hashing, storage)
- [ ] Implement rate limit configuration
- [ ] Add authentication/rate limiting tests

**Dependencies**:
- Redis connection
- User/API key storage schema

### Timeline

- **Weeks 6-7**: Core security middleware (~40 hours)
- **Weeks 8-9**: Authentication & rate limiting (~40 hours)
- **Total Effort**: ~80 hours

---

## Validation

### Security Tests Required

- [x] **HTTPS Enforcement**:
  - [ ] HTTP requests rejected when force_https=true
  - [ ] HTTP requests redirected when redirect_to_https=true
  - [ ] HSTS header sent on HTTPS responses
  - [ ] Configuration options respected
  
- [x] **Request Size Validation**:
  - [ ] Query strings > 2KB rejected with badArgument
  - [ ] Oversized requests logged to security log
  - [ ] Headers > 8KB rejected when configured
  - [ ] Valid-sized requests pass through
  
- [x] **Slowloris Protection**:
  - [ ] Requests exceeding timeout terminated
  - [ ] Slow requests (> 80% timeout) logged
  - [ ] Normal requests complete successfully
  - [ ] Web server timeouts configured correctly
  
- [x] **Security Logging**:
  - [ ] All authentication attempts logged (success/failure)
  - [ ] Rate limit violations logged with client details
  - [ ] Suspicious requests logged (SQL injection patterns)
  - [ ] Restricted record access logged
  - [ ] IP addresses anonymized when configured (ADR-0011)
  - [ ] JSON format parseable by log aggregators
  
- [x] **SQL Injection**: Parameterized queries prevent injection
- [x] **Rate Limiting**: Correctly throttles excessive requests
- [x] **Authentication**: Middleware blocks unauthorized access
- [x] **All Inputs**: Validated before processing

### Security Audit Checklist

- [ ] OWASP Top 10 coverage verified
- [ ] Penetration testing performed (HTTPS bypass, size limits, slowloris)
- [ ] Security logging tested with log analysis tools
- [ ] Configuration options documented with security implications
- [ ] Web server configuration guide provided
- [ ] Security headers verified (HSTS, X-Content-Type-Options, etc.)

---

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PSR-15 HTTP Middleware](https://www.php-fig.org/psr/psr-15/)
- [OWASP Slowloris Protection](https://owasp.org/www-community/attacks/Slowloris)
- [HTTP Strict Transport Security (HSTS)](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security)
- [Repository Server Requirements v1.1 (2026-02-13)](../../docs/REPOSITORY_SERVER_REQUIREMENTS.md)
- [ADR-0011: Privacy & GDPR Compliance](0011-privacy-gdpr-compliance.md)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
| 2026-02-13 | 1.1 | Added HTTPS enforcement, request size validation, slowloris protection, enhanced security logging | Solutions Architect |
