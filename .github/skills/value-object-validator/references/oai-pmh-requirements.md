# OAI-PMH 2.0 Specification Requirements

Reference guide for OAI-PMH value object compliance.

## Specification Document

**OAI-PMH 2.0**: https://www.openarchives.org/OAI/openarchivesprotocol.html

## Common OAI-PMH Value Objects

### 1. BaseURL

**Specification:** Section 4.2 (Identify)

**Description:** Base URL of the repository

**Required:** Yes (in Identify response)

**Format:** Must be HTTP or HTTPS URL

**Validation:**
- Cannot be empty
- Must be valid URL format
- Must use HTTP or HTTPS protocol
- Should be reachable endpoint

**Example:**
```xml
<baseURL>http://memory.loc.gov/cgi-bin/oai</baseURL>
```

### 2. RepositoryName

**Specification:** Section 4.2 (Identify)

**Description:** Human-readable name of the repository

**Required:** Yes (in Identify response)

**Format:** Free text, non-empty string

**Validation:**
- Cannot be empty
- Should be meaningful description

**Example:**
```xml
<repositoryName>Library of Congress OAI Repository</repositoryName>
```

### 3. ProtocolVersion

**Specification:** Section 4.2 (Identify)

**Description:** OAI-PMH protocol version

**Required:** Yes (in Identify response)

**Format:** Must be "2.0"

**Validation:**
- Must equal "2.0" exactly
- No other values allowed

**Example:**
```xml
<protocolVersion>2.0</protocolVersion>
```

### 4. AdminEmail

**Specification:** Section 4.2 (Identify)

**Description:** Email address of repository administrator

**Required:** Yes, at least one (in Identify response)

**Format:** Valid email address

**Validation:**
- Must be valid email format
- Cannot be empty

**Example:**
```xml
<adminEmail>admin@repository.org</adminEmail>
```

### 5. EarliestDatestamp

**Specification:** Section 4.2 (Identify)

**Description:** Earliest datestamp in repository

**Required:** Yes (in Identify response)

**Format:** UTC datetime in ISO8601 format

**Validation:**
- Must match granularity
- YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ

**Example:**
```xml
<earliestDatestamp>1998-01-15</earliestDatestamp>
<earliestDatestamp>2001-01-01T00:00:00Z</earliestDatestamp>
```

### 6. DeletedRecord

**Specification:** Section 3.5 (Deleted Records)

**Description:** How repository handles deleted records

**Required:** Yes (in Identify response)

**Allowed Values:**
- `no` - Repository does not maintain deleted record information
- `transient` - Repository maintains info but not persistently/completely
- `persistent` - Repository maintains complete deletion info with no time limit

**Validation:**
- Must be one of: "no", "transient", "persistent"
- Case-sensitive

**Example:**
```xml
<deletedRecord>persistent</deletedRecord>
```

### 7. Granularity

**Specification:** Section 3.3 (Datestamp)

**Description:** Finest granularity for datestamps

**Required:** Yes (in Identify response)

**Allowed Values:**
- `YYYY-MM-DD` - Day granularity
- `YYYY-MM-DDThh:mm:ssZ` - Seconds granularity

**Validation:**
- Must be one of the two formats exactly
- Case-sensitive (T and Z uppercase)

**Example:**
```xml
<granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
```

### 8. Compression

**Specification:** Section 4.2 (Identify)

**Description:** Supported compression encodings

**Required:** No (optional in Identify response)

**Allowed Values:**
- `gzip`
- `compress`
- `deflate`
- `identity`

**Validation:**
- If present, must be supported encoding

**Example:**
```xml
<compression>gzip</compression>
<compression>deflate</compression>
```

### 9. Description

**Specification:** Section 4.2 (Identify)

**Description:** XML-encoded data about repository

**Required:** No (optional, can have multiple)

**Format:** Well-formed XML with namespace

**Validation:**
- Must be valid XML
- Should have proper namespace

**Example:**
```xml
<description>
  <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier">
    <scheme>oai</scheme>
    <repositoryIdentifier>memory.loc.gov</repositoryIdentifier>
  </oai-identifier>
</description>
```

### 10. MetadataPrefix

**Specification:** Section 4.5 (ListMetadataFormats)

**Description:** Unique prefix identifying metadata format

**Required:** Yes (in ListMetadataFormats response)

**Format:** String conforming to URI syntax

**Validation:**
- Cannot be empty
- Should be valid prefix (e.g., "oai_dc", "marc21")

**Example:**
```xml
<metadataPrefix>oai_dc</metadataPrefix>
```

### 11. MetadataNamespace

**Specification:** Section 4.5 (ListMetadataFormats)

**Description:** XML namespace URI for metadata format

**Required:** Yes (in ListMetadataFormats response)

**Format:** Valid URI

**Validation:**
- Must be valid URI
- Cannot be empty

**Example:**
```xml
<metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
```

### 12. Schema

**Specification:** Section 4.5 (ListMetadataFormats)

**Description:** URL of XML schema for metadata format

**Required:** Yes (in ListMetadataFormats response)

**Format:** Valid URL to XSD file

**Validation:**
- Must be valid URL
- Should point to accessible XSD

**Example:**
```xml
<schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
```

### 13. UTCdatetime

**Specification:** Section 3.3 (Datestamp)

**Description:** Date and time in UTC

