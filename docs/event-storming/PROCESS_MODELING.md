# Process Modeling Event Storming — OAI-PMH Repository Server

**Session Date:** February 18, 2026  
**Domain:** OAI-PMH Repository Server  
**Based on:** [Big Picture Event Storming](BIG_PICTURE.md)  
**Source:** [REPOSITORY_SERVER_REQUIREMENTS.md](../REPOSITORY_SERVER_REQUIREMENTS.md)

---

## Executive Summary

This document details the key business processes identified during the Big Picture session. Each process is modeled using the Event Storming grammar: **Event → Policy → Command → System → Event**. The processes cover the full operational lifecycle of the OAI-PMH server, from configuration through harvesting to error handling.

---

## Process 1: ListRecords — Harvesting Full Records

**Trigger:** 🟠 ListRecords Request Received  
**Goal:** 🟠 OAI-PMH XML Response Delivered to Harvester  
**Priority:** Highest — this is the core value-delivery process of the entire system

### Happy Path

```
🟠 ListRecords Request Received
  → 🟣 Request Routing Policy: "Whenever an HTTP request arrives at the OAI endpoint, route to middleware pipeline"
    → 🔵 Route Request to Middleware
      → 🩷 Web Server (Nginx/Apache)
        → 🟠 Request Routed to Application

🟠 Request Routed to Application
  → 🟣 Security Gate Policy: "Whenever a request enters the pipeline, enforce HTTPS, validate size, check auth, check rate limit"
    → 🔵 Enforce HTTPS
      → 🩷 HTTPS Middleware
        → 🟠 HTTPS Verified

🟠 HTTPS Verified
  → 🟣 Size Validation Policy: "Whenever a request passes HTTPS check, validate request size ≤ 2KB"
    → 🔵 Validate Request Size
      → 🩷 Request Validation Middleware
        → 🟠 Request Size Validated

🟠 Request Size Validated
  → 🟣 Authentication Policy: "Whenever request size is valid, check authentication if enabled"
    → 🔵 Check Authentication
      → 🩷 Authentication Middleware
        → 🟠 Authentication Passed (or skipped if public access)

🟠 Authentication Passed
  → 🟣 Rate Limit Policy: "Whenever authentication passes, check rate limit by IP and/or API key"
    → 🔵 Check Rate Limit
      → 🩷 Rate Limit Middleware + 🩷 Cache (Redis)
        → 🟠 Rate Limit Passed

🟠 Rate Limit Passed
  → 🟣 Parameter Validation Policy: "Whenever rate limit passes, validate OAI-PMH verb and arguments"
    → 🔵 Validate OAI-PMH Parameters
      → 🩷 Parameter Validator
        → 🟠 Parameters Validated (verb=ListRecords, metadataPrefix required, from/until/set optional)

🟠 Parameters Validated
  → 🟣 Verb Dispatch Policy: "Whenever parameters are valid, dispatch to the appropriate verb handler"
    → 🔵 Dispatch to ListRecords Handler
      → 🩷 Request Dispatcher
        → 🟠 ListRecords Handler Invoked

🟠 ListRecords Handler Invoked
  → 🟣 Cache Check Policy: "Whenever a verb handler is invoked, check cache for an existing response"
    → 🟡 ListRecords Service checks 🟢 Cache Key (verb + metadataPrefix + from + until + set + page)
      → 🔵 Query Cache
        → 🩷 Cache (Redis/Memcached/File)
          → 🟠 Cache Miss (first request)

🟠 Cache Miss
  → 🟣 Format Validation Policy: "Whenever cache misses, validate that the requested metadata format exists"
    → 🔵 Validate Metadata Format
      → 🩷 Metadata Format Registry
        → 🟠 Metadata Format Validated (prefix=oai_dc registered and enabled)

🟠 Metadata Format Validated
  → 🟣 Date Filter Policy: "Whenever format is validated with from/until parameters, parse and validate date range"
    → 🔵 Parse Date Range
      → 🩷 UTCdatetime Value Object
        → 🟠 Date Range Parsed (from/until converted to UTCdatetime, granularity enforced)

🟠 Date Range Parsed
  → 🟣 Query Building Policy: "Whenever filter criteria are ready, build database query from schema mapping configuration"
    → 🟡 ListRecords Service reviews 🟢 Schema Mapping Config (record_table, identifier_field, datestamp_field, set joins, metadata fields)
      → 🔵 Build Database Query
        → 🩷 Schema Mapping Engine
          → 🟠 Database Query Built (SQL with WHERE clauses for date range, set, deleted status, access control)

🟠 Database Query Built
  → 🟣 Pagination Policy: "Whenever query is built, apply page size limit and cursor offset"
    → 🔵 Apply Pagination (LIMIT page_size+1, OFFSET cursor)
      → 🩷 Query Builder
        → 🟠 Paginated Query Ready

🟠 Paginated Query Ready
  → 🟣 Execution Policy: "Whenever paginated query is ready, execute against database"
    → 🔵 Execute Database Query
      → 🩷 Source Database (MySQL/PostgreSQL via Doctrine DBAL)
        → 🟠 Result Rows Retrieved

🟠 Result Rows Retrieved
  → 🟣 Mapping Policy: "Whenever raw rows are retrieved, map to Record domain entities"
    → 🔵 Map Rows to Record Entities
      → 🩷 Record Mapper (Entity Factory)
        → 🟠 Record Entities Created (with RecordHeader: identifier, datestamp, setSpecs, deleted status)

🟠 Record Entities Created
  → 🟣 Access Control Filter Policy: "Whenever records are created, filter out records the current user cannot access"
    → 🔵 Apply Access Control Filter
      → 🩷 Authorization Service
        → 🟠 Authorized Records Filtered (restricted records removed for anonymous users)

🟠 Authorized Records Filtered
  → 🟣 Empty Result Policy: "Whenever authorized records are empty, return noRecordsMatch error"
    (if records exist → continue to serialization)
    → 🔵 Proceed to Serialization

🟠 Records Ready for Serialization
  → 🟣 Serialization Policy: "Whenever records are ready, serialize each record's metadata using the requested format plugin"
    → For each Record:
      → 🔵 Serialize Record Metadata
        → 🩷 Metadata Format Plugin (e.g., DublinCore)
          → 🟠 Record Metadata Serialized to XML Fragment

🟠 All Records Serialized
  → 🟣 Response Assembly Policy: "Whenever all records are serialized, assemble the OAI-PMH XML response envelope"
    → 🔵 Assemble OAI-PMH Response
      → 🩷 XML Response Builder
        → 🟠 OAI-PMH Response Assembled
          (responseDate, request element, ListRecords element with record headers + metadata)

🟠 OAI-PMH Response Assembled
  → 🟣 Resumption Token Policy: "Whenever more records exist beyond the current page, generate a resumption token"
    → 🔵 Generate Resumption Token (encodes: cursor, metadataPrefix, from, until, set, expirationDate)
      → 🩷 Resumption Token Service + 🩷 Cache/Database
        → 🟠 Resumption Token Generated (or empty token if last page)

🟠 Resumption Token Generated
  → 🟣 Caching Policy: "Whenever a response is fully assembled, cache it for future identical requests"
    → 🔵 Cache Response (with TTL)
      → 🩷 Cache (Redis)
        → 🟠 Response Cached

🟠 Response Cached
  → 🟣 Response Delivery Policy: "Whenever response is ready, set HTTP headers and deliver"
    → 🔵 Set HTTP Headers (Content-Type: text/xml; charset=UTF-8, Cache-Control, X-RateLimit-*)
    → 🔵 Deliver HTTP Response (200 OK)
      → 🩷 Web Server
        → 🟠 OAI-PMH XML Response Delivered                    ← GOAL

🟠 OAI-PMH XML Response Delivered
  → 🟣 Logging Policy: "Whenever a response is delivered, log the request with metrics"
    → 🔵 Log Request (verb, params, response time, status code, record count)
      → 🩷 Logger (Monolog)
        → 🟠 Request Logged
    → 🔵 Record Metrics (counter, histogram)
      → 🩷 Metrics Collector
        → 🟠 Metrics Recorded
```

