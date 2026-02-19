# Big Picture Event Storming — OAI-PMH Repository Server

**Session Date:** February 18, 2026  
**Domain:** OAI-PMH Repository Server  
**Scope:** Full server lifecycle — installation, configuration, harvesting, operations  
**Type:** As-is / To-be (designing a new system based on requirements)  
**Source:** [REPOSITORY_SERVER_REQUIREMENTS.md](../REPOSITORY_SERVER_REQUIREMENTS.md)

---

## Executive Summary

This Big Picture Event Storming maps the entire OAI-PMH Repository Server domain from initial deployment through daily harvesting operations. It identifies **pivotal events** that anchor the timeline, **hot spots** requiring architectural decisions, and **opportunities** for differentiation. The output feeds directly into Process Modeling and Software Design sessions.

---

## Actors & Systems

### 🟡 Persons / Roles

| Role | Responsibilities |
|------|-----------------|
| 🟡 **Repository Administrator** | Installs, configures, and maintains the server; defines sets, metadata formats, security policies |
| 🟡 **Platform Engineer (DevOps)** | Deploys infrastructure, scales, monitors, maintains uptime |
| 🟡 **Harvester Operator** | Configures and operates automated OAI-PMH harvesting clients |
| 🟡 **Plugin Developer** | Creates custom metadata format plugins, auth providers, storage adapters |
| 🟡 **Content Curator** | Manages source records in the underlying database; assigns sets, manages deletions |

### 🩷 External Systems

| System | Role |
|--------|------|
| 🩷 **Source Database** | MySQL/PostgreSQL holding the actual metadata records |
| 🩷 **Cache (Redis/Memcached)** | Response caching, resumption token storage, rate limit counters |
| 🩷 **Web Server (Nginx/Apache)** | HTTP termination, TLS, reverse proxy to PHP-FPM |
| 🩷 **Monitoring (Prometheus/Grafana)** | Metrics collection, dashboards, alerting |
| 🩷 **Log Aggregator (ELK/etc.)** | Centralized log storage, security event analysis |
| 🩷 **Composer / Packagist** | Package distribution and dependency management |

---

## Pivotal Events

These anchor the timeline and mark major phase transitions:

| # | Pivotal Event | Why It's Pivotal |
|---|--------------|------------------|
| **P1** | 🟠 **Server Installed** | Entry point — nothing works before this |
| **P2** | 🟠 **Configuration Validated** | Server is correctly wired to database, cache, and plugins |
| **P3** | 🟠 **Server Started** | Server is operational and accepting HTTP requests |
| **P4** | 🟠 **OAI-PMH Request Received** | Core value starts — a harvester has arrived |
| **P5** | 🟠 **Request Validated & Authorized** | Request passes all gates (auth, rate limit, params) |
| **P6** | 🟠 **Records Retrieved from Database** | Data flows from source to application |
| **P7** | 🟠 **Metadata Serialized to XML** | Domain data transformed to OAI-PMH protocol format |
| **P8** | 🟠 **XML Response Delivered** | Harvester receives the data — primary mission complete |
| **P9** | 🟠 **Resumption Token Generated** | Pagination boundary — marks incomplete result set |

---

## Timeline by Phase

### Phase 1: Installation & Setup

