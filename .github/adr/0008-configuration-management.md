# ADR-0008: Configuration Management

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect  
**Technical Story**: Repository Server Requirements - Section 2.5

---

## Context

Server configuration must be:
- **Externalized**: No hard-coded values
- **Environment-Aware**: Support dev, staging, production
- **Validated**: Catch errors early
- **Secure**: Sensitive values (passwords) not in version control

---

## Decision

Use **YAML configuration files** with Symfony Config component for validation and environment variable substitution.

### File Structure

```
config/
  config.yaml              # Main configuration
  database_mapping.yaml    # Database schema mapping
  metadata_formats.yaml    # Metadata format plugins
  .env                     # Environment variables (not in git)
  .env.example             # Example template
```

### Configuration Schema

```yaml
# config/config.yaml
repository:
  name: "My Repository"
  base_url: "https://repository.example.org/oai"
  admin_email: "%env(ADMIN_EMAIL)%"
  
database:
  driver: "%env(DB_DRIVER)%"
  host: "%env(DB_HOST)%"
  database: "%env(DB_NAME)%"
  username: "%env(DB_USER)%"
  password: "%env(DB_PASSWORD)%"
  
cache:
  enabled: true
  driver: redis
  redis:
    host: "%env(REDIS_HOST)%"
    port: "%env(int:REDIS_PORT)%"
```

### Environment Variables

```
# .env (not committed)
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=oai_pmh
DB_USER=oai_user
DB_PASSWORD=secret_password

REDIS_HOST=localhost
REDIS_PORT=6379

ADMIN_EMAIL=admin@example.org
```

---

## Validation

- [x] Configuration validates on startup
- [x] Environment variables interpolated correctly
- [x] Missing required values throw clear errors

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
