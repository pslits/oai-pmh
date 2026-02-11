# ADR-0006: Resumption Token Implementation

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect  
**Technical Story**: Repository Server Requirements - Section 2.1.5

---

## Context

OAI-PMH requires resumption tokens for paginating large result sets (ListRecords, ListIdentifiers, ListSets). With 5M+ records, pagination is critical. Resumption tokens must:

- Preserve query context (metadataPrefix, from, until, set)
- Expire after configurable time (e.g., 24 hours)
- Work with horizontal scaling (stateless or shared storage)
- Be opaque to clients
- Support completeListSize and cursor attributes

### Forces at Play

- **Stateless vs Stateful**: Store state in token vs external storage
- **Security**: Tokens must not expose sensitive data or be tamperable
- **Expiration**: Tokens must expire to prevent unbounded resource usage
- **Scalability**: Tokens must work across multiple server instances

---

## Decision

Use **stateless JWT-based resumption tokens** with embedded pagination state, validated with HMAC signature.

### Token Structure

**JWT Payload**:
```json
{
  "ver": "1.0",
  "verb": "ListRecords",
  "params": {
    "metadataPrefix": "oai_dc",
    "from": "2024-01-01T00:00:00Z",
    "until": null,
    "set": null
  },
  "cursor": 100,
  "totalRecords": 5000000,
  "exp": 1707696000
}
```

**Token Encoding**:
```
base64url(header).base64url(payload).hmac_sha256(signature)
```

**Example Token**:
```
eyJhbGci...payload...signature
```

### Implementation

```php
// src/Application/Service/ResumptionTokenService.php
final class ResumptionTokenService
{
    public function __construct(
        private string $secretKey,
        private int $tokenLifetime = 86400  // 24 hours
    ) {}
    
    public function createToken(
        OaiVerb $verb,
        array $parameters,
        int $cursor,
        ?int $totalRecords = null
    ): ResumptionToken {
        $payload = [
            'ver' => '1.0',
            'verb' => $verb->getValue(),
            'params' => $parameters,
            'cursor' => $cursor,
            'totalRecords' => $totalRecords,
            'exp' => time() + $this->tokenLifetime
        ];
        
        $encoded = $this->encodeJWT($payload, $this->secretKey);
        
        return new ResumptionToken(
            value: $encoded,
            expirationDate: new \DateTimeImmutable('@' . $payload['exp']),
            completeListSize: $totalRecords,
            cursor: $cursor
        );
    }
    
    public function parseToken(string $token): ParsedToken
    {
        $payload = $this->decodeJWT($token, $this->secretKey);
        
        if ($payload['exp'] < time()) {
            throw new BadResumptionTokenException('Token expired');
        }
        
        return new ParsedToken(
            verb: new OaiVerb($payload['verb']),
            parameters: $payload['params'],
            cursor: $payload['cursor'],
            totalRecords: $payload['totalRecords'] ?? null
        );
    }
}
```

### Token Usage in Handler

```php
// src/Application/Handler/ListRecordsHandler.php
public function handle(ServerRequestInterface $request): ListRecordsResponse
{
    $params = $request->getQueryParams();
    
    if (isset($params['resumptionToken'])) {
        // Resume from token
        $parsed = $this->tokenService->parseToken($params['resumptionToken']);
        $cursor = $parsed->getCursor();
        $originalParams = $parsed->getParameters();
    } else {
        // Initial request
        $cursor = 0;
        $originalParams = [
            'metadataPrefix' => $params['metadataPrefix'],
            'from' => $params['from'] ?? null,
            'until' => $params['until'] ?? null,
            'set' => $params['set'] ?? null
        ];
    }
    
    $pageSize = 100;
    $records = $this->repository->listRecords(/* ... */, $pageSize, $cursor);
    
    $nextToken = null;
    if (count($records) === $pageSize) {
        // More results available
        $nextToken = $this->tokenService->createToken(
            verb: new OaiVerb('ListRecords'),
            parameters: $originalParams,
            cursor: $cursor + $pageSize,
            totalRecords: $this->repository->countRecords(/* ... */)
        );
    }
    
    return new ListRecordsResponse($records, $nextToken);
}
```

---

## Alternatives Considered

### Alternative 1: Database-Stored Tokens

**Why Rejected**: Requires database and doesn't scale horizontally without shared database. Stateless tokens eliminate this dependency.

### Alternative 2: Cache-Stored Tokens (Redis)

**Why Rejected**: Adds Redis dependency for all deployments. Stateless tokens work even without cache.

### Alternative 3: Cursor-Based Pagination (Offset in Token)

**Why Rejected**: Risk of exposing internal implementation. JWT obscures details.

---

## Consequences

### Positive Consequences

- **Stateless**: Works with horizontal scaling, no shared state
- **Secure**: HMAC prevents tampering
- **Efficient**: No database lookups for token validation

### Negative Consequences

- **Token Size**: JWTs are larger than simple IDs (~200-400 bytes)
- **Cannot Revoke**: Cannot revoke individual tokens (mitigation: short lifetime)

---

## Validation

- [x] Tokens encode/decode correctly
- [x] Expired tokens return badResumptionToken error
- [x] Tampered tokens detected (HMAC validation)
- [x] Pagination works across multiple pages
- [x] completeListSize and cursor attributes returned

---

## References

- [JWT RFC 7519](https://tools.ietf.org/html/rfc7519)
- [OAI-PMH Resumption Tokens](https://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm#FlowControl)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
