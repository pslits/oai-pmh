# OAI-PMH Repository Server - Requirements Document

**Document Version:** 1.0  
**Date:** February 10, 2026  
**Project:** OAI-PMH Repository Server  
**Status:** Requirements Approved  
**License:** MIT License

---

## Executive Summary

This document defines the requirements for developing an OAI-PMH (Open Archives Initiative Protocol for Metadata Harvesting) repository server. The server will be a reusable, open-source solution designed to serve very large datasets (5M+ records) with high performance, extensibility, and standards compliance.

The server will be built on top of the existing OAI-PMH value objects library (Domain-Driven Design foundation) and will serve as the application layer that implements the OAI-PMH 2.0 protocol with enterprise-grade features including security, caching, monitoring, and plugin architecture.

---

## 1. Project Vision & Objectives

### 1.1 Vision Statement
Create a high-performance, enterprise-ready OAI-PMH repository server that can be deployed by any organization to expose their metadata collections for harvesting, with a focus on datasets and research data repositories.

### 1.2 Primary Objectives
1. **Reusability:** Provide a turnkey solution that any organization can deploy without custom development
2. **Scalability:** Support very large repositories (5M+ records) with high performance (<1s response time)
3. **Flexibility:** Enable customization through a comprehensive plugin architecture
4. **Standards Compliance:** Strict adherence to OAI-PMH 2.0 specification and PHP best practices
5. **Operational Excellence:** Production-ready with monitoring, logging, caching, and resilience

### 1.3 Target Audience
- **Primary Users:**
  - Repository administrators deploying and configuring the server
  - Software developers/integrators customizing and extending functionality
  - Harvesters (automated clients) consuming metadata via OAI-PMH protocol
  
- **Secondary Users:**
  - End users (researchers) accessing metadata indirectly through harvesters
  - Content providers/curators contributing metadata to repositories

---

## 2. Functional Requirements

### 2.1 OAI-PMH Protocol Implementation

#### 2.1.1 Required OAI-PMH Verbs
The server MUST implement all six OAI-PMH 2.0 protocol verbs:

| Verb | Description | Priority |
|------|-------------|----------|
| **Identify** | Return repository information (name, baseURL, protocol version, etc.) | MUST HAVE (MVP) |
| **ListMetadataFormats** | List available metadata formats | MUST HAVE (MVP) |
| **ListSets** | List organizational sets/collections | MUST HAVE (MVP) |
| **ListIdentifiers** | List record identifiers (headers only) | MUST HAVE (MVP) |
| **ListRecords** | List full records with metadata | MUST HAVE (MVP) |
| **GetRecord** | Retrieve a single record by identifier | MUST HAVE (MVP) |

**Acceptance Criteria:**
- [ ] All verbs return valid OAI-PMH 2.0 XML responses
- [ ] Response validation against OAI-PMH XML schema passes
- [ ] Error handling complies with OAI-PMH error codes (badArgument, badVerb, cannotDisseminateFormat, etc.)
- [ ] XML namespaces correctly declared
- [ ] UTF-8 encoding enforced

#### 2.1.2 Deleted Records Support
**Requirement:** The server MUST support tracking of deleted records.

**Functional Details:**
- Repository administrator MUST be able to configure deleted record policy:
  - `no`: Repository does not maintain deletion information
  - `transient`: Repository maintains deletion info temporarily
  - `persistent`: Repository maintains complete deletion history indefinitely
- When policy is `transient` or `persistent`:
  - Deleted records MUST appear in ListIdentifiers/ListRecords with `status="deleted"` attribute
  - Deleted records MUST include datestamp of deletion
  - GetRecord for a deleted record MUST return the deleted record header

**Acceptance Criteria:**
- [ ] Configuration allows setting deletedRecord policy
- [ ] Identify response includes correct deletedRecord value
- [ ] Deleted records returned with status="deleted" when applicable
- [ ] Database schema supports storing deletion status and datestamp

#### 2.1.3 Sets (Organizational Hierarchy)
**Requirement:** The server MUST support OAI-PMH sets for organizing records hierarchically.

**Functional Details:**
- Records MAY belong to zero or more sets
- Sets MAY be hierarchical (e.g., `dataset:climate`, `dataset:climate:temperature`)
- Sets MUST have:
  - `setSpec`: unique identifier (e.g., `biology`)
  - `setName`: human-readable name (e.g., "Biology Department Collection")
  - `setDescription` (optional): Dublin Core or custom description
- ListSets verb MUST return all available sets
- ListRecords/ListIdentifiers MUST support `set` parameter for selective harvesting

**Acceptance Criteria:**
- [ ] Database schema supports set definitions and record-to-set mappings
- [ ] ListSets returns all sets with spec, name, and optional description
- [ ] ListRecords/ListIdentifiers filter by set parameter
- [ ] Support for hierarchical sets (colon-separated setSpec)
- [ ] Configuration defines set structure (database mapping or configuration file)

#### 2.1.4 Selective Harvesting (Date-based Filtering)
**Requirement:** The server MUST support selective harvesting using date ranges.

**Functional Details:**
- ListRecords and ListIdentifiers MUST support:
  - `from` parameter: harvest records modified on or after this date
  - `until` parameter: harvest records modified on or before this date
- Dates MUST be in UTC (ISO 8601 format)
- Granularity MUST be configurable:
  - `YYYY-MM-DD` (day-level granularity)
  - `YYYY-MM-DDThh:mm:ssZ` (second-level granularity)
- Repository MUST declare granularity in Identify response
- Records MUST have a `datestamp` indicating last modification

**Acceptance Criteria:**
- [ ] from/until parameters correctly filter results
- [ ] Date validation returns badArgument error for invalid dates
- [ ] Granularity configuration enforced
- [ ] Datestamp stored and returned for all records
- [ ] Edge cases handled (from > until, future dates, etc.)

#### 2.1.5 Flow Control (Resumption Tokens)
**Requirement:** The server MUST implement resumption tokens for paginating large result sets.

**Functional Details:**
- ListRecords, ListIdentifiers, and ListSets MUST support resumption tokens when result set exceeds configurable limit
- Resumption token MUST encapsulate:
  - Current position in result set
  - Original query parameters (metadataPrefix, from, until, set)
  - Expiration timestamp
- Resumption token MUST include:
  - `resumptionToken` element with token value
  - Optional attributes: `expirationDate`, `completeListSize`, `cursor`
- Expired tokens MUST return `badResumptionToken` error
- Token lifetime MUST be configurable (default: 24 hours)

**Acceptance Criteria:**
- [ ] Resumption tokens generated when results exceed page size
- [ ] Tokens correctly restore query context
- [ ] Tokens expire after configured lifetime
- [ ] completeListSize and cursor returned when available
- [ ] badResumptionToken error for invalid/expired tokens
- [ ] Token storage (database, cache, or stateless signed tokens)

### 2.2 Metadata Format Support

#### 2.2.1 Metadata Format Architecture
**Requirement:** The server MUST support multiple metadata formats through a plugin architecture.

**Functional Details:**
- Plugin system for registering metadata formats
- Each format plugin MUST define:
  - `metadataPrefix`: unique identifier (e.g., `oai_dc`, `datacite`)
  - `schema`: XML schema URL
  - `metadataNamespace`: XML namespace URI
  - Serialization logic: transform database records to XML
- Format plugins MUST be configurable (enable/disable per repository)
- Default format: `oai_dc` (Dublin Core) recommended but custom formats supported

**Acceptance Criteria:**
- [ ] Plugin interface for metadata format handlers
- [ ] Format registration system
- [ ] ListMetadataFormats returns all enabled formats
- [ ] GetRecord/ListRecords serialize using selected format
- [ ] cannotDisseminateFormat error when format not supported for a record

#### 2.2.2 Custom Metadata Formats
**Requirement:** The server MUST support custom metadata formats for datasets and specialized collections.

