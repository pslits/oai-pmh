# Feature F-035: Database Migration Support

**Feature ID:** F-035  
**Priority:** Post-MVP (SHOULD HAVE)  
**Phase:** Cross-cutting  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement versioned database schema migrations for creating/updating the OAI-PMH server's own database tables (if needed) and for managing schema changes across versions. Includes a CLI command to run migrations and track migration status.

## User Stories

**US-035.1:** As a **repository administrator**, I want to run database migrations via CLI so that schema changes are applied safely and consistently during upgrades.

**US-035.2:** As a **developer**, I want versioned migration files with up/down support so that schema changes can be rolled back if needed.

## Acceptance Criteria

- [ ] Migration system integrated (Doctrine Migrations, Phinx, or custom)
- [ ] Versioned migration files (timestamp or sequential number)
- [ ] Up/down migrations for rollback capability
- [ ] CLI command: `bin/oaipmh migrate` to run pending migrations
- [ ] Migration status tracking (which migrations have been applied)
- [ ] Initial migration for OAI-PMH tables (if server manages its own tables)
- [ ] Migration for resumption token storage table (if using database tokens)
- [ ] Documentation for creating new migrations
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-002** Configuration Management (database connection config)
- **F-007** Database Adapters (database connectivity)

### Blocks
- None (standalone operational feature)

## Files

```
src/Infrastructure/Database/Migration/MigrationRunner.php
src/Infrastructure/Cli/MigrateCommand.php
database/migrations/                     (migration files directory)
tests/Infrastructure/Database/Migration/MigrationRunnerTest.php
```

## Testing Requirements

- [ ] Run pending migrations
- [ ] Skip already-applied migrations
- [ ] Rollback last migration
- [ ] Migration status reporting
- [ ] Invalid migration handling

## Notes

- The server primarily reads from existing databases — migrations may only be needed for server-managed tables (token storage, rate limit counters, etc.).
- `doctrine/migrations` or `robmorgan/phinx` are recommended libraries.