```
🟡 Repository Administrator
  → 🟠 Server Package Downloaded (via Composer)
  → 🟠 Dependencies Installed
  → 🟠 Configuration File Created (YAML)
      → 🟠 Repository Identity Configured (name, baseURL, admin email, protocol version)
      → 🟠 Database Connection Configured (host, credentials, driver)
      → 🟠 Database Schema Mapping Defined (tables, fields, joins)
      → 🟠 Deleted Record Policy Configured (no/transient/persistent)
      → 🟠 Granularity Configured (day-level or second-level)
      → 🟠 Resumption Token Settings Configured (page size, TTL, storage)
      → 🟠 Metadata Formats Configured (prefixes, plugins, enable/disable)
      → 🟠 Set Structure Defined (set specs, names, descriptions, hierarchy)
      → 🟠 Security Settings Configured (auth providers, rate limits, HTTPS)
      → 🟠 Cache Settings Configured (driver, TTL, hosts)
      → 🟠 Logging Settings Configured (level, format, rotation, GDPR options)
      → 🟠 Monitoring Endpoints Enabled (/health, /metrics)
  → 🟠 Configuration Validated                               ← PIVOTAL P2
      🔴 Hot Spot: What happens when config is partially valid?
  → 🟠 Database Migration Executed (schema created/updated)
  → 🟠 Database Connection Established
  → 🟠 Database Schema Mapping Verified
      🔴 Hot Spot: How to detect mismatched schema mappings at startup?
```

### Phase 2: Plugin & Extension Loading

```
🟡 Repository Administrator / 🟡 Plugin Developer
  → 🟠 Plugin Discovery Started (Composer autoload, PSR-4)
  → 🟠 Metadata Format Plugin Loaded
  → 🟠 Metadata Format Plugin Validated (implements interface)
  → 🟠 Metadata Format Registered (prefix, namespace, schema URL)
  → 🟠 Authentication Provider Loaded (if enabled)
  → 🟠 Authentication Provider Registered
  → 🟠 Storage Adapter Loaded (MySQL/PostgreSQL/custom)
  → 🟠 Storage Adapter Validated (connection test)
  → 🟠 Cache Backend Initialized
  → 🟠 Event Listeners Registered (PSR-14)
  → 🟠 Server Started                                        ← PIVOTAL P3
      🩷 Web Server (Nginx/Apache) starts routing to PHP-FPM
```

### Phase 3: Source Data Management (ongoing, external)

```
🟡 Content Curator (works in source database, not the OAI-PMH server directly)
  → 🟠 Record Created in Source Database
  → 🟠 Record Metadata Updated in Source Database
  → 🟠 Record Datestamp Updated (triggers visibility to harvesters)
  → 🟠 Record Assigned to Set
  → 🟠 Record Removed from Set
  → 🟠 Record Marked as Deleted in Source Database
  → 🟠 Set Created in Source Database
  → 🟠 Set Updated in Source Database
  → 🟠 Record Access Level Changed (public → restricted or vice versa)
      🔴 Hot Spot: No event-driven notification from source DB to OAI server;
                   server discovers changes only on next query (pull model)
```

### Phase 4: Harvesting — Request Intake

```
🟡 Harvester Operator → 🩷 Harvester Client
  → 🟠 OAI-PMH Request Received                              ← PIVOTAL P4
      🩷 Web Server routes HTTP request to application
  → 🟠 HTTPS Enforcement Checked
      (if force_https and request is HTTP → 🟠 HTTP Request Rejected)
  → 🟠 Request Size Validated (query string ≤ 2KB)
      (if oversized → 🟠 Oversized Request Rejected → 🟠 Security Event Logged)
  → 🟠 Slowloris Protection Applied (connection timeout)
  → 🟠 Authentication Checked
      (if auth enabled and fails → 🟠 Authentication Failed → HTTP 401)
  → 🟠 Rate Limit Checked
      (if exceeded → 🟠 Rate Limit Exceeded → HTTP 429 + Retry-After)
      🩷 Cache (Redis) stores rate limit counters
  → 🟠 OAI-PMH Parameters Validated (verb, arguments)
      (if invalid → 🟠 Bad Argument Error Returned)
      (if unknown verb → 🟠 Bad Verb Error Returned)
  → 🟠 Request Validated & Authorized                         ← PIVOTAL P5
```

### Phase 5: Harvesting — Identify Verb