**Functional Details:**
- Repository administrators MUST be able to define custom metadata schemas
- Custom format plugins MUST implement standard interface
- Documentation MUST provide examples for creating custom format plugins
- Custom formats MAY be record-specific (some records only available in certain formats)

**Acceptance Criteria:**
- [ ] Example custom format plugin in documentation
- [ ] Plugin loading mechanism (Composer autoload, PSR-4)
- [ ] Custom format validation (XML schema compliance)

### 2.3 Data Source & Storage Architecture

#### 2.3.1 Database-Driven Architecture
**Requirement:** The server MUST support database-driven repositories (MySQL, PostgreSQL).

**Functional Details:**
- Primary data source: relational database (MySQL 5.7+, PostgreSQL 10+)
- Doctrine DBAL recommended for database abstraction
- Support for both direct database schema and cached/optimized schema
- Connection configuration via configuration files (database host, credentials, etc.)

**Acceptance Criteria:**
- [ ] MySQL support (5.7+)
- [ ] PostgreSQL support (10+)
- [ ] Database connection configuration
- [ ] Connection pooling and error handling
- [ ] Database-specific optimizations (indexes, query optimization)

#### 2.3.2 Configurable Database Schema Mapping
**Requirement:** The server MUST support flexible mapping between database schema and OAI-PMH data model.

**Functional Details:**
- **Adapter Pattern:** Abstract interface for data repositories
- **Configuration-Driven Mapping:** Configuration files define:
  - Which tables/views contain records
  - Field mappings (identifier, datestamp, setSpec, deleted status)
  - Metadata field mappings per format
  - SQL queries or ORM mappings
- **Multiple Mapping Strategies:**
  - Direct table mapping (simple repositories)
  - View-based mapping (complex queries)
  - Stored procedure support
  - Multi-table joins
- **Use Case Examples:**
  - DSpace: Map DSpace database tables to OAI-PMH model
  - EPrints: Map EPrints database to OAI-PMH model
  - Custom: Define mapping for any relational schema

**Acceptance Criteria:**
- [ ] Configuration schema for database mapping (YAML or PHP)
- [ ] Repository adapter interface
- [ ] Example mappings for common repository platforms (DSpace, EPrints as documentation examples)
- [ ] Validation of mapping configuration
- [ ] Support for custom SQL queries
- [ ] Lazy loading and query optimization

**Example Configuration Structure:**
```yaml
database:
  driver: mysql
  host: localhost
  database: repository_db
  username: oai_user
  password: secret

mapping:
  record_table: items
  identifier_field: item_id
  datestamp_field: last_modified
  deleted_field: is_deleted
  
  sets:
    table: collections
    spec_field: collection_code
    name_field: collection_name
    mapping_table: item_collections
    
  metadata_formats:
    oai_dc:
      fields:
        title: dc_title
        creator: dc_creator
        subject: dc_subject
        # ... more fields
```

#### 2.3.3 Integration with Existing Repository Systems
**Requirement:** The server SHOULD provide documentation and examples for integrating with common repository platforms.

**Platforms to Document:**
- DSpace (via database mapping)
- EPrints (via database mapping)
- Custom databases

**Functional Details:**
- Reference implementations/examples in documentation
- Migration guides for common platforms
- No platform-specific code in core server (achieved via flexible mapping)

**Acceptance Criteria:**
- [ ] Documentation includes DSpace mapping example
- [ ] Documentation includes EPrints mapping example
- [ ] How-to guide for creating custom mappings

### 2.4 Security & Access Control

#### 2.4.1 Authentication & Authorization
**Requirement:** The server MUST support multiple authentication mechanisms through a plugin architecture.

**Supported Authentication Methods:**

| Method | Description | Priority |
|--------|-------------|----------|
| **Public (None)** | Open access, no authentication | MUST HAVE (MVP) |
| **HTTP Basic Auth** | Username/password authentication | SHOULD HAVE |
| **API Keys/Tokens** | Token-based authentication | SHOULD HAVE |
| **Enterprise SSO** | OAuth2, SAML, LDAP integration | NICE TO HAVE |

**Functional Details:**
- Authentication plugins implement standard interface
- Multiple authentication methods MAY be enabled simultaneously
- Configuration defines authentication requirements per endpoint/verb
- Failed authentication returns HTTP 401 Unauthorized

**Acceptance Criteria:**
- [ ] Plugin interface for authentication providers
- [ ] Public access supported (no auth)
- [ ] HTTP Basic Auth implementation
- [ ] API key/token implementation
- [ ] Configuration for authentication requirements
- [ ] Documentation for implementing SSO plugins

#### 2.4.2 Rate Limiting
**Requirement:** The server MUST support rate limiting to prevent abuse and ensure fair resource usage.

**Functional Details:**
- Rate limiting by:
  - IP address
  - API key/user
  - Combination of both
- Configurable limits:
  - Requests per minute
  - Requests per hour
  - Requests per day
- Rate limit headers in responses:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests in period
  - `X-RateLimit-Reset`: Time when limit resets (Unix timestamp)
- HTTP 429 Too Many Requests when limit exceeded
- Retry-After header indicating when to retry

**Acceptance Criteria:**
- [ ] Rate limiting middleware
- [ ] Configuration for rate limits (per IP, per key)
- [ ] Rate limit headers in responses
- [ ] HTTP 429 response when exceeded
- [ ] Storage backend for rate limit counters (Redis recommended)

#### 2.4.3 Record-Level Access Control
**Requirement:** The server SHOULD support record-level access control for sensitive metadata.

**Functional Details:**
- Records MAY be marked as restricted/private
- Access control rules define which users/IPs can access restricted records
- Unauthenticated users see only public records
- Authenticated users see public + authorized restricted records
- GetRecord returns error (idDoesNotExist or cannotDisseminateFormat) for unauthorized records
- ListRecords/ListIdentifiers exclude unauthorized records from results

**Acceptance Criteria:**
- [ ] Database schema supports access control flags
- [ ] Configuration defines access control rules
- [ ] Access control enforced in all OAI-PMH verbs
- [ ] Unauthorized records excluded from lists
- [ ] Appropriate error codes for unauthorized access

### 2.5 Configuration Management

#### 2.5.1 Configuration System
**Requirement:** The server MUST be configured entirely through configuration files (no admin UI in MVP).

**Configuration Format:**
- YAML or PHP-based configuration (YAML recommended for readability)
- Configuration sections:
  - Repository identity (name, baseURL, admin email, etc.)
  - Database connection
  - Database schema mapping
  - Metadata formats (enabled formats, plugins)
  - Sets configuration
  - Deleted records policy
  - Resumption token settings (page size, expiration)
  - Caching configuration
  - Security settings (authentication, rate limiting, access control)
  - Logging configuration
  - Performance tuning (query limits, timeouts)

**Acceptance Criteria:**
- [ ] Configuration file schema defined and documented
- [ ] Configuration validation on server startup
- [ ] Clear error messages for invalid configuration
- [ ] Sample configuration files provided
- [ ] Environment variable support for sensitive values (database passwords, API keys)

