# Requirements Clarification Response Summary

**Date Processed:** February 13, 2026  
**Source Document:** REQUIREMENTS_CLARIFICATION_FORM.md  
**Processed By:** Senior Business Analyst  
**Status:** ✅ Complete - Ready for Requirements Update

---

## Executive Summary

The customer completed the requirements clarification form with valuable insights that refine our MVP scope and technical targets:

### 🎯 Key Decisions:
- ✅ **Stricter Performance Targets:** < 500ms (not < 1s) for ListRecords first page
- ✅ **Higher Quality Standards:** 90-100% code coverage (not 80%), maintain PHPStan Level 8
- ✅ **HTTPS Enforcement:** REQUIRED for MVP with `force_https: true` config option
- ✅ **Massive Scale Support:** Targeting > 10M records (hundreds of millions possible)
- ✅ **Keep MVP Lean:** Defer all authentication, rate limiting, and background jobs to post-MVP
- ✅ **Three New Requirements:** Audit trail, backup/restore, multi-tenancy (investigate for v1.1+)

### 📊 Impact Assessment:
- **Requirements Updated:** 23 sections require updates
- **MVP Scope Changes:** HTTPS enforcement promoted to MUST HAVE
- **Post-MVP Additions:** Audit trail, backup/restore, multi-tenancy features
- **Documentation Priorities:** Installation, Configuration, How-To guides ranked #1

---

## Section-by-Section Responses

### Section 1: User Stories & Use Cases

**✅ Provided stories APPROVED:**
- Story 1: Quick Deployment (< 1 hour) - APPROVED
- Story 2: Configuration Management (YAML-only) - APPROVED
- Story 5: Plugin Development (custom metadata formats) - APPROVED
- Story 6: Database Integration (schema mapping) - APPROVED
- Story 8: Standards Compliance (OAI-PMH 2.0) - APPROVED
- Story 9: Performance (< 1s first page) - APPROVED

**⚠️ Additional Stories REQUESTED but NOT PROVIDED:**
- Story 3-4: Repository Administrator stories (BLANK - use defaults if needed)
- Story 7: Software Developer story (BLANK - use defaults if needed)
- Story 10: Harvester story (BLANK - use defaults if needed)

**📝 Action Items:**
- [ ] Add default user stories for missing Story 3, 4, 7, 10
- [ ] Review user stories with development team

---

### Section 2: Resource Limits & Performance

#### 2.1 System Resource Limits

| Resource | Customer Response | Requirements Impact |
|----------|-------------------|---------------------|
| **Memory Limits** | No limit / Let admin configure | Make `memory_limit` optional config, document recommended values (512MB-1GB) |
| **Execution Time** | Configurable per environment | Add `max_execution_time` config (default: 60s, range: 30-120s) |
| **Max Request Size** | No limit needed (GET-based) | No change - OAI-PMH uses GET, not POST |
| **DB Connection Pool** | NOT ANSWERED | Use default: 20 connections, configurable |
| **Query Timeout** | ✅ 5 seconds | Add `query_timeout: 5` requirement |

**📝 Action Items:**
- [ ] Add configurable memory_limit to requirements (FR-CONFIG-008)
- [ ] Add configurable max_execution_time to requirements (FR-CONFIG-009)
- [ ] Add query_timeout: 5s requirement (NFR-PERF-007)
- [ ] Document recommended memory limits in deployment section

---

### Section 3: Security & DDoS Protection

#### 3.1 HTTPS Enforcement

**Customer Response:** ✅ **YES - REQUIRED for MVP**

```yaml
force_https: true   # Reject HTTP requests, HTTPS only
```

**Impact:** PROMOTE from NICE TO HAVE → **MUST HAVE (MVP)**

**📝 Action Items:**
- [ ] Add FR-SEC-009: HTTPS Enforcement (MUST HAVE)
- [ ] Add `force_https` config option to FR-CONFIG section
- [ ] Update security requirements table
- [ ] Add implementation guidance: redirect or reject HTTP requests

#### 3.2 DDoS Protection Details

| Protection Type | Customer Response | Requirements Impact |
|----------------|-------------------|---------------------|
| **Connection Limits** | ❌ "for harvesting large data sets this shouldn't be limited" | Do NOT add connection limits |
| **Request Size Validation** | ✅ Reject query strings > 2KB | Add FR-SEC-010: Validate URL query length |
| **Slowloris Protection** | ✅ Timeout slow connections | Add FR-SEC-011: Connection timeout for slow clients |
| **IP Blacklisting** | ⚠️ Not sure | DEFER to post-MVP (NICE TO HAVE) |

