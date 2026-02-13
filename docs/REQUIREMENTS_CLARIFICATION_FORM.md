# OAI-PMH Repository Server - Requirements Clarification Form

**Date Issued:** February 13, 2026  
**Purpose:** Gather additional requirements details to enhance the requirements document  
**Requested By:** Senior Business Analyst  
**Response Deadline:** [Please specify]

---

## Instructions

This form addresses gaps identified in the requirements review. Please complete the sections below to help us refine the requirements document. You can:

- ✅ Check boxes for your selections
- ✍️ Fill in text fields with your responses
- ❓ Mark "Need to discuss" if you're unsure

---

## Section 1: User Stories & Use Cases

The requirements document currently focuses on technical specifications. Adding user stories will help the development team understand the "why" behind each feature from a user's perspective.

### 1.1 Repository Administrator User Stories

Please review and add/modify these user stories:

**Story 1: Quick Deployment**
```
As a repository administrator,
I want to deploy the OAI-PMH server in under 1 hour,
So that I can quickly expose my metadata collection without extensive technical setup.
```

- [x] Agree with this story as written
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 2: Configuration Management**
```
As a repository administrator,
I want to configure the server entirely through YAML files,
So that I can manage settings without writing code or accessing a database.
```

- [x] Agree with this story as written
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 3: [YOUR STORY]**

Please add 2-3 additional user stories for repository administrators:

**Story 3:**
```
As a repository administrator,
I want to _________________________________________________,
So that I can ______________________________________________.
```

**Story 4:**
```
As a repository administrator,
I want to _________________________________________________,
So that I can ______________________________________________.
```

### 1.2 Software Developer User Stories

**Story 5: Plugin Development**
```
As a software developer,
I want to create a custom metadata format plugin,
So that I can expose my repository's metadata in domain-specific schemas beyond Dublin Core.
```

- [x] Agree with this story as written
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 6: Database Integration**
```
As a software developer,
I want to map my existing database schema to OAI-PMH fields via configuration,
So that I don't have to migrate data to a new database structure.
```

- [x] Agree with this story as written  
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 7: [YOUR STORY]**

Please add 1-2 additional developer user stories:

**Story 7:**
```
As a software developer,
I want to _________________________________________________,
So that I can ______________________________________________.
```

### 1.3 Harvester (Client) User Stories

**Story 8: Standards Compliance**
```
As a harvester client,
I want the repository to be fully OAI-PMH 2.0 compliant,
So that my automated harvesting software works without custom modifications.
```

- [x] Agree with this story as written
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 9: Performance**
```
As a harvester client,
I want to receive the first page of results in under 1 second,
So that I can efficiently harvest large repositories without timeouts.
```

- [x] Agree with this story as written
- [ ] Modify: _______________________________________________
- [ ] Not important / Remove

**Story 10: [YOUR STORY]**

**Story 10:**
```
As a harvester client,
I want to _________________________________________________,
So that I can ______________________________________________.
```

---

## Section 2: Resource Limits & Performance

### 2.1 System Resource Limits

The requirements currently specify response time targets but not resource consumption limits. Please specify:

**Memory Limits:**
- [ ] 256 MB per request (suitable for small repositories)
- [ ] 512 MB per request (recommended for medium repositories)
- [ ] 1 GB per request (for large datasets with complex metadata)
- [x] No limit / Let the server admin configure
- [ ] Other: _______________

**Maximum Execution Time:**
- [ ] 30 seconds (standard PHP timeout)
- [ ] 60 seconds (for large result sets)
- [ ] 120 seconds (for very large repositories)
- [x] Configurable per environment
- [ ] Other: _______________

**Maximum Request Size:**
- [ ] 10 MB (prevent large payload attacks)
- [ ] 50 MB (for file upload features if added later)
- [ ] Configurable
- [x] No limit needed (OAI-PMH is GET-based)

### 2.2 Database Connection Limits

**Connection Pool Size:**
- [ ] 10 connections (small scale)
- [ ] 50 connections (medium scale)
- [ ] 100 connections (large scale)
- [ ] Configurable based on deployment
- [ ] Not sure / Need recommendation

**Query Timeout:**
- [x] 5 seconds (prevent slow queries from hanging)
- [ ] 10 seconds
- [ ] 30 seconds
- [ ] Configurable
- [ ] Other: _______________