### Alternative Path: Cache Hit

Branches from "ListRecords Handler Invoked" when cache contains a valid response:

```
🟠 ListRecords Handler Invoked
  → 🟣 Cache Check Policy: "Whenever handler is invoked, check cache first"
    → 🔵 Query Cache
      → 🩷 Cache (Redis)
        → 🟠 Cache Hit Detected
          → 🟣 Cache Delivery Policy: "Whenever cache hit, return cached response directly"
            → 🔵 Return Cached Response
              → 🟠 OAI-PMH XML Response Delivered              ← GOAL (fast path)
```

### Alternative Path: Resumption Token Follow-Up

Branches when the request contains a resumptionToken instead of verb parameters:

```
🟠 ListRecords Request Received (with resumptionToken parameter)
  ... (middleware pipeline same as happy path) ...

🟠 Parameters Validated (resumptionToken present)
  → 🟣 Token Dispatch Policy: "Whenever a resumption token is present, decode and restore query context"
    → 🔵 Decode Resumption Token
      → 🩷 Resumption Token Service
        → 🟠 Token Decoded (cursor, metadataPrefix, from, until, set, expirationDate)
  → 🟣 Token Expiry Policy: "Whenever token is decoded, check if it has expired"
    → 🔵 Validate Token Expiry
      (if expired → 🟠 Token Expired → 🟠 Bad Resumption Token Error Returned)
  → 🟣 Context Restore Policy: "Whenever token is valid, restore original query context and advance cursor"
    → 🔵 Restore Query Context
      → 🟠 Query Context Restored
        → (continues from "Database Query Built" in happy path, with cursor advanced)
```