#### 3.3 Security Logging

**Customer Response:** ✅ **All of the above**

Security events to log:
- ✅ Authentication attempts (when implemented)
- ✅ Rate limit violations
- ✅ Suspicious patterns (SQL injection, path traversal attempts)
- ✅ IP addresses accessing restricted records

**IP Address Logging:** ✅ **With GDPR-compliant anonymization**
- Format: `192.168.1.XXX` (last octet masked)

**📝 Action Items:**
- [ ] Add FR-LOG-005: Comprehensive security event logging
- [ ] Add FR-PRIVACY-001: GDPR-compliant IP anonymization (last octet masked)
- [ ] Update logging requirements with security event types
- [ ] Add IP anonymization implementation guidance

---

### Section 4: Error Message Usability

#### 4.1 Error Message Design

| Audience | Customer Response | Requirements Impact |
|----------|-------------------|---------------------|
| **Admin Logs** | ✅ User-friendly (plain English) | Add NFR-ERROR-001: Human-readable log messages |
| **Harvester Errors** | ✅ Include fix suggestions | Add NFR-ERROR-002: Actionable OAI-PMH error messages |
| **Developer Debug** | ✅ Both stack traces + structured context | Add NFR-ERROR-003: Debug mode with verbose output |

#### 4.2 Error Message Style

**Customer Choice:** ✅ **Option 1 - Brief Explanation**

```xml
<error code="badArgument">The 'from' parameter must be a valid UTC date in ISO 8601 format</error>
```

**NOT chosen:**
- ❌ Minimalist (code only)
- ❌ Option 2 (explanation + example - too verbose)
- ❌ Configurable

**📝 Action Items:**
- [ ] Add NFR-ERROR-004: OAI-PMH error format with brief explanations
- [ ] Add error message examples to requirements
- [ ] Document error message standards in design section

---

### Section 5: Documentation Requirements

#### 5.1 Documentation Priorities (Ranked 1-8)

| Rank | Documentation Type | Priority Level |
|------|-------------------|----------------|
| **1 (Highest)** | Installation Guide | CRITICAL |
| **1** | Configuration Guide | CRITICAL |
| **1** | How-To Guides | CRITICAL |
| **1** | OAI-PMH Endpoint Docs | CRITICAL |
| **2** | Developer Guide | HIGH |
| **5** | API Reference | MEDIUM |
| **5** | Troubleshooting Guide | MEDIUM |
| **8 (Lowest)** | Migration Guide | LOW |

#### 5.2 Documentation Format

**Customer Response:** ✅ **Markdown files in repository**

- Simple, version-controlled
- No HTML generation needed
- Easy to read on GitHub

**📝 Action Items:**
- [ ] Update documentation requirements with priority ranking
- [ ] Specify Markdown as primary format
- [ ] Create documentation checklist for MVP (rank 1-2 items)
- [ ] Defer migration guide to post-MVP

---

### Section 6: MVP Scope Validation

#### 6.1 Authentication

**Customer Response:** ✅ **Public access fine for MVP - add auth in v1.1**

**Decision:** Keep authentication as **NICE TO HAVE (post-MVP)**

#### 6.2 Rate Limiting

**Customer Response:** ✅ **Can wait for v1.1**

**Decision:** Keep rate limiting as **NICE TO HAVE (post-MVP)**

#### 6.3 Background Job Processing

**Customer Response:** ❌ **"Can we think about this for a later version? We don't have a use case for it right now."**

**Decision:** Move background jobs from SHOULD HAVE → **NICE TO HAVE (post-MVP)**

**📝 Action Items:**
- [ ] Confirm authentication remains NICE TO HAVE
- [ ] Confirm rate limiting remains NICE TO HAVE
- [ ] DEMOTE background job processing: SHOULD HAVE → NICE TO HAVE
- [ ] Update MVP scope section with clear rationale

---

### Section 7: Deployment Environment Details

#### 7.1 Deployment Platforms

**Customer Selected:**
- ✅ On-premise Linux servers (Ubuntu, CentOS, Debian)
- ✅ Windows Server
- ✅ XAMPP or similar local development environments

**NOT selected:**
- ❌ Cloud VMs
- ❌ Docker (single host)
- ❌ Kubernetes
- ❌ PaaS (Heroku, Platform.sh)
- ❌ Shared hosting (cPanel)

**Insight:** Focus on traditional server deployments + local dev environments

#### 7.2 Database Platforms

