# Software Design Event Storming — OAI-PMH Repository Server

**Session Date:** February 18, 2026  
**Domain:** OAI-PMH Repository Server  
**Based on:** [Big Picture](BIG_PICTURE.md) and [Process Modeling](PROCESS_MODELING.md)  
**Source:** [REPOSITORY_SERVER_REQUIREMENTS.md](../REPOSITORY_SERVER_REQUIREMENTS.md)

---

## Executive Summary

This Software Design Event Storming translates the process models into concrete software architecture. It identifies **Bounded Contexts** as self-contained subsystems, defines **Aggregates** as the internal components we own and design, and maps **cross-context integration** through domain events. The result is a DDD-aligned architecture blueprint for the OAI-PMH Repository Server.

---

## Bounded Context Map

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        OAI-PMH Repository Server                            │
│                                                                             │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────────────┐  │
│  │   Protocol        │  │   Repository     │  │   Metadata               │  │
│  │   Context         │  │   Context        │  │   Serialization Context  │  │
│  │                   │  │                  │  │                          │  │
│  │ • OAI-PMH verbs   │  │ • Records        │  │ • Format plugins         │  │
│  │ • XML responses   │  │ • Sets           │  │ • XML generation         │  │
│  │ • Error codes     │  │ • Schema mapping │  │ • Format registry        │  │
│  │ • Parameter       │  │ • Query building │  │                          │  │
│  │   validation      │  │ • Pagination     │  │                          │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────────┬─────────────┘  │
│           │                      │                          │                │
│           │    Domain Events     │      Domain Events       │                │
│           ├──────────────────────┤──────────────────────────┤                │
│           │                      │                          │                │
│  ┌────────┴─────────┐  ┌────────┴─────────┐  ┌────────────┴─────────────┐  │
│  │   Access Control  │  │   Flow Control   │  │   Observability          │  │
│  │   Context         │  │   Context        │  │   Context                │  │
│  │                   │  │                  │  │                          │  │
│  │ • Authentication  │  │ • Resumption     │  │ • Logging                │  │
│  │ • Authorization   │  │   tokens         │  │ • Metrics                │  │
│  │ • Rate limiting   │  │ • Pagination     │  │ • Health checks          │  │
│  │ • HTTPS enforce   │  │ • Cursor mgmt    │  │ • Alerting               │  │
│  │ • Request size    │  │                  │  │                          │  │
│  └──────────────────┘  └──────────────────┘  └──────────────────────────┘  │
│                                                                             │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                     Configuration Context                            │   │
│  │   • YAML loading • Validation • Env vars • Schema mapping config     │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Bounded Contexts

### Context 1: Protocol Context

- **Domain**: OAI-PMH 2.0 protocol compliance — verb parsing, parameter validation, XML response envelope construction, error code management
- **Team**: Core server developers
- **Ubiquitous Language**: verb, request, response, responseDate, badArgument, badVerb, cannotDisseminateFormat, idDoesNotExist, noRecordsMatch, noSetHierarchy, noMetadataFormats

#### Aggregates

##### 🟨 OaiRequest

Represents an incoming OAI-PMH request, validated and parsed.

**Commands (in):**
- 🔵 Parse OAI-PMH Request (from HTTP query string)
- 🔵 Validate Verb (Identify, ListRecords, GetRecord, etc.)
- 🔵 Validate Arguments (metadataPrefix, identifier, from, until, set, resumptionToken)
- 🔵 Validate Argument Exclusivity (resumptionToken must be alone)

**Events (out):**
- 🟠 Request Parsed Successfully
- 🟠 Bad Verb Detected (unknown or missing verb)
- 🟠 Bad Argument Detected (missing required, illegal, repeated, or mutually exclusive args)

**Lifecycle:**
1. Created by: 🔵 Parse OAI-PMH Request → 🟠 Request Parsed Successfully
2. Validated by: 🔵 Validate Verb + 🔵 Validate Arguments → 🟠 Request Valid or 🟠 Bad Verb/Argument Detected
3. Immutable after creation (value object semantics)

**Key Rules:**
- GetRecord requires `identifier` AND `metadataPrefix`
- ListRecords/ListIdentifiers require `metadataPrefix` (unless resumptionToken present)
- `resumptionToken` is an exclusive argument (cannot combine with other params)
- `from` and `until` must match repository's declared granularity
- `from` must not be later than `until`

---

##### 🟨 OaiResponse