### Alternative Path: Error — Cannot Disseminate Format

```
🟠 Metadata Format Validated → FAILS (prefix not registered)
  → 🟣 Format Error Policy: "Whenever requested format is not supported, return cannotDisseminateFormat"
    → 🔵 Build Error Response (cannotDisseminateFormat)
      → 🩷 XML Error Response Builder
        → 🟠 Cannot Disseminate Format Error Returned
          → 🟠 Error Logged
```

### Alternative Path: Error — No Records Match

```
🟠 Authorized Records Filtered → result set is empty
  → 🟣 Empty Result Policy: "Whenever no records match the query, return noRecordsMatch error"
    → 🔵 Build Error Response (noRecordsMatch)
      → 🩷 XML Error Response Builder
        → 🟠 No Records Match Error Returned
          → 🟠 Error Logged
```

### Alternative Path: Error — Bad Argument (Date Range)

```
🟠 Date Range Parsed → FAILS (from > until, or invalid format)
  → 🟣 Date Error Policy: "Whenever date parsing fails, return badArgument error"
    → 🔵 Build Error Response (badArgument: "from date must not be later than until date")
      → 🩷 XML Error Response Builder
        → 🟠 Bad Argument Error Returned
          → 🟠 Error Logged
```

### Alternative Path: Rate Limit Exceeded

```
🟠 Rate Limit Passed → FAILS (limit exceeded)
  → 🟣 Rate Limit Enforcement Policy: "Whenever rate limit exceeded, reject with 429"
    → 🔵 Build Rate Limit Response (HTTP 429, Retry-After header, X-RateLimit-* headers)
      → 🩷 Rate Limit Middleware
        → 🟠 Rate Limit Exceeded Error Returned
          → 🟠 Security Event Logged
```

### Read Models Identified

| Read Model | Used By | Data Fields | Before Command |
|---|---|---|---|
| 🟢 Schema Mapping Config | 🟡 ListRecords Service | record_table, identifier_field, datestamp_field, deleted_field, set join config, metadata field mappings | 🔵 Build Database Query |
| 🟢 Cache Key | 🟡 ListRecords Service | verb, metadataPrefix, from, until, set, page/cursor | 🔵 Query Cache |
| 🟢 Rate Limit Counters | 🟡 Rate Limit Middleware | current_count, max_count, window_start, remaining, reset_time | 🔵 Check Rate Limit |
| 🟢 Format Registry | 🟡 ListRecords Service | prefix, namespace, schema URL, plugin class, enabled flag | 🔵 Validate Metadata Format |
| 🟢 Token State | 🟡 Resumption Token Service | cursor, original_query_params, expiration, completeListSize | 🔵 Decode Resumption Token |
| 🟢 Access Control Rules | 🟡 Authorization Service | user_role, ip_whitelist, record_access_flags | 🔵 Apply Access Control Filter |

### Hot Spots / Open Questions

1. 🔴 **N+1 query risk in serialization** — If each record's metadata requires a separate query (e.g., multi-table join per record), performance degrades. Batch loading needed.
2. 🔴 **completeListSize accuracy** — Running COUNT(*) on hundreds of millions of rows is expensive. Approximate count acceptable? Cached count?
3. 🔴 **Deleted records in result count** — Do deleted records count toward completeListSize and page size? Spec says they should appear in results.
4. 🔴 **Access control + pagination interaction** — If records are filtered by access control after query, page sizes become uneven. Filter in SQL or post-query?
5. 🔴 **Cache key collision** — Different users with different access levels seeing cached results intended for other access levels.