```
🟡 Harvester Operator
  → 🟠 Identify Requested
  → 🟠 Cache Checked for Identify Response
      (if cache hit → 🟠 Cached Response Returned → skip to Phase 9)
  → 🟠 Repository Identity Loaded from Configuration
      (name, baseURL, protocolVersion, adminEmail, earliestDatestamp,
       deletedRecord policy, granularity, compression, descriptions)
  → 🟠 Identify Response Built (XML)
  → 🟠 Identify Response Cached (indefinite TTL)
  → 🟠 Identify Response Returned
```

### Phase 6: Harvesting — ListMetadataFormats Verb

```
🟡 Harvester Operator
  → 🟠 ListMetadataFormats Requested (optional: for specific identifier)
  → 🟠 Cache Checked
  → 🟠 Registered Formats Queried
      (if identifier given → 🟠 Record-Specific Formats Checked)
      (if no formats for record → 🟠 No Metadata Formats Error Returned)
  → 🟠 Metadata Formats Listed (prefix, schema, namespace per format)
  → 🟠 Response Cached
  → 🟠 Formats Response Returned
```

### Phase 7: Harvesting — ListSets Verb

```
🟡 Harvester Operator
  → 🟠 ListSets Requested (optional: resumptionToken)
  → 🟠 Cache Checked
  → 🟠 Sets Loaded from Database/Configuration
      (if no sets configured → 🟠 No Set Hierarchy Error Returned)
  → 🟠 Hierarchical Sets Resolved (colon-separated setSpec)
  → 🟠 Sets Listed (setSpec, setName, setDescription per set)
  → 🟠 Resumption Token Generated (if result set too large)
  → 🟠 Response Cached
  → 🟠 Sets Response Returned
```

### Phase 8: Harvesting — ListIdentifiers / ListRecords / GetRecord Verbs

```
🟡 Harvester Operator
  → 🟠 ListRecords Requested (metadataPrefix, optional: from, until, set)
     OR 🟠 ListIdentifiers Requested (metadataPrefix, optional: from, until, set)
     OR 🟠 GetRecord Requested (identifier, metadataPrefix)

  → 🟠 Cache Checked for Query Result
      (if cache hit → 🟠 Cached Response Returned → skip to Phase 9)

  → 🟠 Metadata Format Validated (is prefix registered?)
      (if not → 🟠 Cannot Disseminate Format Error Returned)

  [ For GetRecord only: ]
  → 🟠 Record Identifier Resolved
      (if not found → 🟠 ID Does Not Exist Error Returned)

  [ For ListRecords / ListIdentifiers: ]
  → 🟠 Date Range Filter Applied (from/until → UTCdatetime conversion)
      (if from > until or invalid → 🟠 Bad Argument Error Returned)
  → 🟠 Set Filter Applied (if set parameter present)
  → 🟠 Access Control Filter Applied (restricted records excluded for unauth users)

  → 🟠 Database Query Built from Schema Mapping                ← 🩷 Source Database
      (SQL constructed from YAML mapping config: tables, joins, fields)
  → 🟠 Database Query Executed
  → 🟠 Result Rows Mapped to Record Entities
  → 🟠 Records Retrieved from Database                         ← PIVOTAL P6

  [ For ListRecords / ListIdentifiers: ]
  → 🟠 Result Set Size Determined (completeListSize)
      (if zero → 🟠 No Records Match Error Returned)
  → 🟠 Page Size Applied (configurable, e.g. 100 records)
  → 🟠 Deleted Records Included with status="deleted" (if policy supports)

  [ For ListRecords / GetRecord: ]
  → 🟠 Metadata Serialization Dispatched to Format Plugin      ← 🩷 Format Plugin
  → 🟠 Metadata Serialized to XML                              ← PIVOTAL P7
      (per-record: plugin transforms DB row to XML fragment)

  → 🟠 OAI-PMH XML Response Assembled
      (responseDate, request params, record headers, metadata, about sections)
  → 🟠 Resumption Token Generated (if more pages remain)       ← PIVOTAL P9
      (token encodes: position, original query, expiration)
      🩷 Cache or DB stores token state

  → 🟠 Response Cached (with TTL)
```