Represents the OAI-PMH XML response envelope being assembled.

**Commands (in):**
- 🔵 Create Response Envelope (set responseDate, request element)
- 🔵 Set Identify Content (repositoryName, baseURL, protocolVersion, etc.)
- 🔵 Set ListRecords Content (records with headers + metadata)
- 🔵 Set ListIdentifiers Content (record headers only)
- 🔵 Set ListSets Content (set specs + names + descriptions)
- 🔵 Set ListMetadataFormats Content (prefix, schema, namespace per format)
- 🔵 Set GetRecord Content (single record with header + metadata)
- 🔵 Add Error Element (error code + message)
- 🔵 Add Resumption Token Element (token value, expirationDate, completeListSize, cursor)
- 🔵 Serialize to XML

**Events (out):**
- 🟠 Response Envelope Created
- 🟠 Response Content Set
- 🟠 Error Response Built
- 🟠 XML Response Serialized

**Lifecycle:**
1. Created by: 🔵 Create Response Envelope → 🟠 Envelope Created
2. Populated by: 🔵 Set *Content → 🟠 Content Set
3. Finalized by: 🔵 Serialize to XML → 🟠 XML Serialized

**Key Rules:**
- responseDate must be in UTC (ISO 8601)
- request element echoes back verb and all legal arguments
- Error responses contain only error elements (no record data)
- Only one verb content section per response
- XML must validate against OAI-PMH 2.0 XSD

---

##### 🟨 OaiError

Represents OAI-PMH protocol error codes.

**Commands (in):**
- 🔵 Create Error (from code + message)
- 🔵 Map Exception to OAI Error Code

**Events (out):**
- 🟠 OAI Error Created

**Error Codes (enumeration):**
- `badArgument` — illegal or missing argument
- `badResumptionToken` — invalid or expired
- `badVerb` — illegal verb
- `cannotDisseminateFormat` — format not supported
- `idDoesNotExist` — identifier unknown
- `noRecordsMatch` — no results for criteria
- `noMetadataFormats` — no formats available
- `noSetHierarchy` — sets not supported

---

#### External Systems
- 🩷 **Web Server** — HTTP request entry point (Nginx/Apache + PHP-FPM)

---

### Context 2: Repository Context

- **Domain**: Record and set management — querying, filtering, and retrieving metadata records from the source database through configurable schema mappings
- **Team**: Core server developers, database adapter developers
- **Ubiquitous Language**: record, recordHeader, identifier, datestamp, setSpec, deletedRecord, set, setName, setDescription, schemaMapping, recordCollection

#### Aggregates

##### 🟨 Record

The core domain entity — a metadata record in the repository.

**Commands (in):**
- 🔵 Retrieve Record by Identifier
- 🔵 Load Record Metadata (from database via schema mapping)
- 🔵 Check Record Deletion Status
- 🔵 Check Record Access Level

**Events (out):**
- 🟠 Record Retrieved
- 🟠 Record Not Found
- 🟠 Deleted Record Retrieved
- 🟠 Record Access Denied

**Lifecycle:**
1. Queried by: 🔵 Retrieve Record → 🟠 Record Retrieved (or Not Found)
2. Status checked: 🔵 Check Deletion Status → 🟠 Deleted Record Retrieved (header only)
3. Access checked: 🔵 Check Access Level → 🟠 Access Denied (for restricted records)

**Key Rules:**
- Every record has a unique `identifier` (typically OAI format: `oai:domain:id`)
- Every record has a `datestamp` (UTC, reflects last modification)
- Records may belong to zero or more sets
- Deleted records retain their header (identifier, datestamp, setSpecs) but have no metadata
- Record metadata is lazy-loaded (only fetched when serialization is needed)

---

##### 🟨 RecordCollection

A paginated collection of records resulting from a list query.

**Commands (in):**
- 🔵 Query Records (by metadataPrefix, from, until, set, access level)
- 🔵 Apply Date Range Filter (from/until → UTCdatetime)
- 🔵 Apply Set Filter (setSpec)
- 🔵 Apply Access Control Filter (user role → visible records)
- 🔵 Apply Pagination (page size, cursor)
- 🔵 Count Total Results (completeListSize)

**Events (out):**
- 🟠 Records Queried
- 🟠 No Records Match (empty result set)
- 🟠 Page Retrieved (subset of total)
- 🟠 More Pages Available