---

## Process 2: GetRecord — Retrieving a Single Record

**Trigger:** 🟠 GetRecord Request Received  
**Goal:** 🟠 Single Record XML Response Delivered  
**Priority:** High — essential verb for targeted harvesting

### Happy Path

```
🟠 GetRecord Request Received (identifier, metadataPrefix)
  → (middleware pipeline: HTTPS → size → auth → rate limit → param validation)
  → 🟠 Parameters Validated (verb=GetRecord, identifier required, metadataPrefix required)

🟠 Parameters Validated
  → 🟣 Verb Dispatch Policy: "Whenever GetRecord params validated, dispatch to GetRecord handler"
    → 🔵 Dispatch to GetRecord Handler
      → 🩷 Request Dispatcher
        → 🟠 GetRecord Handler Invoked

🟠 GetRecord Handler Invoked
  → 🟣 Cache Check Policy: "Whenever GetRecord handler invoked, check cache"
    → 🔵 Query Cache (key = identifier + metadataPrefix)
      → 🩷 Cache
        → 🟠 Cache Miss

🟠 Cache Miss
  → 🟣 Format Validation Policy: "Whenever cache misses, validate metadata format"
    → 🔵 Validate Metadata Format
      → 🩷 Format Registry
        → 🟠 Metadata Format Validated

🟠 Metadata Format Validated
  → 🟣 Record Lookup Policy: "Whenever format is valid, look up the record by identifier"
    → 🔵 Query Record by Identifier
      → 🩷 Source Database (via Schema Mapping)
        → 🟠 Record Found

🟠 Record Found
  → 🟣 Access Check Policy: "Whenever record is found, verify the requester has access"
    → 🔵 Check Record Access
      → 🩷 Authorization Service
        → 🟠 Access Granted

🟠 Access Granted
  → 🟣 Format Support Policy: "Whenever access granted, check if this record supports the requested format"
    → 🔵 Check Format Support for Record
      → 🩷 Format Plugin
        → 🟠 Format Supported for Record

🟠 Format Supported for Record
  → 🟣 Serialization Policy: "Whenever format is supported, serialize the record"
    → 🔵 Serialize Record Metadata
      → 🩷 Metadata Format Plugin
        → 🟠 Record Metadata Serialized

🟠 Record Metadata Serialized
  → 🟣 Response Assembly Policy: "Whenever metadata serialized, build GetRecord response"
    → 🔵 Assemble GetRecord Response (header + metadata + optional about)
      → 🩷 XML Response Builder
        → 🟠 GetRecord Response Assembled

🟠 GetRecord Response Assembled
  → 🟣 Cache & Deliver Policy: "Whenever response assembled, cache and deliver"
    → 🔵 Cache Response
    → 🔵 Deliver HTTP Response (200 OK)
      → 🟠 Single Record XML Response Delivered                ← GOAL
  → 🔵 Log Request
    → 🟠 Request Logged
```

### Alternative Path: Record Not Found

```
🟠 Record Found → FAILS (identifier not in database)
  → 🟣 Not Found Policy: "Whenever identifier doesn't match any record, return idDoesNotExist"
    → 🔵 Build Error Response (idDoesNotExist)
      → 🟠 ID Does Not Exist Error Returned
```

### Alternative Path: Deleted Record

```
🟠 Record Found (with deleted status)
  → 🟣 Deleted Record Policy: "Whenever a deleted record is found, return header only with status=deleted"
    → 🔵 Build Deleted Record Response (header with status="deleted", no metadata)
      → 🟠 Deleted Record Response Returned
```

### Alternative Path: Format Not Supported for This Record

```
🟠 Format Supported for Record → FAILS
  → 🟣 Format Mismatch Policy: "Whenever record doesn't support requested format, return cannotDisseminateFormat"
    → 🔵 Build Error Response (cannotDisseminateFormat)
      → 🟠 Cannot Disseminate Format Error Returned
```

---

## Process 3: Server Configuration & Startup

**Trigger:** 🟠 Server Installation Started  
**Goal:** 🟠 Server Operational and Accepting Requests  
**Priority:** High — everything depends on correct configuration

### Happy Path

