# Feature F-036: CLI Tools

**Feature ID:** F-036  
**Priority:** Post-MVP (NICE TO HAVE)  
**Phase:** 8 — Infrastructure (optional)  
**Bounded Context:** Infrastructure  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement command-line tools using Symfony Console for administrative tasks: configuration validation, health checks, cache purging, token cleanup, and database migration. The CLI entry point is `bin/oaipmh`.

## User Stories

**US-036.1:** As a **repository administrator**, I want CLI commands for validating configuration and purging caches so that I can manage the server without a web UI.

**US-036.2:** As a **DevOps engineer**, I want a CLI health check command for scripted monitoring and automation.

## Acceptance Criteria

- [ ] `bin/oaipmh` CLI entry point (Symfony Console-based)
- [ ] Commands:
  - `validate-config` — validate configuration file and report errors
  - `check-health` — run health checks from CLI
  - `purge-cache` — clear all cached OAI-PMH responses
  - `purge-tokens` — remove expired resumption tokens
- [ ] All commands return appropriate exit codes (0 = success, 1 = failure)
- [ ] Help text for all commands (`--help`)
- [ ] PHPStan Level 8 passes

## Dependencies

### Blocked By
- **F-002** Configuration Management (validate-config command)
- **F-024** Response Caching (purge-cache command)
- **F-020** Resumption Tokens (purge-tokens command)
- **F-033** Health Check Endpoint (check-health command)

### Blocks
- None (standalone operational feature)

## Files

```
bin/oaipmh
src/Infrastructure/Cli/ValidateConfigCommand.php
src/Infrastructure/Cli/CheckHealthCommand.php
src/Infrastructure/Cli/PurgeCacheCommand.php
src/Infrastructure/Cli/PurgeTokensCommand.php
tests/Infrastructure/Cli/ValidateConfigCommandTest.php
tests/Infrastructure/Cli/CheckHealthCommandTest.php
```

## Testing Requirements

- [ ] validate-config: valid config → success, invalid → failure with errors
- [ ] check-health: healthy → success, unhealthy → failure
- [ ] purge-cache: clears cache
- [ ] purge-tokens: removes expired tokens
- [ ] Exit codes correct

## Notes

- `composer require symfony/console` as a dependency.
- Consider adding an interactive installer command post-MVP: `oaipmh install`.