**Lifecycle:**
1. Created by: 🔵 Query Records → 🟠 Records Queried
2. Filtered by: 🔵 Apply Date/Set/Access Filters → 🟠 Filtered Result
3. Paginated by: 🔵 Apply Pagination → 🟠 Page Retrieved + 🟠 More Pages Available

---

##### 🟨 Set

An organizational grouping of records (collection, category, community).

**Commands (in):**
- 🔵 List All Sets
- 🔵 Retrieve Set by SetSpec
- 🔵 Resolve Set Hierarchy (colon-separated: `dataset:climate:temperature`)
- 🔵 Query Records in Set

**Events (out):**
- 🟠 Sets Listed
- 🟠 Set Retrieved
- 🟠 Set Not Found
- 🟠 No Set Hierarchy Available

**Lifecycle:**
1. Queried by: 🔵 List All Sets → 🟠 Sets Listed (or 🟠 No Set Hierarchy)
2. Browsed by: 🔵 Retrieve Set → 🟠 Set Retrieved

**Key Rules:**
- `setSpec` is unique per repository
- Hierarchical sets use colon separator: `parent:child:grandchild`
- Sets have a required `setName` and optional `setDescription`
- Set descriptions follow Dublin Core or custom XML format

---

##### 🟨 SchemaMapping

Configurable mapping between the database schema and the OAI-PMH data model.

**Commands (in):**
- 🔵 Load Mapping Configuration (from YAML)
- 🔵 Build SQL Query (from mapping + filter criteria)
- 🔵 Map Result Row to Record Entity
- 🔵 Validate Mapping Against Database

**Events (out):**
- 🟠 Mapping Loaded
- 🟠 SQL Query Built
- 🟠 Row Mapped to Record
- 🟠 Mapping Validation Failed (table/column mismatch)

**Key Rules:**
- Maps: record_table → records, identifier_field → OAI identifier, datestamp_field → datestamp
- Supports: direct table, views, multi-table joins, stored procedures
- Metadata fields mapped per format (e.g., `oai_dc.title → dc_title` column)
- Sets mapped through junction table or direct column
- SQL generation must use parameterized queries (no injection)

---

##### 🟨 RepositoryIdentity

Represents the repository's identity information for the Identify verb.

**Commands (in):**
- 🔵 Load Identity from Configuration
- 🔵 Determine Earliest Datestamp (from database)
- 🔵 Get Identity for Identify Response

**Events (out):**
- 🟠 Identity Loaded
- 🟠 Earliest Datestamp Determined

**Properties:**
- repositoryName, baseURL, protocolVersion (always "2.0"), adminEmail(s)
- earliestDatestamp, deletedRecord policy, granularity, compression(s)
- description element(s) (oai-identifier, eprints, friends, etc.)

---

#### External Systems
- 🩷 **Source Database (MySQL/PostgreSQL)** — the actual data store queried via Doctrine DBAL

---

### Context 3: Metadata Serialization Context

- **Domain**: Transforming database records into XML metadata in various formats through a plugin architecture
- **Team**: Plugin developers, format maintainers
- **Ubiquitous Language**: metadataPrefix, metadataNamespace, schema, format plugin, serialize, Dublin Core, DataCite, XML fragment

#### Aggregates

##### 🟨 MetadataFormat

A registered metadata format with its serialization plugin.

**Commands (in):**
- 🔵 Register Format (prefix, namespace, schema URL, plugin class)
- 🔵 List Registered Formats
- 🔵 Check Format Support for Record
- 🔵 Validate Format Plugin (implements interface)

**Events (out):**
- 🟠 Format Registered
- 🟠 Formats Listed
- 🟠 Format Supported for Record
- 🟠 Format Not Supported for Record
- 🟠 Format Plugin Invalid

**Lifecycle:**
1. Registered by: 🔵 Register Format → 🟠 Format Registered
2. Queried by: 🔵 List Formats → 🟠 Formats Listed
3. Checked by: 🔵 Check Support → 🟠 Supported or Not Supported

**Key Rules:**
- `oai_dc` (Dublin Core) is the minimally recommended format (per OAI-PMH spec)
- Plugins must implement `MetadataFormatInterface` (serialize, supports, getPrefix, getNamespace, getSchema)
- Formats may be record-specific (some records only available in certain formats)
- New formats added via Composer packages + configuration registration

---

##### 🟨 MetadataSerializer

Responsible for transforming a Record entity into an XML metadata fragment.