### Phase 8a: Resumption Token Follow-Up

```
🟡 Harvester Operator
  → 🟠 Resumption Token Received in Request
  → 🟠 Resumption Token Decoded / Looked Up
      (if invalid → 🟠 Bad Resumption Token Error Returned)
      (if expired → 🟠 Bad Resumption Token Error Returned)
  → 🟠 Original Query Context Restored (prefix, from, until, set, cursor position)
  → 🟠 Next Page of Records Retrieved
  → 🟠 Metadata Serialized to XML
  → 🟠 New Resumption Token Generated (or empty token if last page)
  → 🟠 XML Response Delivered
```

### Phase 9: Response Delivery

```
  → 🟠 XML Response Delivered                                  ← PIVOTAL P8
      (HTTP 200, Content-Type: text/xml; charset=UTF-8)
  → 🟠 Response Headers Set (Cache-Control, rate limit headers)
  → 🟠 Request Logged (verb, params, response time, status code)
  → 🟠 Metrics Recorded (counter, histogram per verb)
      🩷 Monitoring (Prometheus)
```

### Phase 10: Operations & Monitoring (ongoing)

```
🟡 Platform Engineer (DevOps)
  → 🟠 Health Check Performed (/health endpoint)
      (database connectivity, cache connectivity, disk space → healthy/degraded/unhealthy)
  → 🟠 Metrics Scraped (/metrics endpoint by Prometheus)
  → 🟠 Slow Query Detected (query exceeds threshold)
  → 🟠 Error Logged (structured JSON, with request context)
  → 🟠 Security Event Logged (auth failures, rate limit violations, suspicious patterns)
  → 🟠 Cache Invalidated (manually via CLI, or by TTL expiration)
  → 🟠 Cache Warmed (background job pre-builds common responses)
  → 🟠 Log Rotated (daily, with retention policy)
  → 🟠 IP Address Anonymized in Logs (GDPR compliance)
  → 🟠 Database Connection Failed
      → 🟠 Database Connection Retried (exponential backoff)
      → 🟠 Circuit Breaker Tripped (if retries exhausted)
      → 🟠 Service Degraded (HTTP 503)
  → 🟠 Background Job Dispatched (cache warming, indexing, analytics)
  → 🟠 Background Job Completed
```

### Phase 11: Upgrades & Migrations

```
🟡 Repository Administrator / 🟡 Platform Engineer
  → 🟠 New Version Available (Composer update)
  → 🟠 Backup Created (configuration + database)
  → 🟠 Dependencies Updated (composer update)
  → 🟠 Database Migration Executed (schema changes)
  → 🟠 Configuration Changes Reviewed (breaking changes from CHANGELOG)
  → 🟠 OAI-PMH Endpoints Tested (compliance validation)
  → 🟠 Server Restarted with New Version
```

---

## Hot Spots (Ranked by Priority)

