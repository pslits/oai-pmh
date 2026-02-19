# Feature F-017: Metadata Format Plugin System

**Feature ID:** F-017  
**Priority:** MVP (MUST HAVE)  
**Phase:** 4 — Metadata Serialization Context  
**Bounded Context:** Metadata Serialization  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the plugin architecture for metadata format handling, including the `MetadataFormatPluginInterface`, `MetadataFormatRegistry` aggregate, and `MetadataSerializer` service. The system allows registering, discovering, and invoking metadata format plugins that transform database records into XML metadata fragments.

## User Stories

**US-017.1:** As a **repository administrator**, I want to enable/disable metadata formats via configuration so that I only expose formats relevant to my repository.

**US-017.2:** As a **plugin developer**, I want a clear interface to implement so that I can create custom metadata format plugins.

**US-017.3:** As the **server**, I want a registry of format plugins so that I can dispatch serialization requests to the correct plugin.

## Acceptance Criteria

- [ ] `MetadataFormatPluginInterface` defines contract:
  - `getPrefix(): MetadataPrefix`
  - `getNamespace(): MetadataNamespace`
  - `getSchema(): AnyUri`
  - `supports(RecordInterface $record): bool`
  - `serialize(RecordInterface $record): string` (XML fragment)
- [ ] `MetadataFormatRegistry` holds all registered format plugins
  - Indexed by `MetadataPrefix` for O(1) lookup
  - Validates plugin interface at registration
  - `getPlugin(MetadataPrefix): MetadataFormatPluginInterface`
  - `listFormats(?RecordInterface): MetadataFormatPluginInterface[]`
- [ ] `MetadataSerializer` orchestrates serialization:
  - Fetches plugin → calls serialize() → returns XML with namespace declarations
- [ ] Throws `CannotDisseminateFormatException` for unknown/unsupported prefixes
- [ ] Formats loaded from `metadata_formats` configuration section
- [ ] Plugin loading via Composer autoload (PSR-4)
- [ ] At least one format required (validation at startup)
- [ ] Serialized output is well-formed XML with proper namespace declarations
- [ ] UTF-8 encoding enforced
- [ ] PHPStan Level 8 passes

## Technical Design

### Plugin Registration Flow
1. Configuration lists enabled formats with prefix, plugin class, and enabled flag
2. At startup, `MetadataFormatRegistry` instantiates each enabled plugin class
3. Validates each plugin implements `MetadataFormatPluginInterface`
4. Indexes by `MetadataPrefix` for fast lookup

### Configuration Example
```yaml
metadata_formats:
  - prefix: oai_dc
    namespace: http://www.openarchives.org/OAI/2.0/oai_dc/
    schema: http://www.openarchives.org/OAI/2.0/oai_dc.xsd
    plugin: OaiPmh\MetadataSerialization\Plugin\DublinCorePlugin
    enabled: true
```

## Dependencies

### Blocked By
- **F-001** Project Foundation & Shared Contracts (MetadataFormatPluginInterface)
- **F-002** Configuration Management (reads `metadata_formats` section)
- **F-004** Record & RecordHeader Entities (RecordInterface for serialization)

### Blocks
- **F-012** ListMetadataFormats Verb Handler (lists from registry)
- **F-014** ListIdentifiers Verb Handler (validates metadataPrefix)
- **F-015** ListRecords Verb Handler (serializes records)
- **F-016** GetRecord Verb Handler (serializes single record)
- **F-018** Dublin Core Plugin (implements the interface)
- **F-019** Custom Metadata Format Support

## Files

```
src/MetadataSerialization/Contract/MetadataFormatPluginInterface.php
src/MetadataSerialization/Aggregate/MetadataFormatRegistry.php
src/MetadataSerialization/Service/MetadataSerializer.php
src/MetadataSerialization/Exception/CannotDisseminateFormatException.php
tests/MetadataSerialization/Aggregate/MetadataFormatRegistryTest.php
tests/MetadataSerialization/Service/MetadataSerializerTest.php
```

## Testing Requirements

- [ ] Register valid plugin
- [ ] Register multiple plugins
- [ ] Lookup plugin by prefix
- [ ] List all formats (no record filter)
- [ ] List formats for specific record
- [ ] CannotDisseminateFormatException for unknown prefix
- [ ] Plugin interface validation at registration
- [ ] Serialization produces well-formed XML
- [ ] Namespace declarations in output

## Notes

- OAI-PMH 2.0 recommends (but does not require) `oai_dc` as a minimum format.
- Third-party format plugins are distributed as Composer packages.