**Commands (in):**
- 🔵 Serialize Record to XML (using a specific format plugin)
- 🔵 Validate Output XML (against format schema)

**Events (out):**
- 🟠 Record Serialized to XML
- 🟠 Serialization Failed (format error, missing required fields)

**Key Rules:**
- Output must be a well-formed XML fragment with proper namespace declarations
- Plugin determines field mapping: database columns → XML elements
- Must handle missing optional fields gracefully
- UTF-8 encoding enforced

---

#### External Systems
- 🩷 **Format Plugin Packages** — third-party Composer packages implementing MetadataFormatInterface

---

### Context 4: Access Control Context

- **Domain**: Authentication, authorization, rate limiting, and protocol-level security enforcement
- **Team**: Security developers
- **Ubiquitous Language**: authentication, authorization, rate limit, API key, token, IP address, whitelist, restricted, HTTPS, request size, Slowloris

#### Aggregates

##### 🟨 Authenticator

Handles authentication through pluggable providers.

**Commands (in):**
- 🔵 Authenticate Request (extract credentials from HTTP request)
- 🔵 Register Authentication Provider (basic auth, API key, SSO)
- 🔵 Validate Credentials

**Events (out):**
- 🟠 Authentication Succeeded (user identity resolved)
- 🟠 Authentication Failed (invalid credentials)
- 🟠 Authentication Skipped (public access mode)

**Lifecycle:**
1. Triggered by: 🔵 Authenticate Request → 🟠 Succeeded, Failed, or Skipped
2. Extended by: 🔵 Register Provider → 🟠 Provider Registered

**Key Rules:**
- Public access (no auth) is the default for MVP
- Multiple auth providers may be active simultaneously
- Auth middleware runs early in the pipeline, before verb dispatching
- Failed auth → HTTP 401 Unauthorized (not an OAI-PMH error code)

---

##### 🟨 RateLimiter

Tracks and enforces request rate limits per IP and/or API key.

**Commands (in):**
- 🔵 Check Rate Limit (identify requester by IP/key)
- 🔵 Increment Request Counter
- 🔵 Get Remaining Requests
- 🔵 Reset Rate Limit Window

**Events (out):**
- 🟠 Rate Limit Passed (within limits)
- 🟠 Rate Limit Exceeded (over limit)
- 🟠 Rate Limit Window Reset

**Lifecycle:**
1. Checked by: 🔵 Check Rate Limit → 🟠 Passed or Exceeded
2. Updated by: 🔵 Increment Counter → counter advanced
3. Cycled by: 🔵 Reset Window → 🟠 Window Reset

**Key Rules:**
- Limits configurable per IP (requests per minute/hour/day) and per API key
- Rate limit counters stored in Redis (or cache backend)
- HTTP 429 Too Many Requests when exceeded, with Retry-After header
- Response headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset

---

##### 🟨 RequestGuard

Handles protocol-level security: HTTPS enforcement, request size validation, Slowloris protection.

**Commands (in):**
- 🔵 Enforce HTTPS (reject HTTP if force_https enabled)
- 🔵 Validate Request Size (query string ≤ 2KB)
- 🔵 Check Connection Timeout (Slowloris protection)

**Events (out):**
- 🟠 HTTPS Verified
- 🟠 HTTP Request Rejected (non-HTTPS in forced mode)
- 🟠 Request Size Valid
- 🟠 Oversized Request Rejected
- 🟠 Slow Connection Terminated

**Key Rules:**
- HTTPS enforcement configurable (force_https: true/false)
- Oversized requests return badArgument error
- Suspicious requests logged as security events

---

##### 🟨 AccessControl

Handles record-level authorization for restricting metadata access.

**Commands (in):**
- 🔵 Check Record Access (user identity + record access flags)
- 🔵 Filter Record Collection by Access (remove restricted records for unauthorized users)

**Events (out):**
- 🟠 Access Granted
- 🟠 Access Denied
- 🟠 Records Filtered by Access Level

**Key Rules:**
- Anonymous users see only public records
- Authenticated users see public + records they're authorized for
- Record-level access flags stored in source database
- GetRecord for unauthorized record returns idDoesNotExist (not a distinct error code)
- ListRecords silently excludes unauthorized records

---

#### External Systems
- 🩷 **LDAP/SSO Provider** — enterprise authentication (post-MVP)
- 🩷 **Cache (Redis)** — rate limit counter storage

---

### Context 5: Flow Control Context

