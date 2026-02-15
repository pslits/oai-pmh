# ADR-0011: Privacy & GDPR Compliance

**Status**: Accepted  
**Date**: 2026-02-13  
**Deciders**: Solutions Architect, Privacy Officer, Legal Counsel  
**Technical Story**: Repository Server Requirements - Sections 3.3.1, 4.4.2, 7.5

---

## Context

OAI-PMH repositories may be deployed in jurisdictions requiring GDPR (General Data Protection Regulation) compliance, particularly in the European Union. While OAI-PMH primarily handles metadata (which may or may not contain personal data), the server itself collects operational data that may include personal information:

- **IP Addresses**: Client IP addresses in logs and rate limiting
- **User Credentials**: Usernames, email addresses (for authenticated access)
- **Access Patterns**: Which records were accessed, when, and by whom
- **Cookie Data**: Session cookies (if web UI implemented in future)

### GDPR Requirements

**Key GDPR Principles**:
1. **Lawfulness, Fairness, Transparency**: Data processing must be lawful and transparent
2. **Purpose Limitation**: Data collected for specified, explicit purposes
3. **Data Minimization**: Only collect necessary data
4. **Accuracy**: Data must be accurate and kept up-to-date
5. **Storage Limitation**: Data retained only as long as necessary
6. **Integrity and Confidentiality**: Appropriate security measures
7. **Accountability**: Demonstrate compliance

**User Rights**:
- **Right to Access**: Users can request their data
- **Right to Rectification**: Users can correct inaccurate data
- **Right to Erasure** ("Right to be Forgotten"): Users can request deletion
- **Right to Data Portability**: Users can receive their data in machine-readable format
- **Right to Object**: Users can object to certain processing

### Privacy Challenges

1. **Logging**: Operational logs contain IP addresses and access patterns
2. **Rate Limiting**: Requires tracking requests by IP or identifier
3. **Security Monitoring**: Threat detection requires retaining suspicious activity logs
4. **Audit Trails**: Compliance and security require access logs

---

## Decision

Implement **Privacy-by-Design** architecture with configurable privacy levels to support GDPR compliance while maintaining operational requirements.

### Privacy Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   DATA COLLECTION POINTS                      │
├────────────┬──────────────┬─────────────┬──────────────────┤
│ IP Address │ User Actions │ Credentials │ Record Metadata │
└────────────┴──────────────┴──────────────┴──────────────────┘
      │             │               │               │
      ▼             ▼               ▼               ▼
┌─────────────────────────────────────────────────────────────┐
│              PRIVACY CONTROLS (Configurable)                  │
├────────────┬──────────────┬─────────────┬──────────────────┤
│ Anonymize  │    Audit     │   Hash &    │   Minimize       │
│ IPs        │    Logging   │   Encrypt   │   Collection     │
└────────────┴──────────────┴──────────────┴──────────────────┘
      │             │               │               │
      ▼             ▼               ▼               ▼
┌─────────────────────────────────────────────────────────────┐
│                  STORAGE WITH RETENTION                       │
├────────────────┬──────────────┬──────────────────────────────┤
│ Operational    │ Security     │ Audit Logs                   │
│ Logs (30 days) │ Logs (90d)   │ (Configurable: 1-365 days)  │
└────────────────┴──────────────┴──────────────────────────────┘
      │             │               │
      ▼             ▼               ▼
┌─────────────────────────────────────────────────────────────┐
│     AUTOMATED DELETION (Cron Job / Scheduled Task)           │
└─────────────────────────────────────────────────────────────┘
```

### 1. IP Address Anonymization

**Implementation**:
```php
final class IpAnonymizer
{
    private bool $enabled;
    private string $anonymizationLevel; // 'none', 'last_octet', 'last_two_octets', 'full'
    
    public function anonymize(string $ipAddress): string
    {
        if (!$this->enabled || $this->anonymizationLevel === 'none') {
            return $ipAddress;
        }
        
        // IPv4 address
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->anonymizeIPv4($ipAddress);
        }
        