```
🟠 Server Installation Started
  → 🟣 Installation Policy: "Whenever server installation starts, install via Composer"
    → 🟡 Repository Administrator
      → 🔵 Install Package (composer create-project or composer require)
        → 🩷 Composer / Packagist
          → 🟠 Server Package Installed

🟠 Server Package Installed
  → 🟣 Configuration Policy: "Whenever package installed, create configuration from template"
    → 🟡 Repository Administrator reviews 🟢 Example Configuration (config.example.yaml)
      → 🔵 Create Configuration File (config.yaml)
        → 🩷 File System
          → 🟠 Configuration File Created

🟠 Configuration File Created
  → 🟣 Identity Config Policy: "Whenever config created, configure repository identity"
    → 🟡 Repository Administrator reviews 🟢 OAI-PMH Identity Requirements (name, baseURL, email, protocol version, granularity, deleted policy)
      → 🔵 Set Repository Identity Settings
        → 🟠 Repository Identity Configured

🟠 Repository Identity Configured
  → 🟣 Database Config Policy: "Whenever identity set, configure database connection"
    → 🟡 Repository Administrator reviews 🟢 Database Credentials (host, port, driver, user, password)
      → 🔵 Set Database Connection Settings (with env var substitution for secrets)
        → 🟠 Database Connection Configured

🟠 Database Connection Configured
  → 🟣 Schema Mapping Policy: "Whenever DB connection configured, define schema mapping"
    → 🟡 Repository Administrator reviews 🟢 Database Schema (tables, columns, relationships)
      → 🔵 Define Schema Mapping (record_table, identifier_field, datestamp_field, set tables, metadata fields)
        → 🟠 Schema Mapping Defined

🟠 Schema Mapping Defined
  → 🟣 Format Config Policy: "Whenever schema mapped, configure metadata formats"
    → 🟡 Repository Administrator reviews 🟢 Available Format Plugins (prefix, namespace, schema, plugin class)
      → 🔵 Enable Metadata Formats
        → 🟠 Metadata Formats Configured

🟠 Metadata Formats Configured
  → 🟣 Remaining Config Policy: "Whenever formats configured, set remaining settings"
    → 🔵 Configure Sets (if applicable)
    → 🔵 Configure Resumption Token Settings (page_size, lifetime, storage)
    → 🔵 Configure Cache Settings (driver, TTL)
    → 🔵 Configure Security Settings (auth, rate limits, HTTPS)
    → 🔵 Configure Logging (level, format, rotation, GDPR)
    → 🔵 Configure Monitoring (/health, /metrics endpoints)
      → 🟠 Full Configuration Completed

🟠 Full Configuration Completed
  → 🟣 Validation Policy: "Whenever configuration completed, validate all settings"
    → 🔵 Validate Configuration
      → 🩷 Configuration Validator
        → 🟠 Configuration Validated (all sections verified: YAML syntax, required fields, value formats)

🟠 Configuration Validated
  → 🟣 Migration Policy: "Whenever config validated, run database migrations if needed"
    → 🟡 Repository Administrator
      → 🔵 Run Database Migrations (php bin/oai-pmh migrate)
        → 🩷 Migration System (Doctrine Migrations / Phinx)
          → 🟠 Database Migrations Executed

🟠 Database Migrations Executed
  → 🟣 Connection Test Policy: "Whenever migrations complete, test database connectivity"
    → 🔵 Test Database Connection
      → 🩷 Source Database
        → 🟠 Database Connection Verified

🟠 Database Connection Verified
  → 🟣 Mapping Test Policy: "Whenever DB connection verified, test schema mapping"
    → 🔵 Validate Schema Mapping (run test query against mapped tables/fields)
      → 🩷 Source Database
        → 🟠 Schema Mapping Verified

🟠 Schema Mapping Verified
  → 🟣 Plugin Loading Policy: "Whenever schema verified, load and register all plugins"
    → 🔵 Load Plugins (autoload via Composer PSR-4)
    → 🔵 Validate Plugin Interfaces
    → 🔵 Register Plugins
      → 🟠 Plugins Loaded and Registered

🟠 Plugins Loaded and Registered
  → 🟣 Startup Policy: "Whenever all components verified, start server"
    → 🔵 Start HTTP Server (php-fpm behind web server)
      → 🩷 Web Server (Nginx/Apache)
        → 🟠 Server Operational and Accepting Requests        ← GOAL
```

### Alternative Path: Configuration Validation Fails

```
🟠 Configuration Validated → FAILS
  → 🟣 Config Error Policy: "Whenever config validation fails, report errors and abort"
    → 🔵 Report Configuration Errors (list all issues with specific fields/values)
      → 🟠 Configuration Error Reported (server does NOT start)
        🔴 Hot Spot: Should partial config errors allow startup with degraded functionality?
```