- **Domain**: Pagination of large result sets using resumption tokens; cursor management across multiple requests
- **Team**: Core server developers
- **Ubiquitous Language**: resumptionToken, cursor, pageSize, expirationDate, completeListSize, tokenLifetime, page, offset

#### Aggregates

##### 🟨 ResumptionToken

Encapsulates the state needed to resume paginated harvesting.

**Commands (in):**
- 🔵 Create Token (from query context: cursor, metadataPrefix, from, until, set, page size)
- 🔵 Decode Token (from opaque token string)
- 🔵 Validate Token (check expiration, integrity)
- 🔵 Create Empty Token (signals last page)
- 🔵 Store Token (persist to cache/database)
- 🔵 Delete Token (cleanup expired tokens)

**Events (out):**
- 🟠 Token Created (with value, expirationDate, completeListSize, cursor)
- 🟠 Token Decoded (query context restored)
- 🟠 Token Valid
- 🟠 Token Expired
- 🟠 Token Invalid (not found, tampered)
- 🟠 Empty Token Created (last page marker)

**Lifecycle:**
1. Created by: 🔵 Create Token → 🟠 Token Created
2. Stored by: 🔵 Store Token → persisted in backend
3. Retrieved by: 🔵 Decode Token → 🟠 Token Decoded
4. Validated by: 🔵 Validate Token → 🟠 Valid or Expired or Invalid
5. Terminated by: 🔵 Create Empty Token → 🟠 Empty Token Created (end of sequence)
6. Cleaned up by: 🔵 Delete Token → expired tokens purged

**Key Rules:**
- Token lifetime configurable (default: 24 hours)
- Token encapsulates ALL query context (prefix, from, until, set, cursor)
- Exclusive argument: resumptionToken cannot be combined with other params
- completeListSize and cursor are optional but recommended attributes
- Token format: opaque string (implementation may be JWT, UUID, or encoded)

---

##### 🟨 Paginator

Manages pagination logic — page size, cursor advancement, total count.

**Commands (in):**
- 🔵 Configure Page Size (from config)
- 🔵 Calculate Offset (from cursor position)
- 🔵 Determine Has More Pages (fetched page_size+1 to detect overflow)
- 🔵 Count Total Results (for completeListSize)

**Events (out):**
- 🟠 Page Calculated (offset, limit)
- 🟠 More Pages Available
- 🟠 Last Page Reached

**Key Rules:**
- Page size configurable (default: 100)
- Fetch page_size+1 rows to detect if more pages exist (avoid COUNT query when possible)
- completeListSize may be estimated for performance (hundreds of millions of records)
- Cursor-based pagination preferred over OFFSET for large datasets

---

#### External Systems
- 🩷 **Token Storage Backend (Redis/Database)** — persistence for stateful tokens

---

### Context 6: Observability Context

- **Domain**: Logging, monitoring, metrics, health checks — operational visibility into the running system
- **Team**: Platform engineers, DevOps
- **Ubiquitous Language**: log, metric, health check, structured logging, JSON, Prometheus, counter, histogram, alert, rotation, GDPR, anonymization

#### Aggregates

##### 🟨 RequestLogger

Structured logging of all OAI-PMH requests and responses.

**Commands (in):**
- 🔵 Log Request (verb, params, response time, status, record count)
- 🔵 Log Error (error type, message, context, stack trace)
- 🔵 Log Security Event (auth failure, rate limit, suspicious pattern)
- 🔵 Anonymize IP Address (GDPR: mask last octet)

**Events (out):**
- 🟠 Request Logged
- 🟠 Error Logged
- 🟠 Security Event Logged

**Key Rules:**
- JSON structured format for machine parsing
- Configurable log levels: DEBUG, INFO, WARNING, ERROR, CRITICAL
- No sensitive data in logs (passwords, tokens)
- IP anonymization configurable for GDPR compliance
- Log rotation: daily, with configurable retention

---

##### 🟨 MetricsCollector

Prometheus-compatible metrics endpoint for monitoring.

**Commands (in):**
- 🔵 Increment Request Counter (by verb, by status)
- 🔵 Record Response Time (histogram by verb)
- 🔵 Record Cache Hit/Miss
- 🔵 Record Rate Limit Violation
- 🔵 Expose Metrics at /metrics Endpoint

**Events (out):**
- 🟠 Metric Recorded