**Required:** Used throughout protocol

**Format:** ISO8601 format

**Allowed Formats:**
- `YYYY-MM-DD` (day granularity)
- `YYYY-MM-DDThh:mm:ssZ` (seconds granularity)

**Validation:**
- Must match repository granularity
- Year: 0001-9999
- Month: 01-12
- Day: 01-31 (valid for month)
- Hour: 00-23
- Minute: 00-59
- Second: 00-59
- Trailing Z required for full datetime

**Examples:**
```xml
<datestamp>2002-02-08</datestamp>
<datestamp>2002-02-08T14:30:45Z</datestamp>
```

### 14. RecordIdentifier

**Specification:** Section 2.4 (Unique Identifier)

**Description:** Unique identifier for item

**Required:** Yes (in record headers)

**Format:** URI conforming to URI syntax

**Validation:**
- Cannot be empty
- Should be unique within repository
- Must be valid URI format

**Example:**
```xml
<identifier>oai:arXiv.org:cs/0112017</identifier>
```

### 15. SetSpec

**Specification:** Section 2.6 (Set)

**Description:** Unique identifier for a set

**Required:** No (only if repository supports sets)

**Format:** String from [A-Z], [a-z], [0-9], [-], [_], [.], [!], [~], [*], ['], [(], [)]

**Validation:**
- Can contain hierarchical structure with colons
- Cannot be empty if used

**Example:**
```xml
<setSpec>math</setSpec>
<setSpec>math:algebra</setSpec>
```

## Documentation Requirements

For each OAI-PMH value object, the class docblock must include:

### 1. Specification Reference

Cite the exact section:
```php
/**
 * According to OAI-PMH 2.0 specification section 4.2 (Identify)...
 */
```

### 2. Protocol Context

Explain where this appears in OAI-PMH:
```php
/**
 * This value is required in the Identify response and represents...
 */
```

### 3. Allowed Values (if enumeration)

List all valid values:
```php
/**
 * Allowed values:
 * - 'no': repository does not maintain deletion information
 * - 'transient': repository maintains info but not persistently
 * - 'persistent': repository maintains complete deletion info
 */
```

### 4. Format Requirements

Document expected format:
```php
/**
 * Format: YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ
 */
```

### 5. Validation Rules

Explain what makes the value valid:
```php
/**
 * Validation ensures:
 * - Value is not empty
 * - Value uses HTTP or HTTPS protocol
 * - Value is a valid URL format
 */
```

## XML Namespace Requirements

When representing metadata formats or descriptions:

**Standard OAI-PMH namespace:**
```
http://www.openarchives.org/OAI/2.0/
```

**OAI-DC namespace:**
```
http://www.openarchives.org/OAI/2.0/oai_dc/
```

**Schema locations:**
```
http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd
http://www.openarchives.org/OAI/2.0/oai_dc.xsd
```

## Error Handling

OAI-PMH defines specific error codes. Value objects should validate to prevent these errors:

- `badArgument` - Invalid argument value
- `badResumptionToken` - Invalid or expired resumption token
- `badVerb` - Illegal OAI verb
- `idDoesNotExist` - Identifier does not exist
- `noMetadataFormats` - No metadata formats available
- `noRecordsMatch` - No records match criteria
- `noSetHierarchy` - Repository does not support sets

## Common Validation Patterns

### URL Validation
```php
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    throw new InvalidArgumentException('Invalid URL format');
}
if (!preg_match('/^https?:\/\//', $url)) {
    throw new InvalidArgumentException('URL must use HTTP or HTTPS');
}
```

### Email Validation
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new InvalidArgumentException('Invalid email format');
}
```

### DateTime Validation
```php
// Day granularity
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    throw new InvalidArgumentException('Invalid date format');
}

// Full datetime
if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $datetime)) {
    throw new InvalidArgumentException('Invalid datetime format');
}
```

### Enumeration Validation
```php
$allowedValues = ['no', 'transient', 'persistent'];
if (!in_array($value, $allowedValues, true)) {
    throw new InvalidArgumentException(
        sprintf('Invalid value. Must be one of: %s', implode(', ', $allowedValues))
    );
}
```

## References

- OAI-PMH 2.0 Specification: https://www.openarchives.org/OAI/openarchivesprotocol.html
- Dublin Core Metadata: http://dublincore.org/
- ISO 8601 DateTime: https://www.iso.org/iso-8601-date-and-time-format.html
- RFC 3986 URI: https://tools.ietf.org/html/rfc3986

---

## Grep Patterns for Finding Requirements

**Find specific value objects:**
- All value object sections: `/^### \d+\. /`
- BaseURL requirements: `/^### 1\. BaseURL/`
- Email requirements: `/^### 8\. Email/`
- DateTime requirements: `/^### 11\. UTCdatetime/`

**Find specific information:**
- Required fields: `/\*\*Required:\*\* Yes/`
- Optional fields: `/\*\*Required:\*\* No/`
- Validation rules: `/\*\*Validation:\*\*/`
- XML examples: `/```xml/`
- Enumeration values: `/\*\*Allowed Values:\*\*/`
- Format specifications: `/\*\*Format:\*\*/`

**Find patterns:**
- All specifications: `/\*\*Specification:\*\*/`
- All examples: `/\*\*Example:\*\*/`
- Common patterns: `/\#\# Common Validation Patterns/`