### Alternative Path: Database Connection Fails

```
🟠 Database Connection Verified → FAILS
  → 🟣 DB Error Policy: "Whenever DB connection fails, report error with troubleshooting hints"
    → 🔵 Report Database Connection Error (host unreachable, auth failed, DB not found)
      → 🟠 Database Connection Error Reported (server does NOT start)
```

### Alternative Path: Schema Mapping Mismatch

```
🟠 Schema Mapping Verified → FAILS (table or column not found)
  → 🟣 Mapping Error Policy: "Whenever schema mapping doesn't match actual DB, report specific mismatches"
    → 🔵 Report Schema Mapping Errors (e.g., "Table 'items' not found; did you mean 'item'?")
      → 🟠 Schema Mapping Error Reported (server does NOT start)
```

### Read Models Identified

| Read Model | Used By | Data Fields | Before Command |
|---|---|---|---|
| 🟢 Example Configuration | 🟡 Repo Admin | config.example.yaml with all sections, comments, defaults | 🔵 Create Configuration File |
| 🟢 OAI-PMH Identity Requirements | 🟡 Repo Admin | name, baseURL, email, protocolVersion, granularity, deletedRecord, compression, descriptions | 🔵 Set Repository Identity |
| 🟢 Database Credentials | 🟡 Repo Admin | host, port, driver, username, password, charset | 🔵 Set Database Connection |
| 🟢 Database Schema | 🟡 Repo Admin | table names, column names, relationships, indexes | 🔵 Define Schema Mapping |
| 🟢 Available Format Plugins | 🟡 Repo Admin | prefix, namespace, schema URL, plugin class, enabled | 🔵 Enable Metadata Formats |

---

## Process 4: Resumption Token Lifecycle

**Trigger:** 🟠 Result Set Exceeds Page Size  
**Goal:** 🟠 All Pages Delivered to Harvester  
**Priority:** High — essential for large repositories (millions of records)

### Happy Path

```
🟠 Result Set Exceeds Page Size (e.g., query returns 5,000 records, page size is 100)
  → 🟣 Token Creation Policy: "Whenever result set exceeds page size, create a resumption token for the next page"
    → 🔵 Create Resumption Token
      → 🩷 Token Service
        → 🟠 Resumption Token Created
          Token contains:
          - cursor: 100 (next starting position)
          - metadataPrefix: "oai_dc"
          - from: "2020-01-01T00:00:00Z" (original query param)
          - until: null (original query param)
          - set: "biology" (original query param)
          - expirationDate: "2026-02-19T12:00:00Z" (now + token_lifetime)
          - completeListSize: 5000

🟠 Resumption Token Created
  → 🟣 Token Storage Policy: "Whenever token created, persist it for later retrieval"
    → 🔵 Store Token (in Redis, database, or encode as signed JWT)
      → 🩷 Token Storage Backend
        → 🟠 Token Stored

🟠 Token Stored
  → 🟣 Token Inclusion Policy: "Whenever token stored, include it in the OAI-PMH response"
    → 🔵 Append Resumption Token to Response
      (resumptionToken element with value, expirationDate, completeListSize, cursor attributes)
      → 🟠 Response with Token Delivered to Harvester

🟠 Response with Token Delivered to Harvester
  → 🟣 Harvester Continuation Policy: "Whenever harvester receives a response with a resumption token, it sends a follow-up request"
    → 🟡 Harvester Operator / 🩷 Harvester Client
      → 🔵 Send Follow-Up Request (verb=ListRecords, resumptionToken=<token_value>)
        → 🟠 Resumption Token Request Received

🟠 Resumption Token Request Received
  → 🟣 Token Lookup Policy: "Whenever a resumption token is received, look up and validate it"
    → 🔵 Look Up Token
      → 🩷 Token Storage Backend
        → 🟠 Token Found

🟠 Token Found
  → 🟣 Expiry Check Policy: "Whenever token found, verify it hasn't expired"
    → 🔵 Check Token Expiry (compare expirationDate to current time)
      → 🟠 Token Valid (not expired)

🟠 Token Valid
  → 🟣 Context Restore Policy: "Whenever token is valid, restore the original query context"
    → 🔵 Restore Query Context (metadataPrefix, from, until, set, cursor)
      → 🟠 Query Context Restored at Cursor Position 100

🟠 Query Context Restored
  → 🟣 Page Retrieval Policy: "Whenever context restored, fetch the next page of results"
    → 🔵 Execute Query (OFFSET 100, LIMIT 101)
      → 🩷 Source Database
        → 🟠 Next Page Retrieved (records 101-200)

🟠 Next Page Retrieved
  → 🟣 Continuation Policy: "Whenever more pages remain, generate a new token; if last page, include empty token"
    → 🔵 Generate Next Token (cursor=200) OR Empty Token (if records 101-200 is last page)
      → 🩷 Token Service
        → 🟠 New Token Generated (or empty token signaling completion)

🟠 New Token Generated
  → 🔵 Serialize Records + Append Token + Deliver Response
    → 🟠 Next Page Delivered to Harvester

  ... (cycle repeats until all pages exhausted) ...

🟠 Empty Resumption Token Included (last page)
  → 🟣 Completion Policy: "Whenever empty token returned, harvesting is complete"
    → 🟠 Full Result Set Delivered to Harvester               ← GOAL
```