| # | Hot Spot | Severity | Notes |
|---|---------|----------|-------|
| 1 | 🔴 **Schema mapping flexibility vs. performance** — How to handle wildly different database structures (DSpace, EPrints, custom) while maintaining < 500ms response times with hundreds of millions of records? | Critical | Core technical challenge; mapping config must be expressive but generated SQL must be efficient |
| 2 | 🔴 **Resumption token scalability** — Stateless (JWT/signed) vs. stateful (Redis/DB) tokens? Stateless avoids storage but leaks query details; stateful requires shared storage across instances | High | Affects horizontal scaling; JWT tokens grow large with complex queries |
| 3 | 🔴 **Cache invalidation on source data change** — No event-driven notification from source database; server uses a pull model (discovers changes at query time). Stale cache could serve outdated data | High | TTL-based expiration is simple but imprecise; event-driven would require DB triggers or polling |
| 4 | 🔴 **Plugin security & sandboxing** — Third-party plugins run in the same PHP process with full access. How to prevent malicious or buggy plugins from harming the system? | High | No real sandboxing in PHP; rely on code review + interface contracts |
| 5 | 🔴 **Record-level access control performance** — Filtering restricted records in ListRecords/ListIdentifiers adds WHERE clauses and potentially extra JOINs, impacting large result sets | Medium | Must not break pagination (completeListSize includes only visible records) |
| 6 | 🔴 **Partial configuration validity** — What if YAML is syntactically correct but semantically wrong (e.g., table name misspelled)? Fail at startup or at first request? | Medium | Startup validation preferred but can't test all SQL mappings without querying |
| 7 | 🔴 **Multi-tenant support** — Requirements mention it for v2.0 but architecture decisions now affect feasibility later | Medium | Namespace isolation, per-tenant config, shared vs. separate databases |
| 8 | 🔴 **GDPR vs. security logging tension** — Need to log IPs for security (rate limiting, abuse detection) but anonymize for GDPR. How much to anonymize? | Medium | Configurable anonymization levels; separate security logs with shorter retention? |
| 9 | 🔴 **Source database schema drift** — Source database may change independently of OAI server. How to detect and handle mapping breakage? | Low-Medium | Startup validation, health check queries, admin notifications |
| 10 | 🔴 **Earliest datestamp calculation** — Must be determined from actual data, but scanning hundreds of millions of records for MIN(datestamp) is expensive | Low | Cache the value; update on schema migration or admin CLI command |

---

## Opportunities

| # | Opportunity | Potential Impact |
|---|------------|------------------|
| 1 | 💚 **Materialized views for common queries** — Pre-compute frequently requested result sets (e.g., all records in a set, recent changes) as database views | High — could drop response times dramatically for large repositories |
| 2 | 💚 **Streaming XML generation** — Instead of building full XML in memory, stream chunks to the HTTP response for very large result sets | High — reduces memory footprint; enables serving massive pages |
| 3 | 💚 **Admin dashboard** — Web-based dashboard showing real-time harvesting activity, error rates, popular sets/formats, active harvesters | Medium — improves operations UX significantly; post-MVP |
| 4 | 💚 **Webhook notifications** — Notify registered harvesters when new/updated records are available, enabling push-based harvesting alongside pull | Medium — modern integration pattern; complements OAI-PMH |
| 5 | 💚 **Automatic index recommendations** — Analyze slow queries and suggest database indexes based on actual query patterns | Medium — self-optimizing system |
| 6 | 💚 **Schema mapping wizard (CLI)** — Interactive CLI tool that connects to a database, shows tables/columns, and generates mapping YAML | Medium — dramatically reduces setup time |
| 7 | 💚 **OAI-PMH compliance self-test** — Built-in CLI command that runs the OAI-PMH validator against its own endpoints | Low-Medium — quality assurance built into the product |
| 8 | 💚 **Record change detection via database triggers or polling** — Enable event-driven cache invalidation without relying solely on TTL | Low-Medium — improves cache accuracy |

---

## Recommended Next Steps

- [x] Big Picture Event Storming complete
- [ ] **Process Model: ListRecords verb** — most complex and highest-value process; includes filtering, pagination, serialization, caching
- [ ] **Process Model: Server Configuration & Startup** — critical setup path with many validation decision points
- [ ] **Process Model: Resumption Token lifecycle** — cross-cutting concern affecting multiple verbs
- [ ] **Software Design: Bounded Contexts & Aggregates** — derive system architecture from process models
- [ ] Prioritize Hot Spots #1 (schema mapping) and #2 (resumption tokens) for spike/prototype work
- [ ] Explore Opportunity #6 (schema mapping wizard) as an early developer-experience win

---

*Generated from Event Storming session on February 18, 2026*