---

## Section 3: Security & DDoS Protection

### 3.1 HTTPS Enforcement

**Should the server support HTTPS-only mode (reject HTTP requests)?**

- [x] Yes, add `force_https: true` configuration option (REQUIRED for MVP)
- [ ] Yes, but as SHOULD HAVE (post-MVP)
- [ ] No, leave it to the web server/proxy configuration
- [ ] Not sure / Need recommendation

**Reasoning:** ___________________________________________________

### 3.2 DDoS Protection Beyond Rate Limiting

**In addition to rate limiting (already required), should the server implement:**

**Connection Limits:**
- [ ] Yes, limit concurrent connections per IP (e.g., max 10)
- [ ] Yes, but make it configurable
- [ ] No, rely on web server/firewall
- [ ] Not sure
I think for harvesting large data sets this shouldn't be limited.

**Request Size Validation:**
- [x] Yes, reject requests with query strings > 2KB (prevent URL-based attacks)
- [ ] Yes, reject headers > 8KB
- [ ] No, not necessary for OAI-PMH
- [ ] Not sure

**Slowloris Protection:**
- [x] Yes, timeout connections that send data too slowly
- [ ] No, web server (Nginx/Apache) handles this
- [ ] Not sure

**IP Blacklisting:**
- [ ] Yes, support configurable IP blacklist/whitelist
- [ ] Yes, but as SHOULD HAVE (post-MVP)
- [ ] No, use firewall rules instead
- [x] Not sure

### 3.3 Security Logging

**What security events should be logged?**

- [ ] All authentication attempts (success and failure)
- [ ] Rate limit violations
- [ ] Suspicious request patterns (SQL injection attempts, path traversal)
- [ ] IP addresses accessing restricted records
- [x] All of the above
- [ ] Other: _______________________________________________

**Should logs include IP addresses?**
- [ ] Yes, IP addresses are essential for security analysis
- [x] Yes, but with GDPR-compliant anonymization (last octet masked: 192.168.1.XXX)
- [ ] No, privacy concerns outweigh security needs
- [ ] Configurable per deployment

---

## Section 4: Error Message Usability

### 4.1 Error Message Design

**How technical should error messages be?**

**For Repository Administrators (in logs):**
- [ ] Very technical (stack traces, SQL queries, full context)
- [ ] Moderately technical (error codes, component names, no sensitive data)
- [x] User-friendly (plain English descriptions)

**For Harvesters (in OAI-PMH error responses):**
- [ ] OAI-PMH error codes only (e.g., `badArgument`)
- [ ] OAI-PMH codes + brief explanation (e.g., "badArgument: The 'from' parameter must be a valid UTC date")
- [x] Include suggestions for fixing (e.g., "Try: from=2020-01-01T00:00:00Z")

**For Developers (debugging mode):**
- [ ] Stack traces and verbose debugging (when debug mode enabled)
- [ ] Structured error context (JSON format with request ID, timestamp, component)
- [x] Both of the above

### 4.2 Example Error Messages

**Current OAI-PMH error (minimalist):**
```xml
<error code="badArgument"/>
```

**Option 1 (OAI-PMH compliant with explanation):**
```xml
<error code="badArgument">The 'from' parameter must be a valid UTC date in ISO 8601 format</error>
```

**Option 2 (With suggestion):**
```xml
<error code="badArgument">
  Invalid date format in 'from' parameter. 
  Expected format: YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ
  Example: from=2020-01-01T00:00:00Z
</error>
```

**Which approach do you prefer?**
- [ ] Minimalist (OAI-PMH spec minimum)
- [x] Option 1 (Brief explanation)
- [ ] Option 2 (Explanation + example)
- [ ] Configurable (verbose mode via config or query parameter)

---

## Section 5: Documentation Requirements Validation

### 5.1 Documentation Priorities

**Please rank these documentation types by importance (1 = most important, 8 = least important):**

- [1] ___ Installation Guide (step-by-step deployment)
- [1] ___ Configuration Guide (all config options explained)
- [2] ___ Developer Guide (architecture, plugins, extending)
- [5] ___ API Reference (generated from code docblocks)
- [1] ___ How-To Guides (common tasks: add format, map database, etc.)
- [5] ___ Troubleshooting Guide (common issues and solutions)
- [8] ___ Migration Guide (from DSpace, EPrints, etc.)
- [1] ___ OAI-PMH Endpoint Docs (for harvester clients)