**Exposed Metrics:**
- `oaipmh_requests_total{verb, status}` — request counter
- `oaipmh_response_duration_seconds{verb}` — response time histogram
- `oaipmh_cache_hits_total` / `oaipmh_cache_misses_total` — cache effectiveness
- `oaipmh_rate_limit_violations_total` — abuse detection
- `oaipmh_records_served_total{format}` — records by format
- `oaipmh_database_query_duration_seconds` — DB performance

---

##### 🟨 HealthChecker

System health verification endpoint.

**Commands (in):**
- 🔵 Check Database Connectivity
- 🔵 Check Cache Connectivity
- 🔵 Check Disk Space
- 🔵 Build Health Report

**Events (out):**
- 🟠 Health Check Completed (healthy / degraded / unhealthy)

**Response Format:**
```json
{
  "status": "healthy|degraded|unhealthy",
  "timestamp": "2026-02-18T12:00:00Z",
  "checks": {
    "database": { "status": "healthy", "response_time_ms": 5 },
    "cache": { "status": "healthy", "response_time_ms": 1 },
    "disk": { "status": "healthy", "free_space_gb": 120 }
  }
}
```

---

#### External Systems
- 🩷 **Log Aggregator (ELK/etc.)** — centralized log storage
- 🩷 **Prometheus** — metrics scraping
- 🩷 **Grafana** — dashboards and alerting

---

### Context 7: Configuration Context

- **Domain**: Server configuration management — loading, validating, and providing configuration to all other contexts
- **Team**: Core server developers
- **Ubiquitous Language**: config, YAML, validation, environment variable, default, override, section, schema

#### Aggregates

##### 🟨 Configuration

The immutable, validated configuration loaded at server startup.

**Commands (in):**
- 🔵 Load Configuration (from YAML file)
- 🔵 Apply Environment Variable Substitution (e.g., `${DB_PASSWORD}`)
- 🔵 Apply Defaults (for optional fields not specified)
- 🔵 Validate Configuration (required fields, value formats, cross-field rules)
- 🔵 Get Section (repository, database, mapping, formats, cache, security, logging)

**Events (out):**
- 🟠 Configuration Loaded
- 🟠 Configuration Validated
- 🟠 Configuration Validation Failed (list of specific errors)

**Lifecycle:**
1. Loaded by: 🔵 Load Configuration → 🟠 Configuration Loaded
2. Processed by: 🔵 Apply Env Vars + Defaults → configuration enriched
3. Validated by: 🔵 Validate Configuration → 🟠 Validated or Failed
4. Immutable after validation — read-only for the lifetime of the process

**Configuration Sections:**
| Section | Provides To |
|---------|-------------|
| `repository` | Repository Context (identity), Protocol Context |
| `database` | Repository Context (connection) |
| `mapping` | Repository Context (schema mapping) |
| `metadata_formats` | Metadata Serialization Context |
| `resumption` | Flow Control Context |
| `cache` | All contexts (caching strategy) |
| `security` | Access Control Context |
| `logging` | Observability Context |
| `monitoring` | Observability Context |

**Key Rules:**
- YAML format for human readability
- Environment variable substitution for secrets: `${VAR_NAME}`
- Precedence: default.yaml < environment.yaml < environment variables
- Validation fails fast at startup with clear, actionable error messages
- Configuration is read-only after startup (changes require restart)

---

#### External Systems
- 🩷 **File System** — YAML configuration files
- 🩷 **Environment** — environment variables for secrets

---

## Cross-Context Integration

Domain events that cross bounded context boundaries form the integration contracts between contexts.

