# ADR-0005: Caching Strategy

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect, Performance Engineer  
**Technical Story**: Repository Server Requirements - Section 3.3.3

---

## Context

OAI-PMH Repository Server serves mostly read-only data. Performance requirements demand <1s response times for large datasets (5M+ records). Caching is essential for:

- **Static Responses**: Identify, ListMetadataFormats (never change)
- **Semi-Static**: ListSets (changes rarely)
- **Query Results**: First page of ListRecords/ListIdentifiers
- **Individual Records**: Frequently accessed records

Requirements:
- Support horizontal scaling (distributed cache)
- Configurable TTLs per cache type
- Cache bypass for debugging/testing
- Cache invalidation mechanisms

### Forces at Play

- **Performance**: Cache hits reduce database load and improve response time
- **Consistency**: Cached data may become stale
- **Scalability**: Multiple server instances need shared cache
- **Complexity**: Cache adds infrastructure dependency

---

## Decision

Implement a **multi-layer caching strategy** using PSR-6/PSR-16 interfaces with Redis as primary backend.

### Cache Architecture

```
┌──────────────┐
│  Controller  │
└──────┬───────┘
       │
┌──────▼────────────────────────────────────────────┐
│         CacheMiddleware (PSR-15)                  │
│  - Checks cache before reaching handler           │
│  - Stores response after handler execution        │
└──────┬────────────────────────────────────────────┘
       │
┌──────▼────────────────────────────────────────────┐
│      CachedRepositoryDecorator (Repository)       │
│  - Wraps RepositoryInterface                      │
│  - Caches getRecord(), listRecords(), etc.        │
└──────┬────────────────────────────────────────────┘
       │
┌──────▼────────────────────────────────────────────┐
│     Symfony Cache (PSR-6/PSR-16)                  │
│  - Redis adapter (primary)                        │
│  - APCu adapter (single-server fallback)          │
│  - Filesystem adapter (development)               │
└───────────────────────────────────────────────────┘
```

### Caching Rules

| Data Type | TTL | Invalidation | Strategy |
|-----------|-----|--------------|----------|
| **Identify Response** | Infinite | Config change | Full response cache |
| **ListMetadataFormats** | Infinite | Config change | Full response cache |
| **ListSets** | 1 hour | Manual/event | Full response cache |
| **GetRecord** | 1 hour | Record update | Per-record cache |
| **ListRecords (1st page)** | 15 min | New records | Query result cache + pagination |
| **ListIdentifiers (1st page)** | 15 min | New records | Query result cache + pagination |

### Cache Key Pattern

```
oai:{verb}:{param_hash}:{version}
```

**Examples**:
```
oai:Identify:global:v1
oai:ListMetadataFormats:global:v1
oai:GetRecord:oai:example:12345:oai_dc:v1
oai:ListRecords:md5(metadataPrefix+from+until+set):page:0:v1
```

### Implementation

```php
// src/Infrastructure/Cache/CachedRepositoryDecorator.php
final class CachedRepositoryDecorator implements RepositoryInterface
{
    public function __construct(
        private RepositoryInterface $repository,
        private CacheItemPoolInterface $cache,
        private int $ttl = 3600
    ) {}
    
    public function getRecord(RecordIdentifier $identifier): ?Record
    {
        $cacheKey = "oai:record:{$identifier->getValue()}";
        
        $item = $this->cache->getItem($cacheKey);
        
        if ($item->isHit()) {
            return $item->get();
        }
        
        $record = $this->repository->getRecord($identifier);
        
        if ($record !== null) {
            $item->set($record);
            $item->expiresAfter($this->ttl);
            $this->cache->save($item);
        }
        
        return $record;
    }
}
```

### Configuration

```yaml
cache:
  enabled: true
  default_ttl: 3600  # 1 hour
  
  redis:
    host: localhost
    port: 6379
    database: 0
    password: ${REDIS_PASSWORD}
  
  ttls:
    identify: null  # Never expire
    list_metadata_formats: null
    list_sets: 3600
    get_record: 3600
    list_records: 900  # 15 minutes
    list_identifiers: 900
```

---

## Alternatives Considered

### Alternative 1: No Caching

**Why Rejected**: Cannot meet <1s performance target for large datasets without caching.

### Alternative 2: Application-Level Cache (APCu Only)

**Why Rejected**: Doesn't work with horizontal scaling. Each server has separate cache.

### Alternative 3: Database Query Cache

**Why Rejected**: Limited control, database-specific, doesn't cache OAI-PMH responses.

---

## Consequences

### Positive Consequences

- **Performance**: 10-100x faster for cached responses
- **Scalability**: Reduces database load, supports more concurrent users
- **Flexibility**: PSR interfaces allow swapping cache backends

### Negative Consequences

- **Complexity**: Adds Redis dependency
- **Stale Data**: Cached data may be out-of-date (mitigated by reasonable TTLs)
- **Infrastructure**: Redis must be deployed and maintained

---

## Validation

- [x] Cache significantly improves response time (benchmark tests)
- [x] Cache invalidation works correctly
- [x] Cache bypass via request header works (X-No-Cache: true)
- [x] Redis failure doesn't crash server (graceful degradation)

---

## References

- [PSR-6 Caching Interface](https://www.php-fig.org/psr/psr-6/)
- [Symfony Cache Component](https://symfony.com/doc/current/components/cache.html)
- [Redis Documentation](https://redis.io/documentation)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