### 5.2 Documentation Format Preferences

**What documentation format is most useful for your team?**

- [x] Markdown files in repository (easy to version control, simple)
- [ ] Generated HTML site (easier to navigate, search)
- [ ] Both (Markdown source, HTML generated via GitHub Pages or ReadTheDocs)
- [ ] Video tutorials (for installation and common tasks)
- [ ] Interactive examples / playground
- [ ] Other: _______________________________________________

---

## Section 6: MVP Scope Validation

### 6.1 Authentication Requirements for MVP

**The current MVP specifies "Public access (no authentication)" to keep scope minimal.**

**Is this acceptable?**

- [x] Yes, public access is fine for MVP - we'll add authentication in v1.1
- [ ] No, we MUST have Basic HTTP Authentication in MVP
- [ ] No, we MUST have API key authentication in MVP
- [ ] No, we MUST have both Basic Auth and API keys in MVP
- [ ] Other requirement: _______________________________________________

**Reasoning:** ___________________________________________________

### 6.2 Rate Limiting for MVP

**Current MVP: Rate limiting is listed as "NICE TO HAVE" (post-MVP)**

**Is this acceptable?**

- [x] Yes, rate limiting can wait for v1.1
- [ ] No, basic rate limiting is MUST HAVE for MVP (we expect public internet exposure)
- [ ] No, we need sophisticated rate limiting with multiple limits in MVP
- [ ] Depends on authentication: if public, rate limiting is MUST HAVE

**Expected deployment:**
- [ ] Internal network only (low abuse risk)
- [ ] Public internet (high abuse risk)
- [x] Both (need flexible configuration)

### 6.3 Background Job Processing for MVP

**Current MVP: Background job processing is "SHOULD HAVE"**

**Do you have use cases requiring background jobs for MVP?**

- [ ] Yes: Cache warming for large repositories
- [ ] Yes: Pre-building large result sets
- [ ] Yes: Periodic data synchronization from source database
- [ ] Yes: Analytics and reporting
- [ ] No, we can defer to post-MVP
- [x] Other: Can we think about this for a later version? We don't have a use case for it right now.

---

## Section 7: Deployment Environment Details

### 7.1 Expected Deployment Platforms

**Where will this server be deployed? (Select all that apply)**

- [x] On-premise Linux servers (Ubuntu, CentOS, Debian)
- [ ] Cloud VMs (AWS EC2, Google Compute, Azure VMs)
- [ ] Docker containers on single host
- [ ] Kubernetes cluster (managed like AWS EKS, Google GKE)
- [ ] Platform-as-a-Service (Heroku, Platform.sh)
- [ ] Shared hosting (cPanel, Plesk)
- [x] Windows Server
- [x] Other: XAMPP or similar local development environments

### 7.2 Database Platform Confirmation

**Which database(s) will you use?**

- [x] MySQL (version: _______)
- [ ] PostgreSQL (version: _______)
- [ ] MariaDB (version: _______)
- [x] Other: And extendeble to any database with a PHP adapter.

**Is your database schema:**
- [ ] An existing database (DSpace, EPrints, custom application)
- [ ] A new schema we can design for OAI-PMH
- [x] Both (migration scenario and greenfield deployments)

### 7.3 Expected Repository Sizes

**Typical repository size you'll deploy:**

- [ ] Small: < 10,000 records
- [ ] Medium: 10,000 - 100,000 records
- [x] Large: 100,000 - 1,000,000 records
- [ ] Very Large: 1M - 5M records
- [x] Later... Massive: > 5M records

**Largest repository you expect to support:**

- [ ] < 100K records
- [ ] 100K - 1M records
- [ ] 1M - 5M records
- [ ] 5M - 10M records
- [x] > 10M records and could be in the hundreds of millions or more

---

## Section 8: Additional Requirements

### 8.1 Missing Requirements

**Are there any requirements NOT covered in the requirements document that you need?**

**Examples:**
- Multi-language support (internationalization)
x Audit trail / change history
x Backup and restore features
x Multi-tenancy (one server, multiple repositories)
- Integration with specific systems (Dataverse, CKAN, etc.)
- Real-time notifications when records change
- GraphQL API in addition to OAI-PMH
- Other: _______________________________________________