| Source Context | Event | Target Context | Policy | Command |
|---|---|---|---|---|
| Protocol | 🟠 Request Parsed Successfully | Access Control | 🟣 "Whenever request parsed, enforce security gates" | 🔵 Authenticate + Rate Limit + Guard |
| Access Control | 🟠 Authentication Passed + Rate Limit Passed | Protocol | 🟣 "Whenever security gates passed, dispatch to verb handler" | 🔵 Dispatch to Verb Handler |
| Protocol | 🟠 ListRecords Handler Invoked | Repository | 🟣 "Whenever list handler invoked, query records" | 🔵 Query Records |
| Protocol | 🟠 GetRecord Handler Invoked | Repository | 🟣 "Whenever get handler invoked, retrieve record" | 🔵 Retrieve Record by Identifier |
| Repository | 🟠 Records Queried | Access Control | 🟣 "Whenever records queried, apply access control filter" | 🔵 Filter by Access Level |
| Repository | 🟠 Records Retrieved | Metadata Serialization | 🟣 "Whenever records ready, serialize metadata" | 🔵 Serialize Record to XML |
| Metadata Serialization | 🟠 Records Serialized | Protocol | 🟣 "Whenever metadata serialized, assemble OAI response" | 🔵 Assemble OAI-PMH Response |
| Repository | 🟠 More Pages Available | Flow Control | 🟣 "Whenever more pages exist, generate resumption token" | 🔵 Create Resumption Token |
| Flow Control | 🟠 Token Created | Protocol | 🟣 "Whenever token created, append to response" | 🔵 Add Resumption Token Element |
| Protocol | 🟠 Resumption Token Received | Flow Control | 🟣 "Whenever token in request, decode and validate" | 🔵 Decode + Validate Token |
| Flow Control | 🟠 Token Decoded | Repository | 🟣 "Whenever token decoded, restore query and fetch next page" | 🔵 Query Records (with cursor) |
| Protocol | 🟠 XML Response Delivered | Observability | 🟣 "Whenever response delivered, log and record metrics" | 🔵 Log Request + Record Metrics |
| Access Control | 🟠 Authentication Failed | Observability | 🟣 "Whenever auth fails, log security event" | 🔵 Log Security Event |
| Access Control | 🟠 Rate Limit Exceeded | Observability | 🟣 "Whenever rate limit exceeded, log and record metric" | 🔵 Log Security Event + Record Metric |
| All Contexts | 🟠 Any Error | Observability | 🟣 "Whenever any error occurs, log it" | 🔵 Log Error |
| Configuration | 🟠 Configuration Loaded | All Contexts | 🟣 "Whenever config loaded, provide settings to all contexts" | 🔵 Get Section |

---

## Request Processing Pipeline

The following diagram shows how a request flows through bounded contexts:

```
HTTP Request
    │
    ▼
┌─────────────────────────┐
│   Protocol Context       │  Parse verb + params
│   🟨 OaiRequest          │
└──────────┬──────────────┘
           │ 🟠 Request Parsed
           ▼
┌─────────────────────────┐
│   Access Control Context │  Auth → Rate Limit → Guard
│   🟨 Authenticator       │
│   🟨 RateLimiter         │
│   🟨 RequestGuard        │
└──────────┬──────────────┘
           │ 🟠 Security Gates Passed
           ▼
┌─────────────────────────┐
│   Protocol Context       │  Dispatch to verb handler
│   (verb routing)         │
└──────────┬──────────────┘
           │ 🟠 Handler Invoked
           ▼
┌─────────────────────────┐
│   Flow Control Context   │  Check cache / decode token
│   🟨 ResumptionToken     │  (if token present)
└──────────┬──────────────┘
           │ 🟠 Query Context Ready
           ▼
┌─────────────────────────┐
│   Repository Context     │  Build query → execute → map
│   🟨 SchemaMapping       │
│   🟨 RecordCollection    │
│   🟨 Set                 │
└──────────┬──────────────┘
           │ 🟠 Records Retrieved
           ▼
┌─────────────────────────┐
│   Access Control Context │  Filter restricted records
│   🟨 AccessControl       │
└──────────┬──────────────┘
           │ 🟠 Authorized Records
           ▼
┌─────────────────────────┐
│   Metadata Serialization │  Serialize each record → XML
│   🟨 MetadataSerializer  │
│   🟨 MetadataFormat      │
└──────────┬──────────────┘
           │ 🟠 Metadata Serialized
           ▼
┌─────────────────────────┐
│   Flow Control Context   │  Generate resumption token
│   🟨 Paginator           │  (if more pages)
│   🟨 ResumptionToken     │
└──────────┬──────────────┘
           │ 🟠 Token Created (or Empty)
           ▼
┌─────────────────────────┐
│   Protocol Context       │  Assemble OAI-PMH XML envelope
│   🟨 OaiResponse         │
└──────────┬──────────────┘
           │ 🟠 XML Response Serialized
           ▼
┌─────────────────────────┐
│   Observability Context  │  Log + Metrics
│   🟨 RequestLogger       │
│   🟨 MetricsCollector    │
└──────────┬──────────────┘
           │
           ▼
       HTTP Response
```

---

## Architecture Notes

### Key Design Decisions

