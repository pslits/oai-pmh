# Feature F-032: Prometheus Metrics & Monitoring

**Feature ID:** F-032  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** 7 — Observability Context  
**Bounded Context:** Observability  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the `MetricsCollector` aggregate and Prometheus adapter that expose Prometheus-compatible metrics at a `/metrics` endpoint. Metrics cover request counts, response times, cache effectiveness, rate limit violations, and database query performance.

## User Stories

**US-032.1:** As a **DevOps engineer**, I want a Prometheus-compatible metrics endpoint so that I can monitor the OAI-PMH server using Grafana dashboards and set up alerts.

## Acceptance Criteria

- [ ] `MetricsCollector` aggregate records metrics
- [ ] Metrics exposed at `/metrics` in Prometheus text format
- [ ] Metrics endpoint configurable: enable/disable, path
- [ ] Exposed metrics:

| Metric | Type | Labels |
|--------|------|--------|
| `oaipmh_requests_total` | Counter | verb, status |
| `oaipmh_response_duration_seconds` | Histogram | verb |
| `oaipmh_cache_hits_total` | Counter | — |
| `oaipmh_cache_misses_total` | Counter | — |
| `oaipmh_rate_limit_violations_total` | Counter | — |
| `oaipmh_records_served_total` | Counter | format |
| `oaipmh_database_query_duration_seconds` | Histogram | — |

- [ ] Metrics endpoint does not require authentication (configurable)
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (MetricsCollectorInterface)
- **F-002** Configuration Management (reads `monitoring` section)
- **F-023** HTTP Entry Point & Middleware Pipeline (`/metrics` route)

### Blocks
- None (observability is passive)

## Files

```
src/Observability/Aggregate/MetricsCollector.php
src/Infrastructure/Observability/PrometheusAdapter.php
tests/Observability/Aggregate/MetricsCollectorTest.php
tests/Infrastructure/Observability/PrometheusAdapterTest.php
```

## Testing Requirements

- [ ] Counter increment
- [ ] Histogram recording
- [ ] Prometheus text format output
- [ ] Metrics endpoint returns correct content
- [ ] Each metric type renders correctly
- [ ] Labels applied correctly

## Notes

- Consider using `promphp/prometheus_client_php` library for Prometheus integration.
- Grafana dashboard templates could be provided as an additional resource.