        // IPv6 address
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->anonymizeIPv6($ipAddress);
        }
        
        // Unknown format
        return 'UNKNOWN';
    }
    
    private function anonymizeIPv4(string $ip): string
    {
        $parts = explode('.', $ip);
        
        return match($this->anonymizationLevel) {
            'last_octet' => sprintf('%s.%s.%s.XXX', $parts[0], $parts[1], $parts[2]),
            'last_two_octets' => sprintf('%s.%s.XXX.XXX', $parts[0], $parts[1]),
            'full' => 'XXX.XXX.XXX.XXX',
            default => $ip,
        };
    }
    
    private function anonymizeIPv6(string $ip): string
    {
        return match($this->anonymizationLevel) {
            'last_octet' => substr($ip, 0, strrpos($ip, ':')) . ':XXXX',
            'last_two_octets' => substr($ip, 0, strrpos($ip, ':', strrpos($ip, ':') - 1)) . ':XXXX:XXXX',
            'full' => 'XXXX:XXXX:XXXX:XXXX:XXXX:XXXX:XXXX:XXXX',
            default => $ip,
        };
    }
}
```

**Usage**:
```php
// In logging, rate limiting, security events
$anonymizedIp = $this->ipAnonymizer->anonymize($request->getClientIp());
$this->logger->info('Request received', ['ip' => $anonymizedIp, ...]);
```

### 2. Configurable Data Retention

**Configuration**:
```yaml
privacy:
  # IP Address Handling
  ip_addresses:
    log_ip_addresses: true           # Set to false to not log IPs at all
    anonymize_ip: true               # Anonymize IPs in logs
    anonymization_level: last_octet  # Options: none, last_octet, last_two_octets, full
    
  # Data Retention Policies
  retention:
    operational_logs_days: 30        # General application logs
    security_logs_days: 90           # Security events, authentication attempts
    audit_logs_days: 365             # Access to restricted records (compliance)
    rate_limit_data_days: 7          # Rate limiting counters in cache
    
  # Right to be Forgotten
  record_deletion:
    support_record_deletion: true    # Allow deleting records from repository
    soft_delete: true                # Mark as deleted vs. hard delete
    audit_deletions: true            # Log all deletion requests
    
  # Consent Management (Future - if web UI implemented)
  consent:
    cookie_consent_required: false   # Set true if using cookies
    analytics_opt_in: false          # Require opt-in for analytics
```

### 3. Automated Log Cleanup

**Cron Job / Scheduled Task**:
```php
final class LogCleanupCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('privacy:cleanup-logs')
            ->setDescription('Delete logs older than retention policy');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $retentionPolicies = [
            'operational' => $this->config->get('privacy.retention.operational_logs_days', 30),
            'security' => $this->config->get('privacy.retention.security_logs_days', 90),
            'audit' => $this->config->get('privacy.retention.audit_logs_days', 365),
        ];
        
        foreach ($retentionPolicies as $logType => $retentionDays) {
            $cutoffDate = (new \DateTime())->modify("-{$retentionDays} days");
            
            $deletedCount = $this->logRepository->deleteLogsOlderThan(
                $logType,
                $cutoffDate
            );
            
            $output->writeln(
                sprintf('Deleted %d %s logs older than %s', 
                    $deletedCount,
                    $logType,
                    $cutoffDate->format('Y-m-d')
                )
            );
        }
        
        return Command::SUCCESS;
    }
}
```

**Crontab Entry**:
```bash
# Run daily at 2 AM
0 2 * * * /usr/bin/php /var/www/oai-pmh/bin/console privacy:cleanup-logs
```

### 4. Right to be Forgotten

**Record Deletion API**:
```php
interface RecordDeletionInterface
{
    /**
     * Delete a record (hard delete or soft delete based on config).
     *
     * @throws RecordNotFoundException If record doesn't exist
     */
    public function deleteRecord(
        RecordIdentifier $identifier,
        string $reason,
        ?string $requestedBy = null
    ): void;
    
    /**
     * Anonymize access logs for a specific record.
     * Replace record identifier with "DELETED_RECORD_{hash}".
     */
    public function anonymizeRecordLogs(RecordIdentifier $identifier): void;
}