1. **Seven bounded contexts** — keeps each context focused on a single responsibility. Protocol, Repository, and Metadata Serialization are the core trio; Access Control, Flow Control, Observability, and Configuration are supporting contexts.

2. **Configuration Context is foundational** — all other contexts depend on it. It loads once at startup and provides read-only settings. No runtime reconfiguration without restart.

3. **Repository Context owns the database** — schema mapping, SQL generation, and entity mapping are all within this context. Other contexts interact with records through domain interfaces, never directly with the database.

4. **Metadata Serialization is a separate context** — even though it's closely related to Repository, separating it enables plugin-based extensibility. Third-party format plugins don't need to understand the database.

5. **Access Control is orthogonal** — it intercepts both before (auth, rate limit) and after (record filtering) the main processing pipeline. This dual-touch pattern is intentional and maps cleanly to middleware + post-query filtering.

6. **Flow Control context encapsulates all pagination logic** — token creation, storage, decoding, and cursor management. This keeps pagination concerns out of the Repository and Protocol contexts.

7. **Observability is passive** — it only reacts to events from other contexts. It never blocks or modifies the request/response flow. Log failure should not crash the server.

### Trade-offs

| Decision | Benefit | Trade-off |
|----------|---------|-----------|
| Separate Metadata Serialization context | Plugin extensibility; third-party format packages | Extra context boundary; slight overhead for in-process communication |
| Configuration immutable after startup | Simple, predictable, no race conditions | Any config change requires restart |
| Access Control as dual-touch (pre + post) | Clean separation of concerns | Two integration points instead of one |
| Flow Control separate from Repository | Pagination logic reusable across verbs | Token state management adds complexity |
| Observability passive (event-driven) | Cannot block requests; resilient to log failures | Slight async overhead; may miss events if listeners fail |

---

## Aggregate Summary

| Context | Aggregate | Commands In | Events Out | Complexity |
|---------|-----------|-------------|------------|------------|
| Protocol | 🟨 OaiRequest | 4 | 3 | Medium |
| Protocol | 🟨 OaiResponse | 10 | 4 | High |
| Protocol | 🟨 OaiError | 2 | 1 | Low |
| Repository | 🟨 Record | 4 | 4 | Medium |
| Repository | 🟨 RecordCollection | 6 | 4 | High |
| Repository | 🟨 Set | 4 | 4 | Medium |
| Repository | 🟨 SchemaMapping | 4 | 4 | High |
| Repository | 🟨 RepositoryIdentity | 3 | 2 | Low |
| Metadata | 🟨 MetadataFormat | 4 | 5 | Medium |
| Metadata | 🟨 MetadataSerializer | 2 | 2 | Medium |
| Access Control | 🟨 Authenticator | 3 | 3 | Medium |
| Access Control | 🟨 RateLimiter | 4 | 3 | Medium |
| Access Control | 🟨 RequestGuard | 3 | 5 | Low |
| Access Control | 🟨 AccessControl | 2 | 3 | Medium |
| Flow Control | 🟨 ResumptionToken | 6 | 6 | High |
| Flow Control | 🟨 Paginator | 4 | 3 | Medium |
| Observability | 🟨 RequestLogger | 4 | 3 | Low |
| Observability | 🟨 MetricsCollector | 5 | 1 | Low |
| Observability | 🟨 HealthChecker | 4 | 1 | Low |
| Configuration | 🟨 Configuration | 5 | 3 | Medium |
| **Total** | **20 Aggregates** | **81** | **64** | |

---

## Recommended Next Steps

- [x] Big Picture Event Storming
- [x] Process Modeling (6 processes)
- [x] Software Design (7 contexts, 20 aggregates)
- [ ] **Create interface definitions** for cross-context communication (PHP interfaces)
- [ ] **Prototype Schema Mapping** — highest complexity aggregate; spike on SQL generation from YAML
- [ ] **Prototype Resumption Token** — compare JWT vs. Redis implementations under load
- [ ] **Define event contracts** — specify exact payload for each cross-context domain event
- [ ] **Map to PHP namespaces** — `OaiPmh\Protocol\`, `OaiPmh\Repository\`, `OaiPmh\MetadataSerialization\`, etc.
- [ ] **Write ADRs** for each key design decision (Architecture Decision Records)
- [ ] **Validate against requirements** — confirm all Functional & Non-Functional requirements from REPOSITORY_SERVER_REQUIREMENTS.md are covered

---

*Generated from Event Storming session on February 18, 2026*