### Alternative Path: Token Expired

```
🟠 Token Found
  → 🟣 Expiry Check Policy
    → 🔵 Check Token Expiry
      → 🟠 Token Expired (expirationDate < now)
        → 🟣 Expired Token Policy: "Whenever token has expired, return badResumptionToken error"
          → 🔵 Build Error Response (badResumptionToken)
            → 🟠 Bad Resumption Token Error Returned
              (Harvester must restart from first page)
```

### Alternative Path: Token Not Found (Invalid)

```
🟠 Resumption Token Request Received
  → 🟣 Token Lookup Policy
    → 🔵 Look Up Token
      → 🟠 Token Not Found (invalid value, tampered, or already consumed in stateful mode)
        → 🟣 Invalid Token Policy: "Whenever token not found, return badResumptionToken error"
          → 🔵 Build Error Response (badResumptionToken)
            → 🟠 Bad Resumption Token Error Returned
```

### Hot Spots / Open Questions

1. 🔴 **Stateless vs. stateful tokens** — JWT/signed tokens avoid storage but can be large and leak query details. Redis-stored tokens need shared storage across instances but are compact.
2. 🔴 **Token consumed once or reusable?** — Can a harvester re-request the same token (e.g., after network failure)? Idempotent or once-only?
3. 🔴 **Cursor-based vs. offset-based pagination** — OFFSET is simple but O(n); keyset/cursor pagination is O(1) but requires a sortable unique column.
4. 🔴 **completeListSize accuracy** — COUNT(*) is expensive; should we estimate or cache the count?

---

## Process 5: Identify Verb — Repository Discovery

**Trigger:** 🟠 Identify Request Received  
**Goal:** 🟠 Repository Identity XML Response Delivered  
**Priority:** High — first verb any harvester calls; must be fast (< 100ms target)

### Happy Path

```
🟠 Identify Request Received (no arguments required)
  → (middleware pipeline: HTTPS → size → auth → rate limit → param validation)
  → 🟠 Parameters Validated (verb=Identify, no additional args allowed)

🟠 Parameters Validated
  → 🟣 Verb Dispatch Policy: "Whenever Identify params validated, dispatch to Identify handler"
    → 🔵 Dispatch to Identify Handler
      → 🟠 Identify Handler Invoked

🟠 Identify Handler Invoked
  → 🟣 Cache Check Policy: "Whenever Identify handler invoked, check cache (long TTL or indefinite)"
    → 🔵 Query Cache (key = "identify_response")
      → 🩷 Cache
        → 🟠 Cache Miss (first request after startup or cache clear)

🟠 Cache Miss
  → 🟣 Identity Load Policy: "Whenever cache misses, load identity from configuration"
    → 🔵 Load Repository Identity from Config
      → 🩷 Configuration Loader
        → 🟠 Repository Identity Loaded
          (name, baseURL, protocolVersion, adminEmail, earliestDatestamp,
           deletedRecord, granularity, compression, descriptions)

🟠 Repository Identity Loaded
  → 🟣 Earliest Datestamp Policy: "Whenever identity loaded, determine earliest datestamp from database"
    → 🔵 Query Earliest Datestamp (SELECT MIN(datestamp_field) FROM record_table)
      → 🩷 Source Database
        → 🟠 Earliest Datestamp Determined

🟠 Earliest Datestamp Determined
  → 🟣 Response Build Policy: "Whenever all identity data available, build Identify XML response"
    → 🔵 Build Identify Response
      → 🩷 XML Response Builder
        → 🟠 Identify Response Built
          (includes: repositoryName, baseURL, protocolVersion, adminEmail,
           earliestDatestamp, deletedRecord, granularity, compression, description elements)

🟠 Identify Response Built
  → 🟣 Cache & Deliver Policy: "Whenever Identify response built, cache indefinitely and deliver"
    → 🔵 Cache Response (indefinite TTL, invalidate on config change)
    → 🔵 Deliver HTTP Response (200 OK)
      → 🟠 Repository Identity XML Response Delivered          ← GOAL
```