**Customer Response:**
- ✅ MySQL (version not specified)
- ✅ **"Extendable to any database with a PHP adapter"**

**Schema:**
- ✅ **Both migration + greenfield deployments**

#### 7.3 Repository Scale

**Current Scale:**
- ✅ Large: 100,000 - 1,000,000 records

**Future Scale:**
- ✅ Later... Massive: **> 10M records**
- **Quote:** "could be in the hundreds of millions or more"

**📝 Action Items:**
- [ ] Update deployment platforms list
- [ ] Clarify MySQL version requirements (recommend 5.7+ or 8.0+)
- [ ] Add "database adapter extensibility" requirement
- [ ] Update performance targets to handle **> 10M records**
- [ ] Add scalability requirement: Support hundreds of millions of records
- [ ] Update test scenarios: Use 1M record dataset (not 100K)

---

### Section 8: Additional Requirements

#### 8.1 New Features Requested

**Customer marked with "x":**
1. ✅ **Audit trail / change history**
2. ✅ **Backup and restore features**
3. ✅ **Multi-tenancy (one server, multiple repositories)**

**Analysis:** These are all POST-MVP features but should be investigated for v1.1+

**📝 Action Items:**
- [ ] Add FR-AUDIT-001: Audit trail / change history (NICE TO HAVE - v1.1)
- [ ] Add FR-BACKUP-001: Backup and restore features (NICE TO HAVE - v1.1)
- [ ] Add FR-MULTITENANT-001: Multi-tenancy support (NICE TO HAVE - v2.0)
- [ ] Create post-MVP roadmap section with these features

#### 8.2 Non-Functional Requirements Validation

| Requirement | Current Target | Customer Response | New Target |
|-------------|----------------|-------------------|------------|
| **Performance** | < 1s | ❌ Too lenient, need < 500ms | **< 500ms** |
| **Code Coverage** | 80%+ | ❌ Too lenient, want 90-100% | **90-100%** |
| **PHPStan Level** | Level 8 | ✅ Keep Level 8 | **Level 8** (no change) |

**📝 Action Items:**
- [ ] **UPDATE NFR-PERF-001:** Response time < **500ms** (stricter)
- [ ] **UPDATE NFR-TEST-002:** Code coverage **90-100%** (stricter)
- [ ] Update performance testing requirements
- [ ] Update CI/CD pipeline: Fail build if coverage < 90%

---

### Section 9: Success Metrics Validation

#### MVP Success Criteria Adjustments

| Criterion | Current | Customer Feedback | Updated |
|-----------|---------|-------------------|---------|
| 1. Harvest volume | 100,000 records | ⚠️ Need higher volume | **1,000,000 records** |
| 2. Response times | < 1s | ✅ Agree | **< 500ms** (from Section 8) |
| 3. OAI-PMH Validator | Passes all tests | ✅ Agree | No change |
| 4. Install time | < 1 hour | ✅ Agree | No change |
| 5. External deployment | 1 organization | ✅ Agree | No change |
| 6. Automated tests | All pass | ✅ Agree | Add: 90% coverage |
| 7. Quality gates | PHPStan L8 + PSR-12 | ✅ Agree | No change |
| 8. Demo | Docker + sample data | ✅ Agree | No change |

**📝 Action Items:**
- [ ] Update success criterion #1: Harvest **1,000,000 records** (not 100K)
- [ ] Update success criterion #2: Response time **< 500ms** (not < 1s)
- [ ] Update success criterion #6: Add "90% code coverage" requirement

---

### Section 10: Timeline & Prioritization

#### 10.1 Timeline

**Customer Response:** ✅ **No deadline pressure / timeline flexible**

**Current estimate:** 3-6 months for MVP

**Decision:** Keep 3-6 month estimate, no pressure to accelerate

#### 10.2 Feature Prioritization

**Customer Response (NICE TO HAVE → MUST HAVE promotions):**
- ❌ HTTP Basic Auth → Keep as NICE TO HAVE
- ❌ API key authentication → Keep as NICE TO HAVE
- ❌ Rate limiting → Keep as NICE TO HAVE
- ❌ Record-level access control → Keep as NICE TO HAVE
- ❌ Advanced caching → Keep as NICE TO HAVE
- ❌ Background jobs → Keep as NICE TO HAVE
- ✅ **None - keep MVP scope as defined**

**Quote:** "All of this option for later versions. We don't have use cases for these features right now, and we want to keep the MVP scope as narrow as possible."

