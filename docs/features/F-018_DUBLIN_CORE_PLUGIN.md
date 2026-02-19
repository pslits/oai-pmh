# Feature F-018: Dublin Core (oai_dc) Plugin

**Feature ID:** F-018  
**Priority:** MVP (MUST HAVE)  
**Phase:** 4 — Metadata Serialization Context  
**Bounded Context:** Metadata Serialization  
**Status:** Draft  
**Date:** February 18, 2026  

---

## Description

Implement the built-in Dublin Core metadata format plugin (`oai_dc`) that ships as the default format with the server. Dublin Core is the most widely used metadata format in OAI-PMH and is recommended by the specification as the minimum interoperable format. The plugin maps the 15 Dublin Core elements from configured database columns to the `oai_dc` XML schema.

## User Stories

**US-018.1:** As a **harvester**, I want every OAI-PMH repository to support at least Dublin Core (`oai_dc`) so that I can harvest metadata in a standard, interoperable format.

**US-018.2:** As a **repository administrator**, I want to configure which database columns map to Dublin Core elements so that my metadata is correctly represented.

## Acceptance Criteria

- [ ] Implements `MetadataFormatPluginInterface`
- [ ] `metadataPrefix`: `oai_dc`
- [ ] `metadataNamespace`: `http://www.openarchives.org/OAI/2.0/oai_dc/`
- [ ] `schema`: `http://www.openarchives.org/OAI/2.0/oai_dc.xsd`
- [ ] Maps all 15 Dublin Core elements when database columns are configured:
  - title, creator, subject, description, publisher, contributor, date, type, format, identifier, source, language, relation, coverage, rights
- [ ] Handles missing optional fields gracefully (omit element, don't output empty tags)
- [ ] Handles multi-valued fields (multiple `<dc:title>` elements)
- [ ] Output XML is well-formed with correct namespace declarations (`dc:` and `oai_dc:`)
- [ ] Output validates against `oai_dc.xsd`
- [ ] UTF-8 encoding enforced
- [ ] PHPStan Level 8 passes

## Technical Design

### Dublin Core XML Output Example
```xml
<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
           xmlns:dc="http://purl.org/dc/elements/1.1/"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
           http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
  <dc:title>Example Dataset Title</dc:title>
  <dc:creator>John Doe</dc:creator>
  <dc:subject>Climate Science</dc:subject>
  <dc:description>A dataset about climate patterns.</dc:description>
  <dc:date>2025-06-15</dc:date>
  <dc:type>Dataset</dc:type>
  <dc:identifier>oai:example.org:12345</dc:identifier>
</oai_dc:dc>
```

### Column Mapping
The plugin reads field-to-column mapping from the `metadata_formats.oai_dc.fields` configuration section.

## Dependencies

### Blocked By
- **F-017** Metadata Format Plugin System (implements the interface)
- **F-006** Database Schema Mapping (per-format field mapping)

### Blocks
- **F-015** ListRecords Verb Handler (default format for serialization)
- **F-016** GetRecord Verb Handler (default format)
- **F-037** Integration Testing (first format to test)

## Files

```
src/MetadataSerialization/Plugin/DublinCorePlugin.php
tests/MetadataSerialization/Plugin/DublinCorePluginTest.php
```

## Testing Requirements

- [ ] Serialization of record with all 15 DC elements
- [ ] Serialization with missing optional fields (no empty tags)
- [ ] Multi-valued fields (multiple creator, subject)
- [ ] XML output well-formed
- [ ] XML validates against oai_dc.xsd
- [ ] Namespace declarations correct
- [ ] supports() returns true for standard records
- [ ] getPrefix(), getNamespace(), getSchema() return correct values

## Notes

- Dublin Core is defined by the Dublin Core Metadata Initiative (DCMI).
- The 15 elements are the "Dublin Core Metadata Element Set" (ISO 15836).
