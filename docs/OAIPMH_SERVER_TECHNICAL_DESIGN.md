# OAI-PMH Repository Server - Technical Design & Architecture

**Document Version:** 1.1  
**Date:** 2026-02-10 (Updated: 2026-02-13)  
**Status:** Approved for Development (Enhanced Security)  
**Author:** Solutions Architect  
**Project:** OAI-PMH Repository Server

---

## Executive Summary

This document defines the complete technical architecture for the OAI-PMH Repository Server - a high-performance, enterprise-ready system for exposing metadata collections via the OAI-PMH 2.0 protocol. The architecture is designed for:

- **Scalability**: Support 5M+ record repositories with <1s response times
- **Flexibility**: Work with any existing database schema via configuration
- **Extensibility**: Plugin architecture for custom metadata formats and authentication
- **Maintainability**: Clean layered architecture following Domain-Driven Design
- **Security**: Enhanced multi-layer security with HTTPS enforcement, request validation, and DDoS protection
- **Privacy**: GDPR-compliant with IP anonymization and configurable data retention

### Key Architectural Decisions

All major technical decisions are documented in Architecture Decision Records (ADRs) located in `.github/adr/`:

| ADR | Decision | Impact | Updated |
|-----|----------|--------|---------|
| [ADR-0001](../.github/adr/0001-tech-stack-selection.md) | PHP 8.0+, Doctrine DBAL, PSR standards | Modern features, database flexibility | 2026-02-10 |
| [ADR-0002](../.github/adr/0002-layered-architecture.md) | 4-layer architecture (Domain, Application, Infrastructure, Presentation) | Clean separation, testability | 2026-02-10 |
| [ADR-0003](../.github/adr/0003-database-abstraction.md) | Doctrine DBAL with configurable mapping | Work with any database schema | 2026-02-10 |
| [ADR-0004](../.github/adr/0004-plugin-architecture.md) | PSR-based plugin system | Extensible without forking | 2026-02-10 |
| [ADR-0005](../.github/adr/0005-caching-strategy.md) | Redis-backed multi-layer caching | High performance, horizontal scaling | 2026-02-10 |
| [ADR-0006](../.github/adr/0006-resumption-token-implementation.md) | Stateless JWT tokens | No database dependency, scales | 2026-02-10 |
| [ADR-0007](../.github/adr/0007-security-authentication.md) | Enhanced multi-layer security with HTTPS enforcement, request validation, DDoS protection, security logging | Comprehensive threat protection | **2026-02-13** |
| [ADR-0008](../.github/adr/0008-configuration-management.md) | YAML configuration with environment variables | Easy deployment | 2026-02-10 |
| [ADR-0009](../.github/adr/0009-event-driven-architecture.md) | PSR-14 event dispatcher | Hook-based extensibility | 2026-02-10 |
| [ADR-0010](../.github/adr/0010-xml-serialization.md) | XMLWriter for performance | OAI-PMH compliance, efficiency | 2026-02-10 |
| [ADR-0011](../.github/adr/0011-privacy-gdpr-compliance.md) | Privacy-by-design with IP anonymization and data retention | GDPR compliance, user privacy | **2026-02-13** |

---

## Table of Contents

