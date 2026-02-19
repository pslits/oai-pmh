# Feature F-019: Custom Metadata Format Support

**Feature ID:** F-019  
**Priority:** Post-MVP (NICE TO HAVE)  
**Phase:** 4 — Metadata Serialization Context (extended)  
**Bounded Context:** Metadata Serialization  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Enable repository administrators and third-party developers to create, distribute, and register custom metadata format plugins (e.g., DataCite, MODS, MARC21) beyond the built-in Dublin Core. Includes documentation, example plugin scaffolding, and validation of custom format XML output against provided schemas.

## User Stories

**US-019.1:** As a **plugin developer**, I want a documented example of creating a custom metadata format plugin so that I can add my organization's preferred format.

**US-019.2:** As a **repository administrator**, I want to install third-party format plugins via Composer so that I can expose records in DataCite, MODS, or other specialized formats.

## Acceptance Criteria

- [ ] Example custom format plugin in documentation (complete, runnable code)
- [ ] Plugin loading via Composer autoload (PSR-4 class discovery)
- [ ] Custom format validation (XML output validated against plugin's declared schema)
- [ ] Plugin error handling: invalid plugin class → startup error with clear message
- [ ] Record-specific format support (some records only available in certain formats)
- [ ] Documentation covers:
  - Plugin interface reference
  - Step-by-step plugin creation tutorial
  - Composer package setup for distribution
  - Configuration for registering custom plugins
- [ ] PHPStan Level 8 passes on example plugin

## Dependencies

### Blocked By
- **F-017** Metadata Format Plugin System (plugin infrastructure)
- **F-018** Dublin Core Plugin (reference implementation to follow)

### Blocks
- None (standalone extension point)

## Files

```
docs/WRITING_PLUGINS.md
src/MetadataSerialization/Plugin/ExampleCustomPlugin.php  (example/reference)
tests/MetadataSerialization/Plugin/ExampleCustomPluginTest.php
```

## Testing Requirements

- [ ] Example plugin passes all interface tests
- [ ] Plugin registration from configuration
- [ ] Error for invalid plugin class
- [ ] Record-specific format support (supports() returns false for incompatible records)

## Notes

- This feature primarily adds documentation and validation — the plugin infrastructure itself is provided by F-017.
- DataCite plugin is a common request for research data repositories.
