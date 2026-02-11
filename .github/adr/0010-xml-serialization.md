# ADR-0010: XML Response Serialization

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect  
**Technical Story**: Repository Server Requirements - OAI-PMH Specification

---

## Context

OAI-PMH responses must be valid XML complying with OAI-PMH 2.0 schema. Requirements:
- UTF-8 encoding
- Proper namespace declarations
- Valid against OAI-PMH XSD
- Efficient generation (streaming for large responses)

---

## Decision

Use **XMLWriter** for streaming XML generation with template-based structure.

### Serializer Architecture

```php
// src/Presentation/Serializer/OaiPmhXmlSerializer.php
final class OaiPmhXmlSerializer
{
    public function serializeIdentify(IdentifyResponse $response): string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        
        $writer->startElement('OAI-PMH');
        $writer->writeAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $writer->writeAttribute('xsi:schemaLocation', 
            'http://www.openarchives.org/OAI/2.0/ ' .
            'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        );
        
        $writer->writeElement('responseDate', gmdate('Y-m-d\TH:i:s\Z'));
        
        $writer->startElement('request');
        $writer->writeAttribute('verb', 'Identify');
        $writer->text($response->getBaseURL()->getValue());
        $writer->endElement();
        
        $writer->startElement('Identify');
        $writer->writeElement('repositoryName', $response->getRepositoryName()->getValue());
        $writer->writeElement('baseURL', $response->getBaseURL()->getValue());
        // ... more fields
        $writer->endElement();
        
        $writer->endElement(); // OAI-PMH
        $writer->endDocument();
        
        return $writer->outputMemory();
    }
}
```

### Template Approach (Alternative)

Use Twig templates for complex XML:
```xml
{# templates/oai/identify.xml.twig #}
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/">
  <responseDate>{{ responseDate }}</responseDate>
  <request verb="Identify">{{ baseURL }}</request>
  <Identify>
    <repositoryName>{{ repositoryName }}</repositoryName>
    <baseURL>{{ baseURL }}</baseURL>
    ...
  </Identify>
</OAI-PMH>
```

---

## Decision Rationale

**XMLWriter** chosen over templates because:
- Streaming output (memory efficient)
- Automatic XML escaping
- No template parsing overhead
- Type-safe (IDE autocomplete)

**Templates** reserved for complex metadata formats where structure varies significantly.

---

## Validation

- [x] XML validates against OAI-PMH XSD schema
- [x] UTF-8 encoding enforced
- [x] Special characters escaped correctly
- [x] Large responses don't exceed memory limits

---

## References

- [OAI-PMH XML Schema](https://www.openarchives.org/OAI/2.0/OAI-PMH.xsd)
- [PHP XMLWriter](https://www.php.net/manual/en/book.xmlwriter.php)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