**Example Configuration Structure:**
```yaml
# Repository Identity (OAI-PMH Identify response)
repository:
  name: "Example Dataset Repository"
  base_url: "https://repository.example.org/oai"
  admin_email: "admin@example.org"
  earliest_datestamp: "2000-01-01T00:00:00Z"
  deleted_record: "persistent"
  granularity: "YYYY-MM-DDThh:mm:ssZ"
  compression: ["gzip", "deflate"]
  protocol_version: "2.0"
  
  descriptions:
    - type: oai-identifier
      scheme: "oai"
      repository_identifier: "example.org"
      delimiter: ":"
      sample_identifier: "oai:example.org:12345"

# Database Configuration
database:
  driver: mysql
  host: localhost
  port: 3306
  database: repository_db
  username: oai_user
  password: ${DB_PASSWORD} # Environment variable
  charset: utf8mb4
  
# Schema Mapping (see 2.3.2 for details)
mapping:
  # ... (see previous example)

# Metadata Formats
metadata_formats:
  - prefix: oai_dc
    namespace: http://www.openarchives.org/OAI/2.0/oai_dc/
    schema: http://www.openarchives.org/OAI/2.0/oai_dc.xsd
    plugin: OaiPmh\Plugins\Formats\DublinCore
    enabled: true
    
  - prefix: datacite
    namespace: http://datacite.org/schema/kernel-4
    schema: http://schema.datacite.org/meta/kernel-4/metadata.xsd
    plugin: OaiPmh\Plugins\Formats\DataCite
    enabled: true

# Resumption Tokens
resumption:
  page_size: 100
  token_lifetime: 86400 # 24 hours in seconds
  storage: redis # or database, file

# Caching
cache:
  enabled: true
  driver: redis # or file, memcached
  host: localhost
  port: 6379
  ttl: 3600 # 1 hour
  cache_identify: true
  cache_list_sets: true
  cache_list_metadata_formats: true

# Security
security:
  authentication:
    enabled: false # Public access in MVP
    providers:
      - type: basic_auth
        enabled: false
      - type: api_key
        enabled: false
        
  rate_limiting:
    enabled: true
    by_ip:
      requests_per_minute: 60
      requests_per_hour: 1000
    by_key:
      requests_per_minute: 600
      requests_per_hour: 10000
    storage: redis

# Logging
logging:
  enabled: true
  level: info # debug, info, warning, error
  format: json # or text
  file: /var/log/oai-pmh/app.log
  rotate: daily
  retention_days: 30

# Monitoring
monitoring:
  metrics_enabled: true
  metrics_endpoint: /metrics # Prometheus-compatible
  health_check_endpoint: /health
```

#### 2.5.2 Environment-Based Configuration
**Requirement:** The server SHOULD support environment-specific configuration overrides.

**Functional Details:**
- Environment variable substitution in configuration files
- Separate config files per environment (development, staging, production)
- Config file precedence: default.yaml < environment.yaml < environment variables

**Acceptance Criteria:**
- [ ] Environment variable substitution (e.g., `${DB_PASSWORD}`)
- [ ] Environment-specific config files
- [ ] Documentation for deployment configurations

---

## 3. Non-Functional Requirements

### 3.1 Performance Requirements

#### 3.1.1 Response Time
**Requirement:** The server MUST deliver high performance for typical and large-scale operations.

**Performance Targets:**

| Operation | Target Response Time | Notes |
|-----------|---------------------|-------|
| **Identify** | < 100ms | Simple query, cacheable |
| **ListMetadataFormats** | < 100ms | Simple query, cacheable |
| **ListSets** | < 500ms | Depends on set count, cacheable |
| **GetRecord** | < 500ms | Single record retrieval |
| **ListIdentifiers** (first page) | < 1s | Initial query, 100 records |
| **ListRecords** (first page) | < 1s | Initial query, 100 records, includes metadata |
| **Resumption Token** | < 500ms | Subsequent pages |

**Load Targets:**
- **Concurrent Requests:** Handle 100+ requests per minute
- **Dataset Size:** Support repositories with 5M+ records
- **Scalability:** Linear or better scaling with database size (proper indexing)

**Acceptance Criteria:**
- [ ] Performance tests verify targets under load
- [ ] Database indexes optimized (identifier, datestamp, setSpec)
- [ ] Query optimization (EXPLAIN analysis, avoid N+1 queries)
- [ ] Caching used for static/slow queries
- [ ] Connection pooling reduces database overhead

#### 3.1.2 Scalability
**Requirement:** The server architecture MUST support horizontal and vertical scaling.

**Functional Details:**
- **Horizontal Scaling:** Multiple server instances behind load balancer
- **Stateless Design:** Resumption tokens and sessions stored in shared cache (Redis)
- **Database Scaling:** Support read replicas for query distribution
- **Caching Strategy:** Redis or Memcached for distributed caching

**Acceptance Criteria:**
- [ ] Stateless application design
- [ ] Shared cache for resumption tokens
- [ ] Load testing with multiple instances
- [ ] Documentation for scaling strategies

### 3.2 Reliability & Resilience

#### 3.2.1 Error Handling
**Requirement:** The server MUST gracefully handle errors and provide meaningful feedback.

**Functional Details:**
- **OAI-PMH Errors:** Return proper error codes per specification
  - badArgument, badResumptionToken, badVerb, cannotDisseminateFormat, idDoesNotExist, noRecordsMatch, noMetadataFormats, noSetHierarchy
- **HTTP Errors:** Proper HTTP status codes (400, 401, 404, 429, 500, 503)
- **Database Errors:** Graceful degradation when database unavailable
- **Validation Errors:** Clear error messages for configuration validation
- **Logging:** All errors logged with context (request ID, user, timestamp)

**Acceptance Criteria:**
- [ ] OAI-PMH error responses validate against schema
- [ ] HTTP error codes appropriate for error type
- [ ] Database connection errors return HTTP 503 Service Unavailable
- [ ] Error messages do not expose sensitive information (stack traces in production)
- [ ] Structured error logging (JSON format recommended)

#### 3.2.2 Fault Tolerance
**Requirement:** The server SHOULD implement resilience patterns for external dependencies.