---

## Process 6: Error Handling — Database Failure During Request

**Trigger:** 🟠 Database Query Failed  
**Goal:** 🟠 Graceful Error Response Delivered (HTTP 503)  
**Priority:** Medium — resilience for production environments

### Happy Path (Graceful Degradation)

```
🟠 Database Query Failed (connection timeout, query timeout, connection refused)
  → 🟣 Retry Policy: "Whenever database query fails, retry with exponential backoff"
    → 🔵 Retry Database Query (attempt 1: 100ms delay)
      → 🩷 Source Database
        → 🟠 Retry Failed

🟠 Retry Failed
  → 🔵 Retry Database Query (attempt 2: 200ms delay)
    → 🩷 Source Database
      → 🟠 Retry Failed

🟠 Retry Failed
  → 🔵 Retry Database Query (attempt 3: 400ms delay)
    → 🩷 Source Database
      → 🟠 All Retries Exhausted

🟠 All Retries Exhausted
  → 🟣 Circuit Breaker Policy: "Whenever all retries fail, trip circuit breaker"
    → 🔵 Trip Circuit Breaker (prevent further DB queries for cooldown period)
      → 🟠 Circuit Breaker Tripped

🟠 Circuit Breaker Tripped
  → 🟣 Degraded Service Policy: "Whenever circuit breaker trips, serve cached responses or return 503"
    → 🔵 Check Cache for Stale Response
      (if stale cached response available → serve with warning header)
      (if no cache → 🔵 Return HTTP 503 Service Unavailable)
        → 🩷 XML Error Response Builder
          → 🟠 Service Unavailable Response Delivered

🟠 Service Unavailable Response Delivered
  → 🟣 Alert Policy: "Whenever DB failure leads to 503, log critical error and alert"
    → 🔵 Log Critical Error (DB host, error message, retry count, duration)
      → 🩷 Logger
        → 🟠 Critical Error Logged
    → 🔵 Increment Failure Metric
      → 🩷 Metrics Collector
        → 🟠 Failure Metric Recorded (triggers Prometheus alert)
```

---

## Process Summary

| # | Process | Trigger | Goal | Complexity |
|---|---------|---------|------|------------|
| 1 | ListRecords | Request received | XML response delivered | Very High |
| 2 | GetRecord | Request received | Single record XML delivered | Medium |
| 3 | Server Configuration & Startup | Installation started | Server accepting requests | High |
| 4 | Resumption Token Lifecycle | Result exceeds page size | All pages delivered | High |
| 5 | Identify | Request received | Identity XML delivered | Low |
| 6 | Database Failure Handling | Query failed | Graceful 503 or stale cache | Medium |

---

## Cross-Process Read Models

| Read Model | Used In Processes | Data Fields |
|---|---|---|
| 🟢 Schema Mapping Config | 1, 2, 5 | Tables, fields, joins, metadata field mappings |
| 🟢 Format Registry | 1, 2 | Prefix, namespace, schema, plugin class, enabled |
| 🟢 Rate Limit Counters | 1, 2, 5 | Current count, max, window, remaining, reset |
| 🟢 Token State | 1, 4 | Cursor, query params, expiration, completeListSize |
| 🟢 Repository Identity | 5 | Name, baseURL, version, email, granularity, etc. |
| 🟢 Access Control Rules | 1, 2 | User role, IP whitelist, record access flags |
| 🟢 Health Status | 6 | DB connectivity, cache status, disk space |

---

## Recommended Next Steps

- [x] Process Modeling complete for 6 key processes
- [ ] **Software Design** — Translate processes into Bounded Contexts and Aggregates
- [ ] Spike: Benchmark OFFSET vs. keyset pagination for 10M+ record datasets
- [ ] Spike: Compare JWT vs. Redis resumption token implementations
- [ ] Design: Schema mapping DSL/configuration language (Hot Spot #1 from Big Picture)

---

*Generated from Event Storming session on February 18, 2026*
