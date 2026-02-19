# Feature F-038: Documentation & Developer Guides

**Feature ID:** F-038  
**Priority:** MVP (MUST HAVE)  
**Phase:** 10 — Documentation & Release  
**Bounded Context:** Cross-cutting  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Create comprehensive documentation for the OAI-PMH server covering quick start, configuration reference, schema mapping guide, plugin development guide, architecture overview, and deployment guide. Documentation is essential for adoption by repository administrators and plugin developers.

## User Stories

**US-038.1:** As a **repository administrator**, I want a quick start guide so that I can get a working OAI-PMH server running in under 30 minutes.

**US-038.2:** As a **repository administrator**, I want a complete configuration reference so that I can configure every aspect of the server.

**US-038.3:** As a **developer**, I want a schema mapping guide so that I can connect the server to my existing database.

**US-038.4:** As a **plugin developer**, I want a metadata format plugin guide so that I can create custom metadata formats for my repository.

## Acceptance Criteria

### Required Documents
- [ ] `README.md` — Project overview, installation, quick start
- [ ] `docs/QUICKSTART.md` — Step-by-step setup guide (<30 min)
- [ ] `docs/CONFIGURATION_REFERENCE.md` — All configuration options documented
- [ ] `docs/SCHEMA_MAPPING_GUIDE.md` — How to map existing databases
- [ ] `docs/WRITING_PLUGINS.md` — How to create metadata format plugins
- [ ] `docs/ARCHITECTURE.md` — Architecture overview, bounded contexts, DDD
- [ ] `docs/DEPLOYMENT.md` — Apache/Nginx configuration, PHP requirements
- [ ] `docs/TROUBLESHOOTING.md` — Common issues and solutions
- [ ] `CHANGELOG.md` — Version history (starting with v0.1.0)
- [ ] `CONTRIBUTING.md` — Contribution guidelines

### Documentation Quality
- [ ] All code examples are tested and functional
- [ ] Configuration file examples are complete and correct
- [ ] Inline documentation (PHPDoc) is comprehensive
- [ ] API reference generated from PHPDoc (optional)
- [ ] Diagrams (Mermaid) for architecture and request flow

### Specific Content Requirements
- [ ] QUICKSTART includes: install via Composer, create config YAML, configure DB, test with curl
- [ ] CONFIGURATION_REFERENCE lists every YAML key with type, default, description, example
- [ ] SCHEMA_MAPPING_GUIDE includes at least 3 real-world database examples
- [ ] WRITING_PLUGINS includes a complete example plugin from scratch
- [ ] DEPLOYMENT covers Apache `.htaccess`, Nginx `location` block, PHP-FPM

## Dependencies

### Blocked By
- **F-002** Configuration Management (configuration reference)
- **F-006** Database Schema Mapping (schema mapping guide)
- **F-017** Metadata Format Plugin System (plugin guide)
- **F-023** HTTP Entry Point & Middleware Pipeline (deployment guide)

### Blocks
- **F-039** Composer Package & Release (docs required before release)

## Files

```
README.md                                  (update)
CHANGELOG.md                               (create/update)
CONTRIBUTING.md                            (create/update)
docs/QUICKSTART.md
docs/CONFIGURATION_REFERENCE.md
docs/SCHEMA_MAPPING_GUIDE.md
docs/WRITING_PLUGINS.md
docs/ARCHITECTURE.md
docs/DEPLOYMENT.md
docs/TROUBLESHOOTING.md
```

## Testing Requirements

- [ ] All code examples compile/run correctly
- [ ] Configuration examples validate without errors
- [ ] Quick start guide tested end-to-end on fresh install
- [ ] Plugin example functions with the server

## Notes

- Use Mermaid diagrams in the architecture doc for: bounded context map, request flow, middleware pipeline, plugin loading.
- Configuration reference can be partially auto-generated from the ConfigValidator.
- Consider adding a `docs/FAQ.md` post-MVP.