**Functional Details:**
- **Database Connection:** Automatic retry with exponential backoff
- **Cache Failures:** Degrade gracefully (bypass cache, don't fail request)
- **Timeout Configuration:** Configurable timeouts for database queries
- **Circuit Breaker:** Optional circuit breaker for database connections

**Acceptance Criteria:**
- [ ] Database connection retry logic
- [ ] Cache failures don't crash application
- [ ] Query timeout configuration
- [ ] Graceful degradation documented

### 3.3 Operational Requirements

#### 3.3.1 Logging
**Requirement:** The server MUST provide comprehensive, structured logging for operations and debugging.

**Logging Requirements:**
- **Structured Logging:** JSON format for machine-parseable logs
- **Log Levels:** DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Log Content:**
  - Request logging: verb, parameters, response time, status code
  - Error logging: error type, message, stack trace, context
  - Security logging: authentication attempts, rate limit violations
  - Performance logging: slow queries, cache hit/miss rates
- **Log Destinations:** File, syslog, or external services (ELK stack compatible)
- **Log Rotation:** Automatic rotation and retention policies
- **Privacy:** Sensitive data (passwords, tokens) excluded from logs

**Acceptance Criteria:**
- [ ] Structured JSON logging
- [ ] Configurable log levels
- [ ] Request/response logging
- [ ] Error logging with context
- [ ] Log rotation configuration
- [ ] GDPR compliance (no PII in logs without consent)

#### 3.3.2 Monitoring & Metrics
**Requirement:** The server MUST expose metrics and health checks for monitoring.

**Metrics Endpoint:**
- **Format:** Prometheus-compatible metrics endpoint (`/metrics`)
- **Metrics to Expose:**
  - Request count by verb
  - Response time histograms by verb
  - Error count by type
  - Database query count and duration
  - Cache hit/miss rates
  - Rate limit violations
  - Resumption token usage
  - Record count by set and metadata format

**Health Check Endpoint:**
- **Format:** JSON response at `/health`
- **Health Checks:**
  - Database connectivity
  - Cache connectivity (if enabled)
  - Disk space (for logs, cache files)
  - Overall status: healthy, degraded, unhealthy

**Example Health Response:**
```json
{
  "status": "healthy",
  "timestamp": "2026-02-10T12:34:56Z",
  "checks": {
    "database": { "status": "healthy", "response_time_ms": 5 },
    "cache": { "status": "healthy", "response_time_ms": 1 },
    "disk": { "status": "healthy", "free_space_gb": 120 }
  }
}
```

**Acceptance Criteria:**
- [ ] Prometheus metrics endpoint implemented
- [ ] Metrics cover all key operations
- [ ] Health check endpoint implemented
- [ ] Health checks verify all critical dependencies
- [ ] Documentation for monitoring setup (Grafana dashboards, alerts)

#### 3.3.3 Caching Strategy
**Requirement:** The server MUST implement intelligent caching to improve performance.

**Cacheable Operations:**
- **Identify Response:** Cache indefinitely (invalidate on config change)
- **ListMetadataFormats:** Cache indefinitely (invalidate on format changes)
- **ListSets:** Cache with configurable TTL (e.g., 1 hour)
- **Record Metadata:** Cache individual records with configurable TTL
- **Query Results:** Cache first page of ListRecords/ListIdentifiers

**Cache Backends:**
- Redis (recommended for production)
- Memcached (alternative)
- File-based cache (development/single-server)
- APCu (in-memory PHP cache for single server)

**Cache Invalidation:**
- TTL-based expiration
- Manual invalidation via CLI command
- Event-driven invalidation (when records updated)

**Acceptance Criteria:**
- [ ] Cache configuration (driver, TTL, hosts)
- [ ] Identify and ListMetadataFormats cached
- [ ] Optional caching for List operations
- [ ] Cache invalidation mechanism
- [ ] Cache bypass for debugging (query parameter or header)

#### 3.3.4 Background Job Processing
**Requirement:** The server SHOULD support background job processing for heavy operations.

**Use Cases:**
- Pre-building large result sets
- Database indexing and optimization
- Cache warming
- Batch record updates
- Analytics and reporting

**Implementation:**
- Job queue system (e.g., Symfony Messenger, Laravel Queues, or standalone queue)
- Worker processes consume jobs
- Job status tracking
- Retry logic for failed jobs

**Acceptance Criteria:**
- [ ] Job queue system integrated
- [ ] Example background jobs (cache warming, indexing)
- [ ] Worker process management (systemd, Supervisor)
- [ ] Documentation for background job setup

### 3.4 Extensibility & Plugin Architecture

#### 3.4.1 Plugin System
**Requirement:** The server MUST provide a comprehensive plugin/extension system.

**Plugin Types:**

| Plugin Type | Purpose | Interface |
|-------------|---------|-----------|
| **Metadata Format** | Add new metadata formats | `MetadataFormatInterface` |
| **Authentication** | Custom authentication providers | `AuthenticationProviderInterface` |
| **Authorization** | Custom access control rules | `AuthorizationProviderInterface` |
| **Storage Adapter** | Connect to different data sources | `RepositoryAdapterInterface` |
| **Event Listeners** | Pre/post-processing hooks | PSR-14 Event Dispatcher |
| **Cache Backends** | Custom caching strategies | PSR-6 or PSR-16 |

**Plugin Discovery:**
- Composer-based plugin packages
- PSR-4 autoloading
- Plugin registration in configuration
- Dependency injection for plugin instantiation

**Acceptance Criteria:**
- [ ] Plugin interfaces defined
- [ ] Plugin loading and registration system
- [ ] Example plugin for each type
- [ ] Documentation for creating plugins
- [ ] Plugin validation and error handling

#### 3.4.2 Event System (Hooks)
**Requirement:** The server SHOULD provide event hooks for extending behavior.

**Event Types:**
- **Request Events:**
  - `oai.request.before`: Before request processing
  - `oai.request.after`: After response generated
- **Record Events:**
  - `oai.record.before_load`: Before fetching record from database
  - `oai.record.after_load`: After record loaded, before serialization
  - `oai.record.before_serialize`: Before metadata serialization
  - `oai.record.after_serialize`: After metadata XML generated
- **System Events:**
  - `oai.cache.miss`: Cache miss occurred
  - `oai.error`: Error occurred
  - `oai.rate_limit`: Rate limit triggered

**Event Dispatcher:**
- PSR-14 Event Dispatcher standard
- Listeners registered in configuration or via attributes
- Event priority for ordering multiple listeners

**Acceptance Criteria:**
- [ ] PSR-14 Event Dispatcher integration
- [ ] Core events defined and dispatched
- [ ] Example event listener
- [ ] Documentation for event system

### 3.5 Database Migration Support
**Requirement:** The server MUST support versioned database schema migrations.

**Functional Details:**
- Migration system for creating/updating database schema
- Versioned migration files (timestamp or sequential number)
- Up/down migrations for rollback capability
- CLI command to run migrations: `bin/oai-pmh migrate`
- Migration status tracking (which migrations applied)

**Migration Use Cases:**
- Initial schema creation (OAI-PMH metadata tables if not using existing schema)
- Schema updates between versions
- Index creation/optimization
- Data transformations

**Acceptance Criteria:**
- [ ] Migration system integrated (Doctrine Migrations, Phinx, or custom)
- [ ] Initial migration creates required tables
- [ ] CLI command for running migrations
- [ ] Migration status tracking
- [ ] Documentation for creating migrations

---

## 4. Technical Requirements

### 4.1 Technology Stack

#### 4.1.1 Core Technologies
**Required:**
- **PHP Version:** 8.0+ (leverage typed properties, constructor promotion, JIT compiler)
- **Composer:** Dependency management
- **Web Server:** Apache 2.4+ or Nginx 1.18+ (with PHP-FPM)
- **Database:** MySQL 5.7+ or PostgreSQL 10+
- **Cache (Optional but Recommended):** Redis 5.0+ or Memcached 1.5+

**Recommended Libraries:**
- **Database Abstraction:** Doctrine DBAL (database-agnostic queries)
- **HTTP:** PSR-7 HTTP Message (Request/Response), PSR-17 HTTP Factories
- **Dependency Injection:** PSR-11 Container (e.g., Symfony DI, PHP-DI)
- **Event Dispatcher:** PSR-14 Event Dispatcher
- **Logging:** PSR-3 Logger (Monolog recommended)
- **Caching:** PSR-6 Caching or PSR-16 Simple Cache
- **XML:** SimpleXML or XMLWriter for OAI-PMH responses

**Acceptance Criteria:**
- [ ] PHP 8.0+ compatibility verified
- [ ] Composer package created
- [ ] PSR standards compliance (PSR-1, PSR-3, PSR-4, PSR-6/16, PSR-7, PSR-11, PSR-12, PSR-14)
- [ ] Dependency documentation

#### 4.1.2 Development Tools
**Required:**
- **PHPUnit:** 9.6+ for unit and integration testing
- **PHPStan:** Level 8 static analysis
- **PHP_CodeSniffer:** PSR-12 compliance checking
- **Composer Scripts:** Automate testing, linting, analysis

**Recommended:**
- **Xdebug:** Debugging and code coverage
- **PHPBench:** Performance benchmarking
- **Git Hooks:** Pre-commit checks for code quality

**Acceptance Criteria:**
- [ ] PHPUnit test suite (unit, integration, compliance tests)
- [ ] PHPStan Level 8 passes
- [ ] PHP_CodeSniffer PSR-12 compliance passes
- [ ] Composer scripts for all quality checks
- [ ] CI/CD pipeline (GitHub Actions, GitLab CI, etc.)

### 4.2 Architecture & Design Patterns

#### 4.2.1 Domain-Driven Design (DDD)
**Requirement:** The server MUST follow DDD principles, building on the existing value objects library.

**Architectural Layers:**
1. **Domain Layer (Library - Existing):**
   - Value Objects (BaseURL, Email, ProtocolVersion, etc.)
   - Immutable, validated, self-contained
   
2. **Application Layer (New - Server):**
   - OAI-PMH Request Handlers (per verb)
   - Use Cases / Services
   - DTOs (Data Transfer Objects)
   
3. **Infrastructure Layer (New - Server):**
   - Database Repositories (implement RepositoryAdapterInterface)
   - Cache Implementations
   - HTTP Controllers / Entry Points
   - Configuration Loaders
   - Event Dispatcher
   
4. **Presentation Layer (New - Server):**
   - XML Response Serializers
   - HTTP Response Builders
   - Error Response Formatters

**Acceptance Criteria:**
- [ ] Clear separation of concerns between layers
- [ ] Value objects used throughout application
- [ ] Repository pattern for data access
- [ ] Services encapsulate business logic
- [ ] Infrastructure isolated from domain logic

#### 4.2.2 Key Design Patterns
**Required Patterns:**

| Pattern | Purpose | Implementation |
|---------|---------|----------------|
| **Repository** | Data access abstraction | `RepositoryAdapterInterface` for fetching records |
| **Adapter** | Integrate different databases | Database-specific repository adapters |
| **Strategy** | Different metadata serialization | Metadata format plugins |
| **Factory** | Create value objects, responses | Response factories, VO factories |
| **Decorator** | Add caching, logging to repos | Cache decorator, logging decorator |
| **Chain of Responsibility** | Authentication, validation | Middleware chain |
| **Template Method** | Common request handling flow | Abstract request handler |

**Acceptance Criteria:**
- [ ] Repository interface with multiple implementations
- [ ] Strategy pattern for metadata formats
- [ ] Factory classes for complex object creation
- [ ] Decorator pattern for cross-cutting concerns
- [ ] Middleware architecture for request processing

### 4.3 Code Quality & Standards

#### 4.3.1 Coding Standards
**Requirement:** All code MUST adhere to PSR-12 and PHP-FIG standards.

**Standards:**
- **PSR-1:** Basic coding standard
- **PSR-3:** Logger interface
- **PSR-4:** Autoloading standard
- **PSR-6/PSR-16:** Caching interfaces
- **PSR-7:** HTTP message interfaces
- **PSR-11:** Container interface
- **PSR-12:** Extended coding style guide
- **PSR-14:** Event dispatcher

**Additional Standards:**
- Use strict types: `declare(strict_types=1);`
- Type hints for all parameters and return types
- Comprehensive docblocks for public APIs
- Descriptive naming (avoid abbreviations)

**Acceptance Criteria:**
- [ ] PHP_CodeSniffer passes with PSR-12 ruleset
- [ ] All classes use strict types
- [ ] All public methods have docblocks
- [ ] PHPStan Level 8 passes (maximum type safety)

#### 4.3.2 Testing Requirements
**Requirement:** The codebase MUST have comprehensive test coverage.

**Test Types:**

| Test Type | Coverage Target | Tools |
|-----------|----------------|-------|
| **Unit Tests** | 80%+ code coverage | PHPUnit |
| **Integration Tests** | All database adapters | PHPUnit + Test database |
| **OAI-PMH Compliance** | All verbs validated | OAI-PMH Validator |
| **Performance Tests** | Load testing scenarios | PHPBench, ab/wrk |
| **End-to-End Tests** | Critical user flows | PHPUnit + HTTP client |

**Test Quality:**
- BDD-style test names (`testGetRecord_WhenIdentifierValid_ReturnsRecord`)
- Arrange-Act-Assert pattern
- Test fixtures and factories for test data
- Mock external dependencies (database, cache) in unit tests
- Real dependencies in integration tests

**Acceptance Criteria:**
- [ ] 80%+ unit test coverage
- [ ] Integration tests for all adapters
- [ ] OAI-PMH compliance tests pass
- [ ] Performance benchmarks documented
- [ ] CI/CD runs all tests automatically

#### 4.3.3 Reference Implementation / Demo
**Requirement:** The project MUST include a working demo/reference implementation.

**Demo Requirements:**
- Sample dataset (1000+ records)
- All OAI-PMH verbs functional
- Multiple metadata formats demonstrated
- Sets and deleted records examples
- Docker Compose setup for easy deployment
- README with quick-start instructions

**Acceptance Criteria:**
- [ ] Demo repository with sample data
- [ ] Docker Compose configuration
- [ ] One-command demo startup
- [ ] Demo documentation in README

### 4.4 Security Requirements

#### 4.4.1 General Security
**Requirement:** The server MUST follow security best practices.

**Security Measures:**
- **Input Validation:** Validate all OAI-PMH parameters
- **SQL Injection Prevention:** Use parameterized queries (PDO prepared statements, Doctrine DBAL)
- **XSS Prevention:** Escape XML output (automatically handled by XML libraries)
- **Sensitive Data:** No passwords/tokens in logs; use environment variables
- **HTTPS:** Support and recommend HTTPS deployment
- **Dependency Scanning:** Regularly update dependencies for security patches

**Acceptance Criteria:**
- [ ] All inputs validated (type, format, allowed values)
- [ ] Parameterized database queries throughout
- [ ] No sensitive data in error messages or logs
- [ ] HTTPS configuration documented
- [ ] Dependency security scanning in CI/CD

#### 4.4.2 GDPR & Privacy Compliance
**Requirement:** The server SHOULD be GDPR-compliant for European deployments.

**GDPR Considerations:**
- **Logging:** Avoid logging personal data (IP addresses configurable)
- **Access Control:** Support restricting access to sensitive records
- **Data Retention:** Configuration for log retention and record retention policies
- **Right to be Forgotten:** Support for deleting/redacting records

**Acceptance Criteria:**
- [ ] Configuration for IP logging (enable/disable)
- [ ] Log retention policies configurable
- [ ] Documentation for GDPR compliance
- [ ] Record deletion/redaction support

---

## 5. Deployment & Installation Requirements

### 5.1 Distribution & Packaging

#### 5.1.1 Composer Package
**Requirement:** The server MUST be distributed as a Composer package.

**Package Requirements:**
- **Package Name:** `pslits/oai-pmh-server` (or similar)
- **Dependencies:**
  - `pslits/oai-pmh` (the value objects library)
  - PHP 8.0+
  - Database drivers (PDO MySQL, PDO PostgreSQL)
  - Recommended: Redis extension, cURL
- **Autoloading:** PSR-4 autoloading
- **Semantic Versioning:** Follow SemVer (MAJOR.MINOR.PATCH)
- **Packagist:** Published on Packagist.org

**Acceptance Criteria:**
- [ ] composer.json properly configured
- [ ] Package published on Packagist
- [ ] Installation via `composer require pslits/oai-pmh-server`
- [ ] Dependency versions documented

#### 5.1.2 Installation Methods
**Requirement:** The server SHOULD support multiple installation methods.

**Supported Methods:**

1. **Composer Install (Manual):**
   ```bash
   composer create-project pslits/oai-pmh-server my-repository
   cd my-repository
   cp config/config.example.yaml config/config.yaml
   # Edit config.yaml
   php bin/oai-pmh migrate
   php -S localhost:8080 -t public/
   ```

2. **CLI Installer (Interactive):**
   ```bash
   composer global require pslits/oai-pmh-server
   oai-pmh-server install
   # Interactive prompts for configuration
   ```

3. **Docker Compose:**
   ```bash
   git clone https://github.com/pslits/oai-pmh-server
   cd oai-pmh-server
   docker-compose up -d
   ```

**Acceptance Criteria:**
- [ ] Manual composer installation documented
- [ ] CLI installer implemented
- [ ] Docker Compose configuration provided
- [ ] All methods documented with examples

### 5.2 Documentation Requirements

#### 5.2.1 User Documentation
**Requirement:** The project MUST include comprehensive documentation for all audiences.

**Documentation Types:**

| Document | Audience | Content |
|----------|----------|---------|
| **README.md** | All users | Overview, quick start, links to docs |
| **Install Guide** | Administrators | Step-by-step installation, configuration |
| **Configuration Guide** | Administrators | All config options explained |
| **OAI-PMH Endpoint Docs** | Harvesters | How to harvest from this repository |
| **Developer Guide** | Developers | Architecture, extending, contributing |
| **API Reference** | Developers | All classes, interfaces, methods |
| **How-To Guides** | All users | Common tasks (add format, map database, etc.) |
| **Troubleshooting** | Administrators | Common issues, debugging |
| **Migration Guide** | Administrators | Migrate from other OAI-PMH servers |

**Documentation Format:**
- Markdown for all documentation (easy to read, version control)
- API reference generated from docblocks (phpDocumentor or similar)
- Hosted on GitHub Pages or ReadTheDocs

**Acceptance Criteria:**
- [ ] All documentation types present
- [ ] Code examples in documentation tested
- [ ] Screenshots/diagrams where helpful
- [ ] Documentation versioned with releases
- [ ] Contribution guide for documentation

#### 5.2.2 Developer Documentation
**Requirement:** Developer documentation MUST explain architecture and extension points.

**Required Content:**
- **Architecture Overview:** Layers, patterns, component diagram
- **Plugin Development:** How to create each plugin type
- **Database Adapter Guide:** How to create custom adapters
- **Event System:** Available events, creating listeners
- **Testing Guide:** Running tests, writing new tests
- **Code Standards:** PSR compliance, conventions
- **Contributing Guide:** How to contribute code, documentation

**Acceptance Criteria:**
- [ ] Architecture diagram (UML or similar)
- [ ] Plugin tutorial with complete example
- [ ] Database adapter example (custom platform)
- [ ] Event listener example
- [ ] Contributing guide (CONTRIBUTING.md)

### 5.3 Migration & Upgrade Support

#### 5.3.1 Migration from Other OAI-PMH Servers
**Requirement:** The project SHOULD provide migration utilities for common OAI-PMH servers.

**Migration Targets:**
- **DSpace OAI-PMH:** Migrate configuration and metadata
- **EPrints OAI-PMH:** Import EPrints repository structure
- **Custom OAI-PMH Servers:** Generic migration guide

**Migration Utilities:**
- CLI command: `php bin/oai-pmh migrate:from <platform>`
- Automated configuration generation
- Data validation and testing post-migration

**Acceptance Criteria:**
- [ ] Migration guide for DSpace
- [ ] Migration guide for EPrints
- [ ] CLI migration helper tool
- [ ] Post-migration validation checklist

#### 5.3.2 Version Upgrade Path
**Requirement:** The project MUST support seamless upgrades between versions.

**Upgrade Process:**
1. Backup configuration and database
2. Update Composer dependencies: `composer update pslits/oai-pmh-server`
3. Run database migrations: `php bin/oai-pmh migrate`
4. Review breaking changes in CHANGELOG.md
5. Test OAI-PMH endpoints

**Acceptance Criteria:**
- [ ] CHANGELOG.md with all breaking changes
- [ ] Upgrade guide for major versions
- [ ] Database migrations for schema changes
- [ ] Backward compatibility policy documented

---

## 6. Minimum Viable Product (MVP) Scope

### 6.1 MVP Features (v1.0)

**MUST HAVE for MVP:**
- [ ] All six OAI-PMH verbs implemented (Identify, ListMetadataFormats, ListSets, ListIdentifiers, ListRecords, GetRecord)
- [ ] MySQL adapter (single database adapter)
- [ ] PostgreSQL adapter (second database adapter)
- [ ] Configurable database schema mapping
- [ ] Support for at least one metadata format (oai_dc or custom)
- [ ] Configuration system (YAML-based)
- [ ] Resumption tokens (flow control)
- [ ] Basic logging (structured JSON logs)
- [ ] Public access (no authentication required for MVP)
- [ ] Deleted record tracking
- [ ] Sets support
- [ ] Selective harvesting (date ranges)
- [ ] Basic caching (Identify, ListMetadataFormats)
- [ ] Error handling and OAI-PMH error codes
- [ ] Documentation (Installation, Configuration, How-to guides)
- [ ] Unit tests (80%+ coverage)
- [ ] OAI-PMH compliance tests
- [ ] Reference implementation / demo
- [ ] Composer package

**NICE TO HAVE (Post-MVP):**
- [ ] HTTP Basic Auth
- [ ] API key authentication
- [ ] Rate limiting
- [ ] Record-level access control
- [ ] Advanced caching strategies
- [ ] Background job processing
- [ ] Admin UI (web-based configuration)
- [ ] Multiple metadata format plugins
- [ ] SSO integration
- [ ] Grafana dashboards
- [ ] Migration utilities

### 6.2 MVP Success Criteria

**The MVP is considered successful when:**
1. ✅ A harvester can successfully harvest all records from a 100,000-record repository
2. ✅ Response times meet performance targets (<1s for ListRecords first page)
3. ✅ OAI-PMH Validator passes all compliance tests
4. ✅ Documentation allows a developer to install and configure in <1 hour
5. ✅ At least one external organization successfully deploys the server
6. ✅ All automated tests pass (unit, integration, compliance)
7. ✅ PHPStan Level 8 and PSR-12 compliance verified
8. ✅ Demo/reference implementation runs in Docker with sample data

---

## 7. Standards & Compliance

### 7.1 OAI-PMH 2.0 Specification

**Requirement:** The server MUST be fully compliant with OAI-PMH 2.0 specification.

**Specification Reference:**
- **Official Spec:** https://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
- **XML Schema:** https://www.openarchives.org/OAI/2.0/OAI-PMH.xsd

**Compliance Testing:**
- [ ] OAI-PMH Validator: http://www.openarchives.org/data/registerasprovider.html#Protocol_validation
- [ ] All XML responses validate against official schema
- [ ] All error codes per specification
- [ ] Correct HTTP headers (Content-Type: text/xml; charset=UTF-8)
- [ ] UTF-8 encoding enforced

**Acceptance Criteria:**
- [ ] Pass OAI-PMH Repository Validator
- [ ] XML schema validation suite in automated tests
- [ ] Compliance documented and verified

### 7.2 PHP Standards (PSR)

**Requirement:** The codebase MUST follow PHP-FIG PSR standards.

**Required PSRs:**
- **PSR-1:** Basic Coding Standard
- **PSR-3:** Logger Interface
- **PSR-4:** Autoloading Standard
- **PSR-6 or PSR-16:** Caching Interface
- **PSR-7:** HTTP Message Interface
- **PSR-11:** Container Interface
- **PSR-12:** Extended Coding Style Guide
- **PSR-14:** Event Dispatcher

**Acceptance Criteria:**
- [ ] PHP_CodeSniffer with PSR-12 ruleset passes
- [ ] All interfaces from PSR specifications used where applicable
- [ ] Documentation references PSR standards

### 7.3 REST API Standards (Optional)

**Requirement:** If the server exposes additional REST APIs (beyond OAI-PMH), they SHOULD follow REST best practices.

**REST Standards:**
- OpenAPI 3.0 specification for API documentation
- Consistent URL structure (`/api/v1/...`)
- Standard HTTP methods (GET, POST, PUT, DELETE)
- JSON responses with proper Content-Type headers
- API versioning in URL or headers

**Acceptance Criteria:**
- [ ] OpenAPI spec generated (if REST API exposed)
- [ ] RESTful principles followed
- [ ] API versioning strategy documented

### 7.4 Accessibility (WCAG)

**Requirement:** If any web UI is implemented (admin UI or harvesters using browsers), it SHOULD meet WCAG 2.1 Level AA.

**Note:** MVP has no web UI (configuration files only), so this is for future versions.

**Acceptance Criteria (Future):**
- [ ] WCAG 2.1 Level AA compliance
- [ ] Automated accessibility testing (axe-core, Pa11y)
- [ ] Keyboard navigation support
- [ ] Screen reader compatibility

### 7.5 Data Privacy (GDPR)

**Requirement:** The server SHOULD support GDPR compliance for European deployments.

**GDPR Features:**
- [ ] Configurable IP address logging (anonymize or disable)
- [ ] Data retention policies in configuration
- [ ] Documentation for GDPR compliance
- [ ] Support for record deletion ("right to be forgotten")
- [ ] Audit logging for access to sensitive records

**Acceptance Criteria:**
- [ ] GDPR compliance guide in documentation
- [ ] Configuration options for privacy settings
- [ ] Data retention policies configurable

---

## 8. Project Constraints & Assumptions

### 8.1 Constraints

**Technical Constraints:**
- PHP 8.0+ required (no backward compatibility with PHP 7.x)
- Relational database required (NoSQL not supported in MVP)
- Linux/Unix-based deployment recommended (Windows support best-effort)

**Resource Constraints:**
- No specific budget constraints
- No specific deadline constraints
- Development timeline flexible

**Compatibility Constraints:**
- Must work with existing `pslits/oai-pmh` value objects library
- Must support MySQL 5.7+ and PostgreSQL 10+ (earlier versions not supported)

### 8.2 Assumptions

**Deployment Assumptions:**
- Repository administrators have basic Linux/server administration skills
- Database server already exists (server doesn't include database installation)
- Cache server (Redis) is optional and can be deployed separately if needed

**Data Assumptions:**
- Metadata records are already in a relational database
- Records have unique identifiers
- Records have modification timestamps (datestamp)
- Database schema is known and can be mapped

**User Assumptions:**
- Developers extending the server are familiar with PHP and Composer
- Harvesters follow OAI-PMH 2.0 specification
- Repository administrators can edit YAML configuration files

**Performance Assumptions:**
- Database has proper indexes (identifier, datestamp, setSpec)
- Database server has adequate resources (CPU, RAM, storage)
- Network latency between application and database is low (<10ms)

---

## 9. Stakeholder Requirements

### 9.1 Repository Administrators

**Needs:**
- Easy installation and configuration (< 1 hour to deploy)
- Clear documentation with examples
- Minimal maintenance (automated log rotation, health checks)
- Ability to customize without coding (configuration files)
- Monitoring and alerting capabilities

**Acceptance Criteria:**
- [ ] Installation guide allows deployment in under 1 hour
- [ ] Sample configuration covers common scenarios
- [ ] Health check endpoint for monitoring
- [ ] Log rotation configured automatically

### 9.2 Software Developers / Integrators

**Needs:**
- Well-documented architecture and APIs
- Extensibility through plugins (metadata formats, auth, storage)
- Clear examples for common customizations
- Active development and support community

**Acceptance Criteria:**
- [ ] Developer documentation covers all extension points
- [ ] Complete plugin examples for each type
- [ ] API reference generated from code
- [ ] Contributing guide available

### 9.3 Harvesters (OAI-PMH Clients)

**Needs:**
- Standards-compliant OAI-PMH responses
- Reliable service (high uptime)
- Reasonable performance (responses within seconds)
- Clear repository documentation (what metadata formats, sets available)

**Acceptance Criteria:**
- [ ] OAI-PMH 2.0 compliance verified
- [ ] Performance targets met (<1s for typical requests)
- [ ] Endpoint documentation describes available formats and sets
- [ ] Error messages are clear and actionable

### 9.4 Content Providers / Curators

**Needs:**
- Records accurately represented in OAI-PMH
- Metadata quality preserved through harvesting
- Sets reflect organizational structure
- Deleted records properly tracked

**Acceptance Criteria:**
- [ ] Metadata mapping preserves all fields
- [ ] Sets configuration matches repository structure
- [ ] Deleted records tracked according to policy
- [ ] Multiple metadata formats supported for different consumers

---

## 10. Acceptance Criteria Summary

### 10.1 Functional Acceptance

**The server is functionally complete when:**
- [ ] All six OAI-PMH verbs work correctly
- [ ] At least two database adapters implemented (MySQL, PostgreSQL)
- [ ] At least one metadata format supported (oai_dc or custom)
- [ ] Resumption tokens handle large result sets
- [ ] Deleted records tracked and returned correctly
- [ ] Sets organize records hierarchically
- [ ] Selective harvesting filters by date range
- [ ] Configuration system handles all settings
- [ ] Logging records all requests and errors
- [ ] Error handling returns proper OAI-PMH errors

### 10.2 Technical Acceptance

**The codebase is technically acceptable when:**
- [ ] PHPUnit tests achieve 80%+ coverage
- [ ] PHPStan Level 8 passes with zero errors
- [ ] PHP_CodeSniffer PSR-12 compliance verified
- [ ] OAI-PMH Validator passes all compliance tests
- [ ] Performance tests verify <1s response times
- [ ] Load tests verify 100+ req/min capacity
- [ ] All PSR interfaces used correctly
- [ ] Documentation is complete and accurate

### 10.3 Operational Acceptance

**The server is operationally ready when:**
- [ ] Demo/reference implementation runs successfully
- [ ] Docker Compose deployment works
- [ ] Health check endpoint returns system status
- [ ] Metrics endpoint exposes Prometheus metrics
- [ ] Logs are structured (JSON) and rotated
- [ ] Cache improves performance measurably
- [ ] Error recovery handles database failures gracefully
- [ ] Installation guide verified by external tester

### 10.4 Documentation Acceptance

**Documentation is complete when:**
- [ ] Installation guide covers all deployment methods
- [ ] Configuration guide explains all options
- [ ] Developer guide covers architecture and plugins
- [ ] API reference generated from code
- [ ] How-to guides for common tasks
- [ ] Troubleshooting guide for common issues
- [ ] OAI-PMH endpoint documentation for harvesters
- [ ] All code examples tested and working

---

## 11. Risks & Mitigation Strategies

### 11.1 Technical Risks

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| **Performance with 5M+ records** | High | Medium | Early performance testing; database indexing; caching strategy; query optimization |
| **Database schema complexity** | Medium | Medium | Flexible mapping system; thorough documentation; reference examples |
| **Plugin security vulnerabilities** | High | Low | Plugin validation; security review guidelines; sandboxing; code review |
| **OAI-PMH spec misinterpretation** | Medium | Low | Compliance testing; validator integration; spec references in docs |
| **Resumption token scalability** | Medium | Medium | Stateless tokens (JWT) or distributed cache (Redis); token expiration |

### 11.2 Organizational Risks

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| **Low adoption rate** | High | Medium | Strong documentation; active examples; community building; blog posts; presentations |
| **Lack of contributors** | Medium | Medium | Clear contributing guide; friendly community; good first issues; responsive maintainers |
| **Competing projects** | Low | High | Differentiate: performance, extensibility, modern PHP; partner with complementary projects |

### 11.3 Operational Risks

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| **Database unavailability** | High | Low | Connection retry logic; graceful degradation; health checks; monitoring alerts |
| **Cache failures** | Low | Medium | Cache bypass on failure; fallback to database; monitoring; redundant cache nodes |
| **DDoS / abuse** | Medium | Medium | Rate limiting; IP blocking; monitoring; documentation for sysadmin hardening |

---

## 12. Success Metrics

### 12.1 Technical Metrics

**Performance:**
- [ ] Average response time: <500ms for GetRecord
- [ ] Average response time: <1s for ListRecords (first page)
- [ ] Throughput: > 100 requests/minute sustained
- [ ] Database query optimization: < 10 queries per request average

**Quality:**
- [ ] Code coverage: > 80%
- [ ] PHPStan Level: 8
- [ ] OAI-PMH compliance: 100% (validator passes)
- [ ] PSR-12 compliance: 100%

### 12.2 Adoption Metrics

**Community:**
- [ ] GitHub stars: > 50 within 6 months
- [ ] Packagist downloads: > 500 within 6 months
- [ ] Documented deployments: > 5 organizations within 1 year

**Engagement:**
- [ ] GitHub issues: Active responses within 48 hours
- [ ] Pull requests: Reviewed within 1 week
- [ ] Documentation feedback: Incorporated within 1 month

### 12.3 User Satisfaction

**Feedback:**
- [ ] Installation feedback: "Easy" or "Very Easy" from 80%+ of testers
- [ ] Documentation feedback: "Clear" or "Very Clear" from 80%+ of users
- [ ] Performance feedback: "Fast" or "Very Fast" from 80%+ of users

---

## 13. Project Roadmap

### 13.1 MVP (v1.0) - Estimated: 3-6 months

**Phase 1: Foundation (Weeks 1-4)**
- [ ] Project setup (Composer package, directory structure, CI/CD)
- [ ] Domain model design (interfaces, base classes)
- [ ] Configuration system implementation
- [ ] Database abstraction layer

**Phase 2: Core OAI-PMH (Weeks 5-10)**
- [ ] Request handler architecture
- [ ] All six OAI-PMH verbs implementation
- [ ] XML response generation
- [ ] Error handling

**Phase 3: Data Layer (Weeks 11-14)**
- [ ] MySQL adapter
- [ ] PostgreSQL adapter
- [ ] Database schema mapping
- [ ] Resumption token system

**Phase 4: Metadata & Sets (Weeks 15-18)**
- [ ] Metadata format plugin system
- [ ] oai_dc implementation
- [ ] Sets implementation
- [ ] Deleted records implementation

**Phase 5: Quality & Testing (Weeks 19-22)**
- [ ] Unit tests (80%+ coverage)
- [ ] Integration tests
- [ ] OAI-PMH compliance tests
- [ ] Performance testing
- [ ] Code quality (PHPStan, PHPCS)

**Phase 6: Documentation & Release (Weeks 23-26)**
- [ ] User documentation
- [ ] Developer documentation
- [ ] Reference implementation / demo
- [ ] Migration guides
- [ ] v1.0 release

### 13.2 Post-MVP (v1.1-v2.0)

**v1.1 - Security Enhancements:**
- [ ] HTTP Basic Auth
- [ ] API key authentication
- [ ] Rate limiting
- [ ] Record-level access control

**v1.2 - Performance Optimization:**
- [ ] Advanced caching strategies
- [ ] Query optimization
- [ ] Background job processing
- [ ] Database connection pooling

**v1.3 - Monitoring & Operations:**
- [ ] Enhanced metrics (Prometheus)
- [ ] Grafana dashboards
- [ ] Alerting integration
- [ ] Performance profiling tools

**v2.0 - Enterprise Features:**
- [ ] Multi-tenant support
- [ ] Admin UI (web-based configuration)
- [ ] SSO integration (OAuth2, SAML)
- [ ] Advanced authorization (RBAC)
- [ ] High availability (HA) configuration

---

## 14. Appendices

### Appendix A: OAI-PMH Verbs Quick Reference

| Verb | Required Args | Optional Args | Returns |
|------|---------------|---------------|---------|
| **Identify** | - | - | Repository info |
| **ListMetadataFormats** | - | identifier | Available formats |
| **ListSets** | - | resumptionToken | Available sets |
| **ListIdentifiers** | metadataPrefix | from, until, set, resumptionToken | Record headers |
| **ListRecords** | metadataPrefix | from, until, set, resumptionToken | Full records |
| **GetRecord** | identifier, metadataPrefix | - | Single record |

### Appendix B: OAI-PMH Error Codes

| Error Code | Description | When Returned |
|------------|-------------|---------------|
| **badArgument** | Illegal or missing argument | Invalid parameters |
| **badResumptionToken** | Invalid or expired token | Token not found or expired |
| **badVerb** | Illegal OAI verb | Unknown or missing verb |
| **cannotDisseminateFormat** | Metadata format not supported | Format unavailable for record |
| **idDoesNotExist** | Record identifier unknown | Record not found |
| **noRecordsMatch** | No records match criteria | Empty result set |
| **noMetadataFormats** | No formats available for item | Record has no formats |
| **noSetHierarchy** | Repository doesn't support sets | Sets not configured |

### Appendix C: Example Database Mapping (DSpace)

```yaml
# Example mapping for DSpace 7.x database schema
database:
  driver: postgresql
  host: localhost
  database: dspace
  
mapping:
  record_table: item
  identifier_field: uuid
  datestamp_field: last_modified
  
  # DSpace-specific: Join with metadata tables
  metadata_join:
    table: metadatavalue
    on: item.uuid = metadatavalue.dspace_object_id
    
  sets:
    # Map DSpace collections to OAI sets
    table: collection
    spec_field: handle
    name_field: name
    mapping_table: collection2item
    
  metadata_formats:
    oai_dc:
      mapping:
        title:
          table: metadatavalue
          where: 
            metadata_field_id: 64 # dc.title
        creator:
          table: metadatavalue
          where:
            metadata_field_id: 3 # dc.contributor.author
        # ... additional mappings
```

### Appendix D: Plugin Interface Examples

**Metadata Format Plugin Interface:**
```php
<?php

namespace OaiPmh\Plugin;

interface MetadataFormatInterface
{
    public function getPrefix(): string;
    public function getNamespace(): string;
    public function getSchema(): string;
    public function serialize(RecordInterface $record): string; // Returns XML
    public function supports(RecordInterface $record): bool;
}
```

**Repository Adapter Interface:**
```php
<?php

namespace OaiPmh\Repository;

interface RepositoryAdapterInterface
{
    public function getRecord(string $identifier): ?RecordInterface;
    public function listRecords(array $criteria): RecordCollection;
    public function listIdentifiers(array $criteria): IdentifierCollection;
    public function listSets(): SetCollection;
    public function listMetadataFormats(?string $identifier = null): array;
    public function getEarliestDatestamp(): \DateTimeInterface;
}
```

### Appendix E: Configuration Schema Reference

See complete configuration example in Section 2.5.1.

**Configuration Sections:**
1. Repository Identity (Identify response data)
2. Database Connection
3. Schema Mapping
4. Metadata Formats
5. Resumption Tokens
6. Caching
7. Security (Authentication, Rate Limiting, Access Control)
8. Logging
9. Monitoring

### Appendix F: Glossary

| Term | Definition |
|------|------------|
| **Datestamp** | UTC timestamp of when a record was last modified |
| **Granularity** | Precision of datestamps (day-level or second-level) |
| **Harvester** | Client application that retrieves metadata via OAI-PMH |
| **Identifier** | Unique ID for a record (typically `oai:domain:id` format) |
| **Metadata Format** | Schema for representing record metadata (e.g., Dublin Core) |
| **Metadata Prefix** | Short identifier for a metadata format (e.g., `oai_dc`) |
| **Repository** | Server that exposes metadata via OAI-PMH |
| **Resumption Token** | Opaque token for retrieving next page of results |
| **Set** | Organizational grouping of records (e.g., collection, category) |
| **SetSpec** | Unique identifier for a set |
| **Verb** | OAI-PMH operation (Identify, ListRecords, etc.) |

### Appendix G: References

**OAI-PMH Specification:**
- OAI-PMH 2.0: https://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
- XML Schema: https://www.openarchives.org/OAI/2.0/OAI-PMH.xsd
- Guidelines: https://www.openarchives.org/OAI/2.0/guidelines.htm

**PHP Standards:**
- PHP-FIG PSRs: https://www.php-fig.org/psr/
- PSR-12 Coding Style: https://www.php-fig.org/psr/psr-12/
- PSR-7 HTTP Messages: https://www.php-fig.org/psr/psr-7/
- PSR-14 Event Dispatcher: https://www.php-fig.org/psr/psr-14/

**Tools & Libraries:**
- Doctrine DBAL: https://www.doctrine-project.org/projects/dbal.html
- Monolog: https://github.com/Seldaek/monolog
- PHPUnit: https://phpunit.de/
- PHPStan: https://phpstan.org/

**Repository Platforms:**
- DSpace: https://dspace.lyrasis.org/
- EPrints: https://www.eprints.org/
- Dataverse: https://dataverse.org/
- CKAN: https://ckan.org/

---

## Document Approval

**Prepared by:** GitHub Copilot (Senior Business Analyst)  
**Date:** February 10, 2026  
**Status:** Draft for Review  

**Next Steps:**
1. Review requirements with stakeholders
2. Refine and finalize requirements document
3. Create architectural design document (architect's responsibility)
4. Begin MVP development planning

---

**End of Requirements Document**