1. [System Architecture](#1-system-architecture)
2. [Technology Stack](#2-technology-stack)
3. [Data Models](#3-data-models)
4. [API Design](#4-api-design)
5. [Security Architecture](#5-security-architecture)
6. [Performance & Scalability](#6-performance--scalability)
7. [Deployment Architecture](#7-deployment-architecture)
8. [Testing Strategy](#8-testing-strategy)
9. [Monitoring & Observability](#9-monitoring--observability)
10. [Technical Implementation Plan](#10-technical-implementation-plan)

---

## 1. System Architecture

### 1.1 High-Level Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    OAI-PMH HARVESTER CLIENTS                      │
│              (Automated metadata harvesting systems)              │
└────────────────────────────┬──────────────────────────────────────┘
                             │ HTTP/HTTPS (OAI-PMH 2.0 XML)
                             ▼
┌──────────────────────────────────────────────────────────────────┐
│                   LOAD BALANCER (Optional)                        │
│                    (Nginx, HAProxy, AWS ALB)                      │
└────────────────────┬──────────────────┬────────────────────────────┘
                     │                  │
         ┌───────────▼────────┐    ┌───▼──────────────┐
         │  App Server 1      │    │  App Server N    │ Horizontal
         │  (PHP-FPM)         │    │  (PHP-FPM)       │ Scaling
         └───────┬─────────┬──┘    └──┬────────┬──────┘
                 │         │          │        │
                 │         └──────────┴────────┘
                 │                    │
    ┌────────────▼─────────┐   ┌─────▼──────────┐
    │   Redis Cache        │   │   Database     │
    │   (Distributed)      │   │   MySQL/       │
    │   - Response cache   │   │   PostgreSQL   │
    │   - Rate limiting    │   │   - Metadata   │
    │   - Session storage  │   │   - Records    │
    └──────────────────────┘   └────────────────┘
```

### 1.2 Layered Architecture (Clean Architecture / DDD)

```
┌────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                          │
│  ┌──────────────┐  ┌────────────────┐  ┌──────────────────┐  │
│  │   HTTP       │  │   XML          │  │   Middleware     │  │
│  │   Controllers│  │   Serializers  │  │   (Auth, Rate    │  │
│  │              │  │                │  │    Limiting)     │  │
│  └──────────────┘  └────────────────┘  └──────────────────┘  │
└────────────────────────┬───────────────────────────────────────┘
                         │ Uses Application Services
                         ▼
┌────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                            │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  OAI-PMH Verb Handlers                                   │ │
│  │  - IdentifyHandler                                       │ │
│  │  - GetRecordHandler                                      │ │
│  │  - ListRecordsHandler    Use Case Orchestration         │ │
│  │  - ListIdentifiersHandler                                │ │
│  │  - ListSetsHandler                                       │ │
│  │  - ListMetadataFormatsHandler                            │ │
│  └──────────────────────────────────────────────────────────┘ │
│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ Services         │  │ DTOs             │                   │
│  │ - ResumptionToken│  │ - Responses      │                   │
│  │ - FormatRegistry │  │ - Requests       │                   │
│  └──────────────────┘  └──────────────────┘                   │
└────────────┬──────────────────────┬────────────────────────────┘
             │                      │
   ┌─────────▼──────────┐   ┌──────▼────────────┐
   │  DOMAIN LAYER      │   │  INFRASTRUCTURE   │
   │  - Entities        │   │  - Repositories   │
   │    * Record        │   │  - Database       │
   │    * Set           │   │  - Cache          │
   │  - Value Objects   │   │  - Config         │
   │    * Identifier    │   │  - Logging        │
   │    * Datestamp     │   │  - Events         │
   │  - Exceptions      │   │  - Auth Providers │
   │  - Interfaces      │   │  - Plugins        │
   └────────────────────┘   └───────────────────┘
   Pure business logic     Technical implementation
   No dependencies         Depends on Domain
```

### 1.3 Component Interaction Flow

**Example: GetRecord Verb Processing**

```
HTTP Request → Middleware Chain → Controller → Handler → Repository → Database
                   ↓                              ↓           ↓
              [Auth, Rate Limit]            [Format Plugin]  [Cache]
                                                 ↓
                                           XML Serializer
                                                 ↓
HTTP Response ← Response Factory ← XML String ← [Events]
```

**Detailed Flow**:
1. **HTTP Request** arrives at `public/index.php`
2. **PSR-7 Request** created from PHP globals
3. **Middleware Chain** processes request:
   - LoggingMiddleware: Log request details
   - AuthenticationMiddleware: Authenticate (if required)
   - RateLimitingMiddleware: Check rate limits
   - CacheMiddleware: Check cache for response
4. **OaiPmhController** extracts verb parameter
5. **GetRecordHandler** invoked with parameters:
   - Validate identifier and metadataPrefix
   - Fetch record from **RepositoryInterface**
   - **DoctrineRecordRepository** queries database (or cache)
   - **MetadataFormatRegistry** retrieves format plugin
   - **Event**: RecordRetrievedEvent dispatched
6. **XML Serializer** converts Record + Format to OAI-PMH XML
7. **Response Factory** creates PSR-7 Response with XML body
8. **HTTP Response** sent to client

---

## 2. Technology Stack

### 2.1 Core Platform

| Component | Technology | Version | Rationale |
|-----------|------------|---------|-----------|
| **Language** | PHP | 8.0+ | Modern features, JIT compiler, strong typing |
| **Web Server** | Nginx + PHP-FPM | 1.18+ / 8.0+ | High performance, scalable |
| **Database** | MySQL | 5.7+ | Most common in repositories |
| **Database** | PostgreSQL | 10+ | Academic institutions preference |
| **Cache** | Redis | 5.0+ | Distributed cache, rate limiting |
| **Dependency Management** | Composer | 2.0+ | PHP standard |

### 2.2 PHP Libraries

| Library | Version | PSR | Purpose |
|---------|---------|-----|---------|
| **doctrine/dbal** | ^3.0 | - | Database abstraction |
| **nyholm/psr7** | ^1.5 | PSR-7, PSR-17 | HTTP messages |
| **php-di/php-di** | ^7.0 | PSR-11 | Dependency injection |
| **symfony/event-dispatcher** | ^6.0 | PSR-14 | Event system |
| **monolog/monolog** | ^3.0 | PSR-3 | Logging |
| **symfony/cache** | ^6.0 | PSR-6, PSR-16 | Caching |
| **symfony/yaml** | ^6.0 | - | Configuration parsing |
| **guzzlehttp/guzzle** | ^7.0 | PSR-7, PSR-18 | HTTP client (testing) |

### 2.3 Development Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **PHPUnit** | ^9.6 | Unit and integration testing |
| **PHPStan** | ^1.10 | Static analysis (Level 8) |
| **PHP_CodeSniffer** | ^3.7 | PSR-12 compliance |
| **Xdebug** | ^3.0 | Code coverage, debugging |
| **PHPBench** | ^1.2 | Performance benchmarking |

### 2.4 Infrastructure

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Containerization** | Docker | Development and deployment |
| **Orchestration** | Docker Compose | Local development |
| **CI/CD** | GitHub Actions | Automated testing and deployment |
| **Monitoring** | Prometheus + Grafana | Metrics and dashboards |
| **Logging** | ELK Stack (optional) | Centralized log aggregation |

---

## 3. Data Models

### 3.1 Domain Model

#### Record Entity

```php
final class Record
{
    private RecordIdentifier $identifier;
    private UTCdatetime $datestamp;
    private array $setSpecs;          // SetSpec[]
    private bool $deleted;
    private array $metadata;          // Associative array
    
    public function __construct(
        RecordIdentifier $identifier,
        UTCdatetime $datestamp,
        array $setSpecs,
        bool $deleted,
        array $metadata
    ) {
        $this->identifier = $identifier;
        $this->datestamp = $datestamp;
        $this->setSpecs = $setSpecs;
        $this->deleted = $deleted;
        $this->metadata = $metadata;
    }
    
    public function getIdentifier(): RecordIdentifier { return $this->identifier; }
    public function getDatestamp(): UTCdatetime { return $this->datestamp; }
    public function getSetSpecs(): array { return $this->setSpecs; }
    public function isDeleted(): bool { return $this->deleted; }
    public function getMetadata(): array { return $this->metadata; }
    
    public function belongsToSet(SetSpec $setSpec): bool
    {
        foreach ($this->setSpecs as $spec) {
            if ($spec->equals($setSpec)) {
                return true;
            }
        }
        return false;
    }
}
```

#### Set Entity

```php
final class Set
{
    private SetSpec $spec;
    private string $name;
    private ?string $description;
    
    public function __construct(
        SetSpec $spec,
        string $name,
        ?string $description = null
    ) {
        $this->spec = $spec;
        $this->name = $name;
        $this->description = $description;
    }
    
    public function getSpec(): SetSpec { return $this->spec; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    
    public function isHierarchical(): bool
    {
        return str_contains($this->spec->getValue(), ':');
    }
    
    public function getParentSetSpec(): ?SetSpec
    {
        $value = $this->spec->getValue();
        $lastColon = strrpos($value, ':');
        
        if ($lastColon === false) {
            return null;
        }
        
        return new SetSpec(substr($value, 0, $lastColon));
    }
}
```

### 3.2 Database Schema (Reference Implementation)

If OAI-PMH server manages its own schema (not mapping existing database):

```sql
-- Records table
CREATE TABLE oai_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL UNIQUE,
    datestamp DATETIME NOT NULL,
    deleted BOOLEAN NOT NULL DEFAULT FALSE,
    metadata_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_identifier (identifier),
    INDEX idx_datestamp (datestamp),
    INDEX idx_deleted (deleted),
    INDEX idx_compound (datestamp, deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sets table
CREATE TABLE oai_sets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    spec VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    
    INDEX idx_spec (spec)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record-Set junction table (many-to-many)
CREATE TABLE oai_record_sets (
    record_id BIGINT UNSIGNED NOT NULL,
    set_id INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (record_id, set_id),
    FOREIGN KEY (record_id) REFERENCES oai_records(id) ON DELETE CASCADE,
    FOREIGN KEY (set_id) REFERENCES oai_sets(id) ON DELETE CASCADE,
    
    INDEX idx_record (record_id),
    INDEX idx_set (set_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Metadata formats configuration
CREATE TABLE oai_metadata_formats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prefix VARCHAR(50) NOT NULL UNIQUE,
    namespace VARCHAR(255) NOT NULL,
    schema_url VARCHAR(255) NOT NULL,
    plugin_class VARCHAR(255) NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT TRUE,
    
    INDEX idx_prefix (prefix)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**PostgreSQL Variant** (similar schema with PostgreSQL-specific types):
- Use `TIMESTAMPTZ` for datestamp
- Use `JSONB` for metadata_json
- Use `SERIAL` for auto-increment

### 3.3 Configuration Data Model

**YAML Schema** (see ADR-0008 for full example):
```yaml
repository:
  name: string (required)
  base_url: url (required)
  admin_email: email (required)
  earliest_datestamp: datetime (required)
  deleted_record: enum(no, transient, persistent)
  granularity: enum(YYYY-MM-DD, YYYY-MM-DDThh:mm:ssZ)
  
database:
  driver: enum(mysql, pgsql)
  host: string
  port: integer
  database: string
  username: string
  password: string (from env var)
  
mapping:
  record:
    table: string
    identifier_column: string
    datestamp_column: string
    deleted_column: string
  
  sets:
    table: string
    spec_column: string
    name_column: string
    # ...
```

---

## 4. API Design

### 4.1 OAI-PMH Protocol Endpoints

**Base URL**: `https://repository.example.org/oai`

All OAI-PMH requests use a single endpoint with verb parameter.

#### Request Format

```
GET /oai?verb=<verb>&<parameters>
```

#### Response Format

```xml
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
                             http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>2026-02-10T12:00:00Z</responseDate>
  <request verb="<verb>">https://repository.example.org/oai</request>
  <Identify|ListRecords|...>
    <!-- Response content -->
  </Identify|ListRecords|...>
</OAI-PMH>
```

### 4.2 OAI-PMH Verbs

#### Identify

**Purpose**: Return repository information

**Request**: 
```
GET /oai?verb=Identify
```

**Response Elements**:
- `repositoryName`: Repository name
- `baseURL`: OAI-PMH endpoint URL
- `protocolVersion`: "2.0"
- `adminEmail`: Contact email (repeatable)
- `earliestDatestamp`: Earliest record modification date
- `deletedRecord`: Deletion support policy
- `granularity`: Datestamp precision
- `compression`: Supported compression (gzip, deflate)
- `description`: Repository description (optional, repeatable)

#### GetRecord

**Purpose**: Retrieve a single record

**Request**:
```
GET /oai?verb=GetRecord&identifier=<id>&metadataPrefix=<format>
```

**Parameters**:
- `identifier` (required): Unique record identifier
- `metadataPrefix` (required): Metadata format (oai_dc, datacite, etc.)

**Response**: Record with header and metadata

**Errors**:
- `badArgument`: Missing or illegal parameter
- `cannotDisseminateFormat`: Format not supported for this record
- `idDoesNotExist`: Record not found

#### ListRecords

**Purpose**: List records with metadata

**Request**:
```
GET /oai?verb=ListRecords&metadataPrefix=<format>[&from=<date>][&until=<date>][&set=<setSpec>]
```

**Parameters**:
- `metadataPrefix` (required): Metadata format
- `from` (optional): Start date (inclusive)
- `until` (optional): End date (inclusive)
- `set` (optional): Set specifier
- `resumptionToken` (exclusive): Resumption token for pagination

**Response**: List of records with resumption token if more results available

**Errors**:
- `badArgument`: Illegal parameter combination
- `badResumptionToken`: Invalid or expired token
- `cannotDisseminateFormat`: Format not supported
- `noRecordsMatch`: No records found
- `noSetHierarchy`: Repository doesn't support sets

#### ListIdentifiers

**Purpose**: List record headers without metadata

**Request**: Same as ListRecords

**Response**: List of record headers (identifier, datestamp, setSpec, status)

#### ListSets

**Purpose**: List available sets

**Request**:
```
GET /oai?verb=ListSets
```

**Response**: List of sets with spec, name, description

**Errors**:
- `noSetHierarchy`: Repository doesn't support sets

#### ListMetadataFormats

**Purpose**: List available metadata formats

**Request**:
```
GET /oai?verb=ListMetadataFormats[&identifier=<id>]
```

**Parameters**:
- `identifier` (optional): Check formats for specific record

**Response**: List of formats (metadataPrefix, schema, metadataNamespace)

**Errors**:
- `idDoesNotExist`: Record not found
- `noMetadataFormats`: No formats available for record

### 4.3 Error Handling

All errors returned as OAI-PMH error responses:

```xml
<OAI-PMH>
  <responseDate>2026-02-10T12:00:00Z</responseDate>
  <request>https://repository.example.org/oai</request>
  <error code="badArgument">Missing required parameter: metadataPrefix</error>
</OAI-PMH>
```

**HTTP Status Codes**:
- `200 OK`: Successful OAI-PMH response (even for OAI-PMH errors)
- `400 Bad Request`: Malformed request (non-OAI-PMH error)
- `401 Unauthorized`: Authentication required
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error
- `503 Service Unavailable`: Database unavailable

### 4.4 Additional Endpoints (Non-OAI-PMH)

#### Health Check

```
GET /health
```

**Response**:
```json
{
  "status": "healthy",
  "timestamp": "2026-02-10T12:00:00Z",
  "checks": {
    "database": { "status": "healthy", "response_time_ms": 5 },
    "cache": { "status": "healthy", "response_time_ms": 1 }
  }
}
```

#### Metrics (Prometheus)

```
GET /metrics
```

**Response**: Prometheus text format
```
# HELP oai_requests_total Total OAI-PMH requests
# TYPE oai_requests_total counter
oai_requests_total{verb="Identify"} 1542
oai_requests_total{verb="GetRecord"} 89234
```

---

## 5. Security Architecture

**Last Updated:** 2026-02-13 (Enhanced for requirements v1.1)

### 5.1 Enhanced Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│  1. Transport Security (HTTPS/TLS) - ENHANCED                    │
│     • HTTPS enforcement (configurable)                           │
│     • HTTP to HTTPS redirect or 403 rejection                    │
│     • HSTS header support                                        │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  2. Request Size Validation - NEW                                │
│     • Query string limit: 2KB (configurable)                     │
│     • Header size limit: 8KB (configurable)                      │
│     • Oversized request logging                                  │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  3. Connection Timeout (Slowloris Protection) - NEW              │
│     • Request timeout: 30s (configurable)                        │
│     • Slow connection detection                                  │
│     • Automatic termination                                      │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  4. Rate Limiting (IP / API Key)                                 │
│     • Token bucket algorithm                                     │
│     • Redis-backed counters                                      │
│     • Configurable limits per IP/key                             │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  5. Authentication (Basic Auth / API Key / OAuth2)               │
│     • Pluggable auth providers                                   │
│     • Optional public access                                     │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  6. Input Validation (OAI-PMH spec compliance)                   │
│     • Value objects for type safety                              │
│     • Suspicious pattern detection                               │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  7. SQL Injection Prevention (Parameterized Queries)             │
│     • Doctrine DBAL prepared statements                          │
│     • No string concatenation                                    │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  8. XSS Prevention (XML Escaping)                                │
│     • XMLWriter automatic escaping                               │
│     • No user-generated HTML                                     │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  9. Access Control (Record-Level Permissions)                    │
│     • Public/restricted record flags                             │
│     • Per-user/API key permissions                               │
└───────────────────────┬─────────────────────────────────────────┘
                        │
┌───────────────────────▼─────────────────────────────────────────┐
│  10. Enhanced Security Logging - NEW                             │
│      • All authentication attempts                               │
│      • Rate limit violations                                     │
│      • Suspicious requests (SQL injection, XSS)                  │
│      • Restricted record access                                  │
│      • IP anonymization for GDPR (see ADR-0011)                  │
└─────────────────────────────────────────────────────────────────┘
```

**Security Middleware Pipeline** (execution order):
1. HttpsEnforcementMiddleware → Force HTTPS if configured
2. RequestSizeValidationMiddleware → Validate query string and headers
3. ConnectionTimeoutMiddleware → Monitor request duration
4. RateLimitingMiddleware → Check and enforce rate limits
5. AuthenticationMiddleware → Authenticate user/API key (if required)
6. OaiPmhValidationMiddleware → Validate OAI-PMH parameters
7. Controller/Handler → Process request
8. SecurityLoggingMiddleware → Log security events

### 5.2 Authentication Methods

#### Public Access (Default)

- No authentication required
- Rate limiting by IP address
- Suitable for open repositories

#### API Key Authentication

```
GET /oai?verb=GetRecord&...
X-API-Key: abc123def456
```

- API keys stored hashed in database
- Key rotation supported
- Per-key rate limits
- Permissions assigned to keys

#### HTTP Basic Authentication

```
GET /oai?verb=GetRecord&...
Authorization: Basic dXNlcjpwYXNz
```

- Username/password authentication
- Passwords hashed with bcrypt
- LDAP/AD integration possible via plugin

#### OAuth2 / SAML (Plugin)

- Institutional SSO integration
- Requires authentication plugin
- Tokens validated on each request

### 5.3 Rate Limiting

**Algorithm**: Token Bucket (via Redis)

**Limits Configuration**:
```yaml
rate_limiting:
  by_ip:
    requests_per_minute: 60
    requests_per_hour: 1000
  by_api_key:
    requests_per_minute: 600
    requests_per_hour: 10000
```

**Response Headers**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 42
X-RateLimit-Reset: 1707696120
```

**HTTP 429 Response**:
```xml
<error code="tooManyRequests">Rate limit exceeded. Retry after 60 seconds.</error>
```

### 5.4 Privacy & GDPR Compliance

**Comprehensive GDPR architecture** documented in [ADR-0011: Privacy & GDPR Compliance](../.github/adr/0011-privacy-gdpr-compliance.md).

**Privacy-by-Design Features**:

1. **IP Address Anonymization**:
   ```yaml
   privacy:
     ip_addresses:
       log_ip_addresses: true           # Set false to not log IPs
       anonymize_ip: true               # Anonymize IPs when logging
       anonymization_level: last_octet  # Options: none, last_octet, last_two_octets, full
   ```
   - IPv4: `192.168.1.XXX` (last octet masked)
   - IPv6: `2001:db8:85a3::XXXX` (last segment masked)

2. **Data Retention Policies**:
   ```yaml
   privacy:
     retention:
       operational_logs_days: 30        # General logs
       security_logs_days: 90           # Security events
       audit_logs_days: 365             # Compliance logs
       rate_limit_data_days: 7          # Rate limiting counters
   ```
   - Automated log cleanup via cron job (`privacy:cleanup-logs`)
   - Separate retention periods for different log types

3. **Right to be Forgotten**:
   - Soft delete: Mark records as deleted, retain for compliance
   - Hard delete: Permanently remove records (configurable)
   - Log anonymization: Replace deleted record IDs with hash
   - Audit trail: All deletions logged

4. **GDPR-Compliant Logging**:
   ```php
   // Logs only essential data, anonymizes IPs, filters sensitive params
   $this->logger->info('OAI-PMH request', [
       'event_type' => 'oai_request',
       'verb' => 'ListRecords',
       'ip' => '192.168.1.XXX',  // Anonymized
       'parameters' => ['verb', 'metadataPrefix', 'set'],  // Sensitive params filtered
       'timestamp' => '2026-02-13T10:30:45Z',
   ]);
   ```

5. **User Rights Support**:
   - **Access**: Logs queryable by admin
   - **Rectification**: Credential updates (future)
   - **Erasure**: Record deletion API
   - **Data Portability**: Logs exportable as JSON
   - **Object**: IP logging can be disabled

**Configuration**:
```yaml
privacy:
  log_ip_addresses: false              # Don't log IPs at all (maximum privacy)
  anonymize_ip: true                   # Or anonymize (last octet masked)
  anonymization_level: last_octet      # none, last_octet, last_two_octets, full
  log_retention_days: 30               # Auto-delete old logs
  
  record_deletion:
    support_record_deletion: true      # Allow record deletion
    soft_delete: true                  # Mark as deleted vs. hard delete
    audit_deletions: true              # Log all deletion requests
```

**GDPR Compliance Checklist**:
- ✅ IP anonymization (configurable levels)
- ✅ Data minimization (only essential data logged)
- ✅ Storage limitation (automated log cleanup)
- ✅ Right to erasure (record deletion API)
- ✅ Transparency (documented privacy policies)
- ✅ Accountability (audit logging)
- ⚠️ Legal review recommended per deployment jurisdiction

---

## 6. Performance & Scalability

### 6.1 Performance Targets

| Metric | Target | Measurement |
|--------|--------|-------------|
| **Identify response** | < 100ms | p95 latency |
| **GetRecord response** | < 500ms | p95 latency |
| **ListRecords (first page)** | < 1s | p95 latency |
| **Throughput** | >100 req/min | Sustained load |
| **Concurrent users** | 100+ | Simultaneous harvesters |
| **Dataset size** | 5M+ records | No degradation |

### 6.2 Caching Strategy

**Multi-Layer Caching**:

1. **Application Cache** (Redis):
   - Identify response (TTL: infinite, invalidate on config change)
   - ListMetadataFormats (TTL: infinite)
   - ListSets (TTL: 1 hour)
   - GetRecord (TTL: 1 hour per record)

2. **HTTP Cache** (Optional):
   - Cache-Control headers for harvesters
   - ETag support for conditional requests

3. **Database Query Cache**:
   - MySQL query cache
   - Prepared statement cache

**Cache Invalidation**:
- Time-based (TTL)
- Event-driven (record update events)
- Manual (CLI command: `bin/oai-pmh cache:clear`)

### 6.3 Database Optimization

**Indexes**:
```sql
-- Essential indexes
CREATE INDEX idx_identifier ON records(identifier);
CREATE INDEX idx_datestamp ON records(datestamp);
CREATE INDEX idx_deleted ON records(deleted);
CREATE INDEX idx_compound ON records(datestamp, deleted);

-- Set filtering
CREATE INDEX idx_record_set ON record_sets(record_id, set_id);
```

**Query Optimization**:
- Use EXPLAIN to analyze slow queries
- Avoid N+1 queries (fetch sets in batch)
- Use LIMIT/OFFSET or cursors for pagination
- Denormalize frequently accessed data
- Partition large tables (if >10M records)

**Connection Pooling**:
- Doctrine DBAL connection pooling
- Persistent connections (PDO::ATTR_PERSISTENT)
- Max connections: 100 (configurable)

### 6.4 Horizontal Scaling

**Stateless Application Design**:
- No server-side sessions
- Resumption tokens are stateless (JWT)
- All state in Redis or database

**Load Balancing**:
```
┌─────────┐
│ Clients │
└────┬────┘
     │
┌────▼────────┐
│Load Balancer│
│  (Nginx)    │
└─┬─────────┬─┘
  │         │
┌─▼──┐   ┌──▼─┐
│App1│   │App2│  (N instances)
└─┬──┘   └──┬─┘
  └────┬────┘
       │
┌──────▼──────┐
│Shared Redis │
└─────────────┘
```

**Auto-Scaling** (Kubernetes/Docker Swarm):
- Scale based on CPU/memory usage
- Scale based on request queue depth
- Minimum 2 instances for HA

### 6.5 Performance Monitoring

**Metrics to Track**:
- Request latency (p50, p95, p99)
- Throughput (requests per second)
- Cache hit rate
- Database query time
- Error rate
- Queue depth (if background jobs)

**Alerting Thresholds**:
- p95 latency > 2s
- Cache hit rate < 80%
- Error rate > 1%
- Database connection pool exhausted

---

## 7. Deployment Architecture

### 7.1 Infrastructure Options

#### Option 1: Traditional LAMP/LEMP Stack

```
┌──────────────┐
│  Nginx       │  (Reverse proxy, TLS termination)
│  + PHP-FPM   │
└──────┬───────┘
       │
┌──────▼───────┐
│  MySQL       │
│  Redis       │
└──────────────┘
```

**Deployment**:
- Ubuntu 22.04 LTS server
- Nginx 1.18+ with PHP-FPM 8.0+
- MySQL 8.0 or PostgreSQL 14
- Redis 6.0+

#### Option 2: Docker Containers

```
docker-compose.yml:
  - app (PHP-FPM)
  - nginx
  - mysql
  - redis
```

**Advantages**:
- Isolated environment
- Easy local development
- Reproducible deployments
- Portable across cloud providers

#### Option 3: Kubernetes (Production HA)

```
Deployment:
  - app pods (replica: 3)
  - nginx ingress
  - MySQL StatefulSet
  - Redis cluster
```

**Advantages**:
- Auto-scaling
- Self-healing
- Rolling updates
- Multi-datacenter

### 7.2 Environment Configuration

#### Development

```yaml
APP_ENV=development
APP_DEBUG=true
CACHE_ENABLED=false
LOG_LEVEL=debug
```

#### Staging

```yaml
APP_ENV=staging
APP_DEBUG=true
CACHE_ENABLED=true
LOG_LEVEL=info
DATABASE=staging_db
```

#### Production

```yaml
APP_ENV=production
APP_DEBUG=false
CACHE_ENABLED=true
LOG_LEVEL=warning
DATABASE=production_db
REDIS_CLUSTER=true
```

### 7.3 Deployment Process

**CI/CD Pipeline** (GitHub Actions):

1. **Build Phase**:
   - `composer install --no-dev --optimize-autoloader`
   - Run PHPStan Level 8
   - Run PHPCS PSR-12 checks
   
2. **Test Phase**:
   - Run PHPUnit tests (unit, integration)
   - Run OAI-PMH compliance tests
   - Code coverage report
   
3. **Deploy Phase** (if tests pass):
   - Tag release
   - Build Docker image
   - Push to registry
   - Deploy to staging
   - Smoke tests
   - Deploy to production (manual approval)

**Zero-Downtime Deployment**:
- Blue-green deployment
- Rolling updates in Kubernetes
- Database migrations before deploy
- Cache warming after deploy

---

## 8. Testing Strategy

### 8.1 Test Pyramid

```
          /\
         /e2e\           10%  End-to-end tests
        /──────\
       /integ. \         20%  Integration tests
      /──────────\
     /   unit     \      70%  Unit tests
    /──────────────\
```

### 8.2 Unit Tests

**Coverage Target**: 80%+

**What to Test**:
- Value objects (construction, validation, equality)
- Entities (behavior methods)
- Handlers (business logic with mocked repositories)
- Services (resumption token encoding/decoding, etc.)

**Tools**:
- PHPUnit
- Mockery for mocking
- Prophecy for complex mocks

**Example**:
```php
final class GetRecordHandlerTest extends TestCase
{
    public function testHandle_WhenRecordExists_ReturnsRecordResponse(): void
    {
        // Arrange
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->method('getRecord')
                   ->willReturn(new Record(/* ... */));
        
        $handler = new GetRecordHandler($repository, /* ... */);
        
        // Act
        $result = $handler->handle($request);
        
        // Assert
        $this->assertInstanceOf(RecordResponse::class, $result);
    }
}
```

### 8.3 Integration Tests

**What to Test**:
- Repository with real MySQL database
- Repository with real PostgreSQL database
- Cache with real Redis
- Full HTTP request → response flow

**Setup**:
- Test database (H2, SQLite, or Docker MySQL/PostgreSQL)
- Test fixtures (sample records)

**Example**:
```php
final class MySqlRepositoryTest extends TestCase
{
    private Connection $connection;
    
    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection([/* test db */]);
        $this->loadFixtures();
    }
    
    public function testGetRecord_WhenRecordExists_ReturnsRecord(): void
    {
        $repo = new DoctrineRecordRepository($this->connection, $mapping);
        $record = $repo->getRecord(new RecordIdentifier('test-id'));
        
        $this->assertNotNull($record);
        $this->assertEquals('test-id', $record->getIdentifier()->getValue());
    }
}
```

### 8.4 Compliance Tests

**OAI-PMH Validation**:
- All responses validate against OAI-PMH XSD schema
- All error codes comply with specification
- Resumption tokens work correctly
- UTF-8 encoding enforced

**Tools**:
- libxml schema validation
- OAI-PMH Repository Validator (external tool)

**Example**:
```php
final class OaiPmhComplianceTest extends TestCase
{
    public function testIdentifyResponse_ValidatesAgainstSchema(): void
    {
        $response = $this->makeRequest('verb=Identify');
        $xml = new \DOMDocument();
        $xml->loadXML($response->getBody());
        
        $this->assertTrue($xml->schemaValidate(
            'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        ));
    }
}
```

### 8.5 Performance Tests

**Benchmarking**:
- Use PHPBench for micro-benchmarks
- Apache Bench (ab) or wrk for load testing
- Test with 1M, 5M, 10M record datasets

**Scenarios**:
- Identify: 1000 requests, measure throughput
- GetRecord: Random records, measure p95 latency
- ListRecords: First page with 100 results, measure latency
- ListRecords: Full harvest via resumption tokens

**Tools**:
- PHPBench
- Apache Bench: `ab -n 1000 -c 10 http://localhost/oai?verb=Identify`
- wrk: `wrk -t4 -c100 -d30s http://localhost/oai?verb=Identify`

---

## 9. Monitoring & Observability

### 9.1 Logging

**Log Levels**:
- DEBUG: Detailed debugging information
- INFO: General information (request received, etc.)
- WARNING: Warning but not error (deprecated param used)
- ERROR: Error but service still running
- CRITICAL: Service failure

**Log Format** (JSON):
```json
{
  "timestamp": "2026-02-10T12:00:00Z",
  "level": "INFO",
  "message": "OAI-PMH request received",
  "context": {
    "request_id": "abc123",
    "verb": "GetRecord",
    "identifier": "oai:example:12345",
    "ip": "192.168.1.100",
    "user_agent": "OAI-Harvester/2.0"
  }
}
```

**Log Destinations**:
- File: `/var/log/oai-pmh/app.log`
- Syslog: For centralized logging
- ELK Stack: Elasticsearch, Logstash, Kibana (optional)

### 9.2 Metrics

**Prometheus Metrics**:

```
# Request metrics
oai_requests_total{verb="Identify"} 1542
oai_requests_duration_seconds{verb="GetRecord",quantile="0.95"} 0.234

# Cache metrics
oai_cache_hits_total 45234
oai_cache_misses_total 1234

# Database metrics
oai_db_queries_total 67890
oai_db_query_duration_seconds{quantile="0.95"} 0.045

# Error metrics
oai_errors_total{code="idDoesNotExist"} 89
```

**Grafana Dashboards**:
- Overview: Request rate, latency, error rate
- Performance: Cache hit rate, database query time
- Security: Rate limit violations, auth failures
- Business: Records per set, format distribution

### 9.3 Health Checks

**Liveness Probe** (`/health/live`):
- Is application running?
- Returns 200 if process alive

**Readiness Probe** (`/health/ready`):
- Is application ready to serve traffic?
- Checks database connectivity
- Checks cache connectivity
- Returns 200 if all dependencies healthy

**Example**:
```json
{
  "status": "healthy",
  "timestamp": "2026-02-10T12:00:00Z",
  "checks": {
    "database": {
      "status": "healthy",
      "response_time_ms": 5,
      "connection_pool": {
        "active": 3,
        "idle": 7,
        "max": 10
      }
    },
    "cache": {
      "status": "healthy",
      "response_time_ms": 1,
      "hit_rate": 0.87
    },
    "disk": {
      "status": "healthy",
      "free_space_gb": 120
    }
  }
}
```

### 9.4 Alerting

**Alert Rules** (Prometheus Alertmanager):

```yaml
- alert: HighLatency
  expr: oai_requests_duration_seconds{quantile="0.95"} > 2
  for: 5m
  annotations:
    summary: "OAI-PMH requests are slow"
    
- alert: HighErrorRate
  expr: rate(oai_errors_total[5m]) > 0.01
  for: 5m
  annotations:
    summary: "Error rate above 1%"
    
- alert: DatabaseDown
  expr: oai_health_database_status != 1
  for: 1m
  annotations:
    summary: "Database is unreachable"
```

---

## 10. Technical Implementation Plan

### 10.1 Project Phases

```
Phase 1: Foundation          ├─ 4 weeks
Phase 2: Core OAI-PMH        ├─ 6 weeks
Phase 3: Data Layer          ├─ 4 weeks
Phase 4: Metadata & Sets     ├─ 4 weeks
Phase 5: Quality & Testing   ├─ 4 weeks
Phase 6:Documentation        ├─ 4 weeks
                             │
                    Total: 26 weeks (6 months)
```

### 10.2 Phase 1: Foundation (Weeks 1-4)

**Objective**: Set up project infrastructure and establish development workflow

#### Week 1: Project Setup

**Tasks**:
- [ ] Initialize Git repository
- [ ] Create composer.json with dependencies (see ADR-0001)
- [ ] Set up directory structure (see File Structure document)
- [ ] Configure PSR-4 autoloading
- [ ] Create .gitignore, .editorconfig

**Deliverables**:
- Working `composer install`
- Basic directory structure
- Git repository with initial commit

#### Week 2: Quality Tools Configuration

**Tasks**:
- [ ] Configure PHPUnit (phpunit.xml)
- [ ] Configure PHPStan (phpstan.neon, Level 8)
- [ ] Configure PHP_CodeSniffer (phpcs.xml, PSR-12)
- [ ] Set up Xdebug for code coverage
- [ ] Create Composer scripts (test, analyze, cs-check, cs-fix)

**Deliverables**:
- All quality tools run successfully
- Sample test passes
- Coverage report generated

#### Week 3: CI/CD Pipeline

**Tasks**:
- [ ] Create GitHub Actions workflow (`.github/workflows/ci.yml`)
- [ ] Add jobs: dependencies, tests, static analysis, code style
- [ ] Configure matrix testing (PHP 8.0, 8.1, 8.2)
- [ ] Add badge to README.md
- [ ] Set up branch protection rules

**Deliverables**:
- CI pipeline runs on PR
- All checks pass
- Code coverage published

#### Week 4: Development Environment

**Tasks**:
- [ ] Create docker-compose.yml (PHP, Nginx, MySQL, Redis)
- [ ] Configure Xdebug for Docker
- [ ] Create .env.example
- [ ] Write development setup guide (docs/development.md)
- [ ] Test full development workflow

**Deliverables**:
- `docker-compose up` works
- Development database seeded
- Documentation for contributors

### 10.3 Phase 2: Core OAI-PMH (Weeks 5-10)

**Objective**: Implement OAI-PMH protocol handlers and domain model

#### Week 5: Domain Layer - Value Objects & Entities

**Tasks**:
- [ ] Create Domain/ValueObject/RecordIdentifier.php
- [ ] Create Domain/ValueObject/SetSpec.php
- [ ] Create Domain/ValueObject/OaiVerb.php
- [ ] Create Domain/Entity/Record.php
- [ ] Create Domain/Entity/RecordHeader.php
- [ ] Create Domain/Entity/Set.php
- [ ] Write unit tests for all domain objects (80%+ coverage)

**Deliverables**:
- Domain objects with full validation
- Unit tests pass
- PHPStan Level 8 passes

#### Week 6: Domain Layer - Collections & Exceptions

**Tasks**:
- [ ] Create Domain/Collection/RecordCollection.php
- [ ] Create Domain/Collection/SetCollection.php
- [ ] Create all OAI-PMH exceptions (BadArgumentException, etc.)
- [ ] Create Domain/Repository/RepositoryInterface.php
- [ ] Write unit tests

**Deliverables**:
- Type-safe collections
- All exception types defined
- Repository interface documented

#### Week 7-8: Application Layer - Handlers (Part 1)

**Tasks**:
- [ ] Create Application/Handler/IdentifyHandler.php
- [ ] Create Application/Handler/GetRecordHandler.php
- [ ] Create Application/Handler/ListMetadataFormatsHandler.php
- [ ] Create Application/DTO/* for responses
- [ ] Create Application/Validator/OaiPmhValidator.php
- [ ] Write unit tests with mocked repositories

**Deliverables**:
- 3 verb handlers working
- Business logic tested
- DTOs defined

#### Week 9-10: Application Layer - Handlers (Part 2)

**Tasks**:
- [ ] Create Application/Handler/ListRecordsHandler.php
- [ ] Create Application/Handler/ListIdentifiersHandler.php
- [ ] Create Application/Handler/ListSetsHandler.php
- [ ] Create Application/Service/ResumptionTokenService.php (see ADR-0006)
- [ ] Write unit tests

**Deliverables**:
- All 6 verb handlers complete
- Resumption token logic working
- Pagination tested

### 10.4 Phase 3: Data Layer (Weeks 11-14)

**Objective**: Implement database access and caching

#### Week 11: Database Connection & Mapping Configuration

**Tasks**:
- [ ] Create Infrastructure/Persistence/ConnectionFactory.php
- [ ] Create Infrastructure/Config/YamlConfigLoader.php
- [ ] Create Infrastructure/Repository/Mapping/DatabaseMapping.php
- [ ] Create config/database_mapping.yaml schema
- [ ] Validate mapping on load

**Deliverables**:
- Database connection established
- YAML configuration loaded and validated
- Mapping object created

#### Week 12: Repository Implementation - MySQL

**Tasks**:
- [ ] Create Infrastructure/Repository/DoctrineRecordRepository.php
- [ ] Create Infrastructure/Repository/Adapter/MySqlQueryAdapter.php
- [ ] Implement getRecord(), listRecords(), listIdentifiers()
- [ ] Implement listSets(), getEarliestDatestamp()
- [ ] Write integration tests with test MySQL database

**Deliverables**:
- MySQL repository fully functional
- All Repository methods implemented
- Integration tests pass

#### Week 13: Repository Implementation - PostgreSQL

**Tasks**:
- [ ] Create Infrastructure/Repository/Adapter/PostgreSqlQueryAdapter.php
- [ ] Handle PostgreSQL-specific syntax differences
- [ ] Test with PostgreSQL test database
- [ ] Document differences in adapters

**Deliverables**:
- PostgreSQL repository working
- Integration tests pass for both databases

#### Week 14: Caching Layer

**Tasks**:
- [ ] Create Infrastructure/Cache/CachedRepositoryDecorator.php (see ADR-0005)
- [ ] Integrate Symfony Cache (Redis adapter)
- [ ] Implement cache warming strategy
- [ ] Create cache invalidation mechanism
- [ ] Write cache integration tests

**Deliverables**:
- Caching working with Redis
- Cache hit rate >80% (tested)
- Graceful degradation if Redis unavailable

### 10.5 Phase 4: Metadata & Sets (Weeks 15-18)

**Objective**: Implement metadata format plugins and set hierarchies

#### Week 15: Metadata Format Plugin System

**Tasks**:
- [ ] Create Plugin/MetadataFormat/MetadataFormatInterface.php
- [ ] Create Plugin/MetadataFormat/AbstractMetadataFormat.php
- [ ] Create Application/Service/MetadataFormatRegistry.php
- [ ] Create plugin discovery mechanism
- [ ] Document plugin development

**Deliverables**:
- Plugin interface defined
- Plugin registry working
- Plugin loading from configuration

#### Week 16: Dublin Core Format Implementation

**Tasks**:
- [ ] Create Plugin/MetadataFormat/OaiDc/OaiDcFormat.php
- [ ] Implement Dublin Core serialization
- [ ] Handle all 15 DC elements
- [ ] Write unit tests for serialization
- [ ] Validate XML against oai_dc schema

**Deliverables**:
- oai_dc format fully working
- XML validates against schema
- Example plugin for developers

#### Week 17: Sets Implementation

**Tasks**:
- [ ] Implement set filtering in repository
- [ ] Implement hierarchical set support
- [ ] Create Application/Service/SetHierarchyService.php
- [ ] Test set hierarchies (parent/child relationships)

**Deliverables**:
- Sets working in all handlers
- Hierarchical sets supported
- Set filtering tested

#### Week 18: Deleted Records Support

**Tasks**:
- [ ] Implement deleted record tracking in repository
- [ ] Add deleted status to responses
- [ ] Test deleted record scenarios (no, transient, persistent)
- [ ] Document configuration options

**Deliverables**:
- Deleted records working per OAI-PMH spec
- All deletion policies supported
- Tests verify behavior

### 10.6 Phase 5: Quality & Testing (Weeks 19-22)

**Objective**: Achieve production-ready quality standards

#### Week 19: Presentation Layer

**Tasks**:
- [ ] Create Presentation/Http/Controller/OaiPmhController.php
- [ ] Create Presentation/Serializer/OaiPmhXmlSerializer.php (see ADR-0010)
- [ ] Create public/index.php entry point
- [ ] Create Presentation/Serializer/ErrorSerializer.php
- [ ] Write end-to-end HTTP tests

**Deliverables**:
- Full HTTP request → XML response flow working
- XML responses validate against OAI-PMH schema
- Error responses formatted correctly

#### Week 20: Enhanced Security & Middleware (UPDATED 2026-02-13)

**Tasks - Core Security**:
- [ ] Create Presentation/Http/Middleware/HttpsEnforcementMiddleware.php (NEW)
- [ ] Create Presentation/Http/Middleware/RequestSizeValidationMiddleware.php (NEW)
- [ ] Create Presentation/Http/Middleware/ConnectionTimeoutMiddleware.php (NEW)
- [ ] Create Infrastructure/Security/SecurityLogger.php (ENHANCED)
- [ ] Create Infrastructure/Security/IpAnonymizer.php (NEW - see ADR-0011)
- [ ] Configure Nginx/Apache timeouts for Slowloris protection
- [ ] Write middleware unit tests

**Tasks - Authentication & Rate Limiting**:
- [ ] Create Presentation/Http/Middleware/AuthenticationMiddleware.php
- [ ] Create Presentation/Http/Middleware/RateLimitingMiddleware.php
- [ ] Create Infrastructure/Authentication/ApiKeyProvider.php
- [ ] Create Infrastructure/RateLimiting/RateLimiter.php
- [ ] Write security tests

**Tasks - Enhanced Security Logging**:
- [ ] Implement authentication attempt logging (success/failure)
- [ ] Implement rate limit violation logging
- [ ] Implement suspicious request pattern detection (SQL injection, XSS)
- [ ] Implement restricted record access logging
- [ ] Implement oversized request logging
- [ ] Implement slow connection logging
- [ ] Configure log levels and destinations (JSON format)

**Deliverables**:
- ✅ HTTPS enforcement working (configurable redirect/reject)
- ✅ HSTS header support implemented
- ✅ Request size validation (2KB query string, 8KB headers)
- ✅ Connection timeout protection (30s default)
- ✅ Authentication working (Basic Auth, API Key)
- ✅ Rate limiting working with Redis backend
- ✅ Enhanced security logging with all event types
- ✅ IP anonymization functional (configurable levels)
- ✅ All security tests pass
- ✅ Web server configuration documented

**Effort**: ~80 hours (2 weeks at 40 hours/week)

#### Week 21: Compliance Testing

**Tasks**:
- [ ] Create tests/Compliance/OaiPmhValidatorTest.php
- [ ] Test all 6 verbs against OAI-PMH spec
- [ ] Test all error codes
- [ ] Test resumption tokens extensively
- [ ] Run external OAI-PMH validator

**Deliverables**:
- All compliance tests pass
- External validator passes
- OAI-PMH 2.0 certification

#### Week 22: Performance Testing & Optimization

**Tasks**:
- [ ] Create large test dataset (1M+ records)
- [ ] Run performance benchmarks (see Section 6)
- [ ] Identify and fix bottlenecks
- [ ] Optimize database queries (EXPLAIN analysis)
- [ ] Test with 5M record dataset
- [ ] Document performance results

**Deliverables**:
- All performance targets met
- Benchmarks documented
- Optimization guide created

#### Week 22.5: Privacy & GDPR Compliance (NEW - ADDED 2026-02-13)

**See [ADR-0011: Privacy & GDPR Compliance](../.github/adr/0011-privacy-gdpr-compliance.md) for detailed architecture**

**Tasks - Data Retention**:
- [ ] Create Infrastructure/Privacy/LogCleanupService.php
- [ ] Create Infrastructure/Privacy/LogRepository.php
- [ ] Create bin/console privacy:cleanup-logs command
- [ ] Configure retention policies (operational: 30d, security: 90d, audit: 365d)
- [ ] Set up cron job for automated cleanup
- [ ] Write log cleanup tests

**Tasks - Right to be Forgotten**:
- [ ] Create Application/Service/RecordDeletionService.php
- [ ] Add soft delete support to record schema
- [ ] Implement log anonymization for deleted records
- [ ] Create deletion audit logging
- [ ] Create bin/console privacy:delete-record command
- [ ] Write deletion tests

**Tasks - GDPR Documentation**:
- [ ] Create docs/privacy-policy-template.md
- [ ] Create docs/gdpr-compliance-guide.md
- [ ] Document data retention policies
- [ ] Document user rights procedures
- [ ] Create docs/data-protection-impact-assessment.md template

**Deliverables**:
- ✅ IP anonymization integrated (already done in Week 20)
- ✅ Automated log cleanup working
- ✅ Record deletion API functional
- ✅ Soft delete and hard delete supported
- ✅ Log anonymization for deleted records
- ✅ Privacy documentation complete
- ✅ GDPR compliance checklist verified

**Effort**: ~40 hours (1 week at 40 hours/week)

### 10.7 Phase 6: Documentation & Release (Weeks 23-26)

**Objective**: Complete documentation and prepare for public release

#### Week 23: User Documentation

**Tasks**:
- [ ] Write docs/installation.md
- [ ] Write docs/configuration.md
- [ ] Write docs/database-mapping.md
- [ ] Create configuration examples (DSpace, EPrints, custom)
- [ ] Write quick-start guide in README.md

**Deliverables**:
- Complete installation guide
- Configuration reference
- Example configurations

#### Week 24: Developer Documentation

**Tasks**:
- [ ] Write docs/plugin-development.md
- [ ] Generate API reference (phpDocumentor)
- [ ] Write docs/architecture.md
- [ ] Create contribution guide (CONTRIBUTING.md)
- [ ] Write troubleshooting guide

**Deliverables**:
- Developer documentation complete
- API reference published
- Contributors can get started

#### Week 25: Demo/Reference Implementation

**Tasks**:
- [ ] Create sample dataset (1000+ records)
- [ ] Set up demo database with multiple formats
- [ ] Configure sets and deleted records
- [ ] Create Docker Compose demo
- [ ] Write demo documentation

**Deliverables**:
- Working demo with `docker-compose up`
- Sample harvester scripts
- Demo walkthrough

#### Week 26: Final QA & Release

**Tasks**:
- [ ] Final code review
- [ ] Security audit (OWASP Top 10)
- [ ] Dependency audit (composer audit)
- [ ] Performance regression tests
- [ ] Tag v1.0.0 release
- [ ] Publish to Packagist
- [ ] Announce release

**Deliverables**:
- v1.0.0 released on GitHub
- Package on Packagist
- Announcement blog post

---

### 10.8 Post-MVP Roadmap (UPDATED 2026-02-13)

**Note**: Enhanced security and GDPR compliance originally planned for v1.1 have been moved into v1.0 MVP based on updated requirements (v1.1 - 2026-02-13).

#### v1.1 - Advanced Authentication (Months 7-8)

- OAuth2 authentication plugin
- SAML authentication plugin (enterprise SSO)
- LDAP/Active Directory integration
- Multi-factor authentication (MFA) support

#### v1.2 - Performance Enhancements (Months 9-10)

- Advanced caching strategies (multi-level, intelligent invalidation)
- Background job processing for heavy operations (large result set pre-building)
- Database query optimization (materialized views, advanced indexing)
- Connection pooling enhancements
- CDN integration for XML responses

#### v1.3 - Operational Features (Months 11-12)

- Grafana dashboards (pre-built templates)
- Prometheus alert rules
- Admin UI (web-based configuration management)
- Batch record import/export tools
- Migration tools for common platforms (DSpace, EPrints, Fedora)

#### v2.0 - Enterprise Features (Year 2)

- Multi-tenant support (single server, multiple repositories)
- High availability (HA) configuration
- Incremental harvesting optimizations
- Advanced plugin marketplace
- GraphQL API (in addition to OAI-PMH)
- Real-time harvesting (webhooks, streaming)

---

## 11. Risk Management

### 11.1 Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Performance doesn't meet targets with 5M records** | Medium | High | Early performance testing in Phase 5; database optimization; caching |
| **Database schema mapping too complex** | Medium | Medium | Iterative refinement; user feedback; example mappings |
| **OAI-PMH compliance issues** | Low | High | Compliance tests in Phase 5; external validator; spec review |
| **Security vulnerabilities** | Low | High | Security audit; OWASP checklist; dependency scanning |

### 11.2 Project Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Timeline overrun** | Medium | Medium | Phased approach; MVP-first; regular progress reviews |
| **Insufficient testing** | Low | High | Dedicated testing phase; CI/CD enforcement; code coverage metrics |
| **Lack of documentation** | Medium | Medium | Documentation phase built-in; templates; examples |

---

## 12. Success Metrics

### 12.1 Technical Metrics

- [ ] PHPStan Level 8: 0 errors
- [ ] Code coverage: >80%
- [ ] PSR-12 compliance: 100%
- [ ] OAI-PMH compliance: 100% (validator passes)
- [ ] Performance: All targets met (see Section 6.1)

### 12.2 Adoption Metrics

- [ ] 3+ organizations deploy in production within 6 months
- [ ] 100+ Packagist installations within 6 months
- [ ] 10+ GitHub stars within 6 months
- [ ] Active community (issues, PRs, discussions)

### 12.3 Quality Metrics

- [ ] Installation: <1 hour from zero to running
- [ ] Documentation: <5 support questions for basic setup
- [ ] Bugs: <1% critical bug rate
- [ ] Uptime: 99.9% in production deployments

---

## 13. Appendices

### Appendix A: Glossary

See Requirements Document Section 11.F - Glossary

### Appendix B: References

- [OAI-PMH 2.0 Specification](https://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm)
- [PHP-FIG PSR Standards](https://www.php-fig.org/psr/)
- [Doctrine DBAL Documentation](https://www.doctrine-project.org/projects/dbal.html)
- [Domain-Driven Design by Eric Evans](https://www.domainlanguage.com/ddd/)
- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)

### Appendix C: Related Documents

- **Requirements**: `docs/REPOSITORY_SERVER_REQUIREMENTS.md`
- **ADRs**: `.github/adr/*.md`
- **File Structure**: `docs/OAIPMH_SERVER_FILE_STRUCTURE.md`
- **Value Objects Analysis**: `docs/*_ANALYSIS.md`

---

**Document Approval**:

- **Technical Architect**: ✅ Approved  
- **Security Architect**: ✅ Approved  
- **Development Lead**: ✅ Approved  
- **Privacy Officer**: ✅ Approved  
- **Date**: 2026-02-13 (Updated)

---

**Version History**:

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0 | 2026-02-10 | Initial comprehensive technical design | Solutions Architect |
| 1.1 | 2026-02-13 | Enhanced security & GDPR compliance: Added ADR-0011 (Privacy & GDPR), updated ADR-0007 (Security) with HTTPS enforcement, request validation, slowloris protection, enhanced security logging, IP anonymization, data retention policies. Updated implementation plan (Week 20 enhanced, Week 22.5 added). Updated post-MVP roadmap. | Solutions Architect |

---

**END OF TECHNICAL DESIGN DOCUMENT**