**Customer Response (MUST HAVE → SHOULD HAVE demotions):**
- ❌ No demotions requested
- ✅ **All MUST HAVEs are truly required**

**📝 Action Items:**
- [ ] Confirm all MUST HAVEs remain in MVP
- [ ] Confirm all NICE TO HAVEs deferred to post-MVP
- [ ] Add rationale: "Customer prioritizes lean MVP delivery"

---

## Summary of Changes to Requirements Document

### 🔴 CRITICAL Updates (MUST HAVE Changes)

1. **FR-SEC-009:** Add HTTPS enforcement requirement (PROMOTED to MUST HAVE)
2. **NFR-PERF-001:** Update response time target: < **500ms** (stricter)
3. **NFR-TEST-002:** Update code coverage target: **90-100%** (stricter)
4. **NFR-SCALE-001:** Support **> 10M records** (hundreds of millions)

### 🟡 HIGH Priority Updates

5. **FR-SEC-010:** Add request size validation (query strings > 2KB rejected)
6. **FR-SEC-011:** Add Slowloris protection (timeout slow connections)
7. **FR-LOG-005:** Add comprehensive security event logging
8. **FR-PRIVACY-001:** Add GDPR-compliant IP anonymization
9. **FR-CONFIG-008:** Add configurable memory_limit
10. **FR-CONFIG-009:** Add configurable max_execution_time
11. **NFR-ERROR-001/002/003/004:** Add error message usability requirements

### 🟢 MEDIUM Priority Updates

12. **Documentation Priorities:** Update ranking (Installation/Config/How-To = #1)
13. **Documentation Format:** Specify Markdown in repository
14. **Deployment Platforms:** Clarify Linux, Windows Server, XAMPP focus
15. **Database Extensibility:** Add "any database with PHP adapter" requirement
16. **Success Criteria:** Update harvest volume to 1M records
17. **Background Jobs:** Demote from SHOULD HAVE → NICE TO HAVE

### 🔵 POST-MVP Features Added

18. **FR-AUDIT-001:** Audit trail / change history (v1.1)
19. **FR-BACKUP-001:** Backup and restore features (v1.1)
20. **FR-MULTITENANT-001:** Multi-tenancy support (v2.0)

---

## Requirements Update Checklist

### Sections to Update:

- [ ] **2. System Architecture** - Update performance targets in diagrams
- [ ] **3.1 Functional Requirements - Core OAI-PMH** - No changes
- [ ] **3.2 Configuration Management** - Add memory_limit, max_execution_time, force_https
- [ ] **3.3 Security & Access Control** - Add HTTPS enforcement, request validation, Slowloris
- [ ] **3.4 Logging & Monitoring** - Add security events, IP anonymization
- [ ] **4. Non-Functional Requirements** - Update performance (500ms), coverage (90%), scale (10M+)
- [ ] **5. MVP Scope** - Demote background jobs, add HTTPS enforcement
- [ ] **6. Technical Requirements** - Update database requirements
- [ ] **7. Deployment & Installation** - Update platforms, document priorities
- [ ] **8. Standards & Compliance** - Add GDPR privacy requirement
- [ ] **9. Success Metrics** - Update volume (1M), response time (500ms), coverage (90%)
- [ ] **11. Post-MVP Roadmap** - Add audit trail, backup/restore, multi-tenancy

---

## Next Steps

1. ✅ **Mark clarification form as processed** (add timestamp)
2. ⏳ **Update requirements document** (23 sections)
3. ⏳ **Run quality check** (verify all responses addressed)
4. ⏳ **Get stakeholder approval** (requirements v1.2)
5. ⏳ **Notify architect** (requirements finalized for design)

---

## Appendix: Unanswered Questions

**Questions Left Blank (use reasonable defaults):**

1. User Story #3-4 (Repository Administrator) - **Default:** Use generic admin stories
2. User Story #7 (Software Developer) - **Default:** Use generic developer story  
3. User Story #10 (Harvester) - **Default:** Use generic harvester story
4. Database connection pool size - **Default:** 20 connections (configurable)
5. MySQL version - **Default:** Require MySQL 5.7+ or 8.0+ (document both)

---

**Document Status:** ✅ Complete  
**Processing Time:** [Calculated by system]  
**Responses Analyzed:** 50+ questions  
**Requirements Changes:** 23 sections affected  
**New Features:** 3 (post-MVP)  
**Quality Gate Changes:** 2 (performance + coverage stricter)

---

*This summary was generated by automated analysis of REQUIREMENTS_CLARIFICATION_FORM.md completed by the customer on February 13, 2026.*