**Please describe:**

1. _______________________________________________________________
2. _______________________________________________________________
3. _______________________________________________________________

### 8.2 Non-Functional Requirements Validation

**Performance Targets** (currently < 1s for ListRecords first page):

- [ ] Too strict (we can accept 2-3 seconds)
- [ ] Appropriate
- [x] Too lenient (we need < 500ms)

**Code Coverage Target** (currently 80%+):

- [ ] Too strict (60-70% is acceptable)
- [ ] Appropriate
- [x] Too lenient (we want 90%+ or 100%)

**PHPStan Level** (currently Level 8 - maximum strictness):

- [ ] Too strict (Level 6 is acceptable)
- [ ] Appropriate
- [x] Keep Level 8 (maximum type safety)

---

## Section 9: Success Metrics Validation

### 9.1 MVP Success Criteria

**Review the current MVP success criteria. Are these appropriate?**

1. ✅ A harvester can successfully harvest all records from a 100,000-record repository
   - [ ] Agree  [x] Need higher volume  [ ] Need lower volume

2. ✅ Response times meet performance targets (<1s for ListRecords first page)
   - [x] Agree  [ ] Too strict  [ ] Too lenient

3. ✅ OAI-PMH Validator passes all compliance tests
   - [x] Agree  [ ] Not necessary  [ ] Add other validators

4. ✅ Documentation allows a developer to install and configure in <1 hour
   - [x] Agree  [ ] Too ambitious (allow 2-3 hours)  [ ] Too lenient (target 30 min)

5. ✅ At least one external organization successfully deploys the server
   - [x] Agree  [ ] Change to: _____ organizations

6. ✅ All automated tests pass (unit, integration, compliance)
   - [x] Agree  [ ] Add performance benchmarks

7. ✅ PHPStan Level 8 and PSR-12 compliance verified
   - [x] Agree  [ ] Too strict  [ ] Add other quality gates

8. ✅ Demo/reference implementation runs in Docker with sample data
   - [x] Agree  [ ] Also need Kubernetes example

**Additional success criteria to add:**

1. _______________________________________________________________
2. _______________________________________________________________

---

## Section 10: Timeline & Prioritization

### 10.1 MVP Timeline Validation

**Current estimate: 3-6 months for MVP (v1.0)**

- [ ] Realistic
- [ ] Too aggressive (suggest: _____ months)
- [ ] Too conservative (we can do it in: _____ months)
- [x] No deadline pressure / timeline flexible

### 10.2 Feature Prioritization Adjustments

**Are there any "NICE TO HAVE" features that should be promoted to "MUST HAVE" for MVP?**

From the current NICE TO HAVE list:
- [ ] HTTP Basic Auth → **MUST HAVE** for MVP
- [ ] API key authentication → **MUST HAVE** for MVP
- [ ] Rate limiting → **MUST HAVE** for MVP
- [ ] Record-level access control → **MUST HAVE** for MVP
- [ ] Advanced caching strategies → **MUST HAVE** for MVP
- [ ] Background job processing → **MUST HAVE** for MVP
- [ ] None - keep MVP scope as defined
All of this option for later versions. We don't have use cases for these features right now, and we want to keep the MVP scope as narrow as possible.

**Are there any "MUST HAVE" features that could be deferred to post-MVP?**

- [ ] ListSets verb → SHOULD HAVE (we don't use sets)
- [ ] Deleted record tracking → SHOULD HAVE (not critical for us)
- [ ] PostgreSQL adapter → SHOULD HAVE (we only use MySQL)
- [ ] Sets support → SHOULD HAVE
- [x] None - all MUST HAVEs are truly required

---

## Submission Instructions

**Please complete this form and return by:** [DATE]

**Return method:**
- Email to: [EMAIL]
- Create GitHub issue with responses
- Schedule review meeting to discuss

**Questions?**
Contact: [NAME/EMAIL]

---

## For Internal Use Only

**Form Version:** 1.0  
**Response Received:** [ ] Yes [ ] No  
**Date Received:** ______________  
**Reviewed By:** ______________  
**Requirements Updated:** [ ] Yes [ ] No  
**Update Date:** ______________

---

**End of Requirements Clarification Form**
