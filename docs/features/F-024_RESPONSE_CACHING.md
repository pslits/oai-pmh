# Feature F-024: Response Caching

**Feature ID:** F-024  
**Priority:** MVP (MUST HAVE)  
**Phase:** 8 — Infrastructure Adapters  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the caching layer with multiple backend adapters (Redis, file-based, null/disabled). The cache is used for OAI-PMH response caching (Identify, ListMetadataFormats, ListSets) and optionally for query result caching. Cache failures must degrade gracefully — never crash the application.

## User Stories

**US-024.1:** As a **repository administrator**, I want responses to be cached so that repeated harvester requests are served quickly without hitting the database.

**US-024.2:** As a **DevOps engineer**, I want to choose between Redis (production), file (development), and disabled (testing) cache backends via configuration.

**US-024.3:** As the **server application**, I want cache failures to be handled gracefully (bypass cache, serve from database) so that temporary cache outages don't cause errors.

## Acceptance Criteria

- [ ] `CacheInterface` implemented by three adapters:
  - `RedisCacheAdapter` — production (Redis 5.0+)
  - `FileCacheAdapter` — development/single-server fallback
  - `NullCacheAdapter` — testing (disables caching)
- [ ] Cache backend configurable via `cache.driver` setting
- [ ] Cacheable operations:
  - Identify response — cache indefinitely (invalidate on config change)
  - ListMetadataFormats — cache indefinitely
  - ListSets — cache with configurable TTL (default: 1 hour)
  - Query results — optional, configurable TTL
- [ ] TTL configurable per operation or global default
- [ ] Cache invalidation:
  - TTL-based expiration (automatic)
  - Manual invalidation via CLI command (post-MVP)
- [ ] Cache failures: degrade gracefully (bypass cache, don't fail request)
- [ ] Cache bypass for debugging (optional header or query parameter)
- [ ] Cache hit/miss tracking for metrics (F-032)
- [ ] PHPStan Level 8 passes

## Technical Design

### Cache Key Strategy
- Key pattern: `oaipmh:{verb}:{hash_of_params}`
- Example: `oaipmh:ListRecords:sha256(metadataPrefix=oai_dc&from=2025-01-01&set=biology&page=1)`

### Graceful Degradation
```php
try {
    $cached = $cache->get($key);
    if ($cached !== null) {
        $metricsCollector->incrementCacheHits();
        return $cached;
    }
} catch (\Throwable $e) {
    $logger->warning('Cache read failed, bypassing cache', ['error' => $e->getMessage()]);
}
// Continue without cache...
```

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (CacheInterface)
- **F-002** Configuration Management (reads `cache` section)

### Blocks
- **F-011** Identify Verb Handler (caches Identify response)
- **F-012** ListMetadataFormats Verb Handler (caches format list)
- **F-013** ListSets Verb Handler (caches set list)

## Files

```
src/Infrastructure/Cache/RedisCacheAdapter.php
src/Infrastructure/Cache/FileCacheAdapter.php
src/Infrastructure/Cache/NullCacheAdapter.php
tests/Infrastructure/Cache/RedisCacheAdapterTest.php
tests/Infrastructure/Cache/FileCacheAdapterTest.php
tests/Infrastructure/Cache/NullCacheAdapterTest.php
```

## Testing Requirements

- [ ] Redis adapter: set, get, delete, TTL expiration
- [ ] File adapter: set, get, delete, TTL expiration
- [ ] Null adapter: always returns null (no caching)
- [ ] Cache key generation is deterministic
- [ ] Graceful degradation on cache failure
- [ ] Cache bypass mechanism
- [ ] TTL configuration respected

## Notes

- Redis is recommended for production; file cache is suitable for development.
- PSR-6 or PSR-16 compliance is recommended but not required.
- APCu (in-memory) could be added as a fourth backend for single-server deployments.