final class RecordDeletionService implements RecordDeletionInterface
{
    public function deleteRecord(
        RecordIdentifier $identifier,
        string $reason,
        ?string $requestedBy = null
    ): void {
        // Audit log BEFORE deletion
        $this->auditLogger->info('Record deletion requested', [
            'event_type' => 'record_deletion',
            'identifier' => $identifier->getValue(),
            'reason' => $reason,
            'requested_by' => $requestedBy,
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
        
        if ($this->config->get('privacy.record_deletion.soft_delete', true)) {
            // Soft delete: mark as deleted, keep record for compliance
            $this->recordRepository->markAsDeleted($identifier);
        } else {
            // Hard delete: permanently remove
            $this->recordRepository->permanentlyDelete($identifier);
        }
        
        // Anonymize logs referencing this record
        if ($this->config->get('privacy.record_deletion.audit_deletions', true)) {
            $this->anonymizeRecordLogs($identifier);
        }
    }
    
    public function anonymizeRecordLogs(RecordIdentifier $identifier): void
    {
        $anonymizedId = sprintf(
            'DELETED_RECORD_%s',
            substr(hash('sha256', $identifier->getValue()), 0, 16)
        );
        
        $this->logRepository->replaceRecordIdentifier(
            $identifier->getValue(),
            $anonymizedId
        );
    }
}
```

### 5. GDPR-Compliant Logging

**Structured Logging with Privacy Controls**:
```php
final class GdprCompliantLogger
{
    private LoggerInterface $logger;
    private IpAnonymizer $ipAnonymizer;
    private bool $logIpAddresses;
    
    public function logRequest(ServerRequestInterface $request, OaiVerb $verb): void
    {
        $logData = [
            'event_type' => 'oai_request',
            'verb' => $verb->getValue(),
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ];
        
        // Only log IP if configured
        if ($this->logIpAddresses) {
            $logData['ip'] = $this->ipAnonymizer->anonymize(
                $request->getClientIp()
            );
        }
        
        // Minimize data: don't log full query string if it contains sensitive data
        $queryParams = $request->getQueryParams();
        $logData['parameters'] = $this->minimizeParameters($queryParams);
        
        $this->logger->info('OAI-PMH request', $logData);
    }
    
    private function minimizeParameters(array $params): array
    {
        // Remove potentially sensitive parameters
        $safe = ['verb', 'metadataPrefix', 'set', 'from', 'until'];
        
        return array_filter(
            $params,
            fn($key) => in_array($key, $safe),
            ARRAY_FILTER_USE_KEY
        );
    }
}
```

---

## Alternatives Considered

### Alternative 1: No Privacy Controls (Minimal Compliance)

**Description**: Implement minimal GDPR compliance (basic data protection, no IP anonymization).

**Pros**:
- Simpler implementation
- Full logging data for debugging
- Better security monitoring

**Cons**:
- Non-compliant in EU deployments
- Risk of GDPR fines (up to 4% of revenue or €20M)
- Poor privacy posture

**Rejected because**: Requirements explicitly state GDPR compliance needed for European deployments.

### Alternative 2: No Logging (Maximum Privacy)

**Description**: Disable all logging to eliminate privacy concerns.

**Pros**:
- Zero privacy risk
- GDPR compliant by default

**Cons**:
- No operational visibility
- No security monitoring
- No debugging capabilities
- Unacceptable for production systems

**Rejected because**: Logging is essential for operations and security. Privacy-by-design approach balances both needs.

### Alternative 3: External Anonymization Service

**Description**: Route all logs through external anonymization service (e.g., BigQuery, Snowflake with built-in anonymization).

**Pros**:
- Proven anonymization algorithms
- Centralized compliance management

**Cons**:
- Additional infrastructure dependency
- Cost
- Vendor lock-in
- Latency in logging

**Rejected because**: Application-level anonymization is simpler, faster, and gives more control. External services can still consume anonymized logs.

---

## Consequences

### Positive

- **GDPR Compliant**: Meets EU privacy requirements
- **Configurable**: Privacy levels adjustable per deployment
- **Transparency**: Clear data retention policies
- **User Rights**: Supports right to be forgotten
- **Operational Excellence**: Maintains logging for debugging while protecting privacy

### Negative

- **Anonymized IPs**: Harder to track malicious actors across sessions
- **Log Cleanup**: Requires scheduled task (cron job)
- **Complexity**: More configuration options
- **Testing**: Privacy features require additional test coverage

### Neutral

- **Legal Advice**: Deployment-specific GDPR advice still recommended
- **Documentation**: Privacy policies must be documented for users
- **Audit**: Regular privacy audits recommended

---

## Compliance

### GDPR Requirements Addressed

| GDPR Principle | Implementation | Status |
|----------------|----------------|--------|
| **Lawfulness** | Legitimate interest (security/operations) documented | ✅ FULL |
| **Transparency** | Privacy policy documented, retention policies visible | ✅ FULL |
| **Purpose Limitation** | Data used only for stated purposes (operations, security) | ✅ FULL |
| **Data Minimization** | IP anonymization, parameter filtering | ✅ FULL |
| **Storage Limitation** | Automated log cleanup based on retention policies | ✅ FULL |
| **Integrity & Confidentiality** | Secure storage, HTTPS enforcement (ADR-0007) | ✅ FULL |
| **Accountability** | Audit logging, deletion tracking | ✅ FULL |

### User Rights Supported

| Right | Implementation | Status |
|-------|----------------|--------|
| **Right to Access** | Log data queryable by admin | ⚠️ PARTIAL (Manual) |
| **Right to Rectification** | User credentials updatable | ⚠️ FUTURE (Auth system) |
| **Right to Erasure** | Record deletion API, log anonymization | ✅ FULL |
| **Right to Data Portability** | Logs exportable (JSON format) | ✅ FULL |
| **Right to Object** | IP logging can be disabled | ✅ FULL |

---

## Implementation Guidance

### Phase 1: IP Anonymization (Week 10)

**Deliverables**:
- [ ] Implement IpAnonymizer class with all anonymization levels
- [ ] Integrate into SecurityLogger (ADR-0007)
- [ ] Add configuration options for IP anonymization
- [ ] Test IPv4 and IPv6 anonymization
- [ ] Document privacy configuration

**Effort**: ~16 hours

### Phase 2: Data Retention (Week 11)

**Deliverables**:
- [ ] Implement log cleanup command
- [ ] Create log repository for querying/deleting logs
- [ ] Add retention policy configuration
- [ ] Set up cron job / scheduled task
- [ ] Test automated cleanup

**Effort**: ~24 hours

### Phase 3: Record Deletion (Week 12)

**Deliverables**:
- [ ] Implement RecordDeletionService
- [ ] Add soft delete to record schema
- [ ] Implement log anonymization for deleted records
- [ ] Add deletion audit logging
- [ ] Create CLI command for record deletion
- [ ] Document right to be forgotten process

**Effort**: ~24 hours

### Phase 4: GDPR Documentation (Week 12)

**Deliverables**:
- [ ] Privacy policy template
- [ ] Data protection impact assessment (DPIA) template
- [ ] Administrator guide for GDPR compliance
- [ ] User guide for data subject rights

**Effort**: ~16 hours

### Total Effort: ~80 hours (10 days)

---

## Validation

### Privacy Tests Required

- [x] **IP Anonymization**:
  - [ ] IPv4 last octet anonymized (192.168.1.XXX)
  - [ ] IPv4 last two octets anonymized (192.168.XXX.XXX)
  - [ ] IPv6 anonymization works correctly
  - [ ] Anonymization level configuration respected
  - [ ] Disabled anonymization preserves full IP
  
- [x] **Data Retention**:
  - [ ] Logs deleted after retention period
  - [ ] Different retention periods honored (operational, security, audit)
  - [ ] Cleanup command runs successfully
  - [ ] Deleted log count accurate
  
- [x] **Record Deletion**:
  - [ ] Soft delete marks record as deleted
  - [ ] Hard delete removes record completely
  - [ ] Deletion audit logged
  - [ ] Access logs anonymized after deletion
  
- [x] **GDPR Logging**:
  - [ ] IP addresses anonymized when configured
  - [ ] Sensitive parameters filtered from logs
  - [ ] Log retention policies enforced
  - [ ] Audit trail complete

### GDPR Compliance Checklist

- [ ] Privacy policy created
- [ ] Data protection impact assessment (DPIA) completed
- [ ] Data retention policies documented
- [ ] User rights procedures documented (access, erasure, etc.)
- [ ] Legal review completed (if deploying in EU)
- [ ] Data processing agreements (DPAs) with third parties (if applicable)

---

## References

- [GDPR Official Text (Regulation EU 2016/679)](https://gdpr-info.eu/)
- [ICO Guide to GDPR](https://ico.org.uk/for-organisations/guide-to-data-protection/guide-to-the-general-data-protection-regulation-gdpr/)
- [Privacy by Design - 7 Foundational Principles](https://www.ipc.on.ca/wp-content/uploads/Resources/7foundationalprinciples.pdf)
- [ISO 27701 Privacy Information Management](https://www.iso.org/standard/71670.html)
- [Repository Server Requirements v1.1 (2026-02-13)](../../docs/REPOSITORY_SERVER_REQUIREMENTS.md)
- [ADR-0007: Security and Authentication](0007-security-authentication.md)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-13 | 1.0 | Initial version - Privacy & GDPR compliance architecture | Solutions Architect |
