# ADR-0004: Plugin Architecture Design

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect, Development Team  
**Technical Story**: Repository Server Requirements - Section 3.4

---

## Context

The OAI-PMH Repository Server must support extensibility for:

- **Metadata Formats**: Custom formats beyond oai_dc (DataCite, MODS, MARC21, etc.)
- **Authentication Providers**: HTTP Basic, API keys, OAuth2, SAML, LDAP
- **Storage Adapters**: Different database platforms, NoSQL, APIs
- **Event Listeners**: Custom pre/post-processing hooks

Extensibility requirements:
- Plugins installable via Composer packages
- Zero-code configuration for simple plugins
- Well-defined interfaces and contracts
- Isolation (plugin bugs don't crash server)
- Discovery (auto-registration)

### Forces at Play

- **Simplicity vs Flexibility**: Easy plugins for common cases, powerful for advanced cases
- **Security**: Malicious plugins could compromise server
- **Performance**: Plugin overhead must be minimal
- **Compatibility**: Plugins must work across server versions

---

## Decision

Implement a **multi-interface plugin system** with PSR-11 Container-based discovery and PSR-14 Event-driven extensibility.

### Plugin Types & Interfaces

#### 1. Metadata Format Plugins

**Interface**: `OaiPmh\Plugin\MetadataFormat\MetadataFormatInterface`

```php
namespace OaiPmh\Plugin\MetadataFormat;

interface MetadataFormatInterface
{
    /**
     * Metadata prefix (e.g., "oai_dc", "datacite").
     */
    public function getPrefix(): MetadataPrefix;
    
    /**
     * XML namespace URI.
     */
    public function getNamespace(): MetadataNamespace;
    
    /**
     * XML schema URL.
     */
    public function getSchema(): AnyUri;
    
    /**
     * Check if this format supports a given record.
     */
    public function supports(Record $record): bool;
    
    /**
     * Serialize record metadata to XML string.
     */
    public function serialize(Record $record): string;
}
```

**Example Plugin**:
```php
// vendor/acme/oai-datacite/src/DataCiteFormat.php
namespace Acme\OaiDataCite;

final class DataCiteFormat implements MetadataFormatInterface
{
    public function getPrefix(): MetadataPrefix
    {
        return new MetadataPrefix('datacite');
    }
    
    public function getNamespace(): MetadataNamespace
    {
        return new MetadataNamespace('http://datacite.org/schema/kernel-4');
    }
    
    public function getSchema(): AnyUri
    {
        return new AnyUri('http://schema.datacite.org/meta/kernel-4/metadata.xsd');
    }
    
    public function supports(Record $record): bool
    {
        return $record->hasMetadata('datacite.identifier');
    }
    
    public function serialize(Record $record): string
    {
        // Build DataCite XML
        return $this->buildDataCiteXml($record);
    }
}
```

**Registration** (in config):
```yaml
metadata_formats:
  - class: Acme\OaiDataCite\DataCiteFormat
    enabled: true
```

#### 2. Authentication Provider Plugins

**Interface**: `OaiPmh\Plugin\Authentication\AuthenticationProviderInterface`

```php
namespace OaiPmh\Plugin\Authentication;

interface AuthenticationProviderInterface
{
    /**
     * Authenticate request, return authenticated user or null.
     */
    public function authenticate(ServerRequestInterface $request): ?AuthenticatedUser;
    
    /**
     * Provider name/type.
     */
    public function getName(): string;
}
```

#### 3. Repository Adapter Plugins

**Interface**: `OaiPmh\Domain\Repository\RepositoryInterface` (same as core interface)

Allows implementing custom data sources (NoSQL, REST API, etc.)

#### 4. Cache Backend Plugins

**Interface**: PSR-6 `CacheItemPoolInterface` or PSR-16 `CacheInterface`

Standard PSR interfaces, no custom interface needed.

#### 5. Event Listeners

**Mechanism**: PSR-14 Event Dispatcher

**Example**:
```php
namespace Acme\OaiPlugin;

final class RecordEnrichmentListener
{
    public function __invoke(RecordRetrievedEvent $event): void
    {
        $record = $event->getRecord();
        // Modify or enrich record
        $enrichedMetadata = $this->enrichMetadata($record->getMetadata());
        $event->setRecord($record->withMetadata($enrichedMetadata));
    }
}
```

**Registration** (in config):
```yaml
event_listeners:
  - event: OaiPmh\Event\RecordRetrievedEvent
    listener: Acme\OaiPlugin\RecordEnrichmentListener
    priority: 10
```

### Plugin Discovery & Registration

**1. Composer-Based Discovery**:

```json
{
  "name": "acme/oai-datacite",
  "type": "oai-pmh-plugin",
  "extra": {
    "oai-pmh": {
      "plugin-type": "metadata-format",
      "plugin-class": "Acme\\OaiDataCite\\DataCiteFormat"
    }
  }
}
```

**2. Configuration-Based Registration** (alternative):

```yaml
plugins:
  metadata_formats:
    - Acme\OaiDataCite\DataCiteFormat
    - Vendor\OaiMods\ModsFormat
  
  authentication:
    - App\Auth\LdapAuthProvider
```

**3. Auto-Discovery Service**:

```php
// src/Infrastructure/Plugin/PluginDiscovery.php
final class PluginDiscovery
{
    public function discoverMetadataFormats(): array
    {
        // Scan installed Composer packages
        // Find packages with "oai-pmh" extra config
        // Instantiate and return plugin instances
    }
}
```

### Plugin Isolation & Security

**Validation**:
```php
interface PluginValidatorInterface
{
    /**
     * Validate plugin implements required interface correctly.
     */
    public function validate(object $plugin): ValidationResult;
}
```

**Sandboxing** (future enhancement):
- Plugins run with limited permissions
- Resource limits (memory, time)
- No direct file system access
- Monitored for exceptions

---

## Alternatives Considered

### Alternative 1: Scripting Language Plugins (e.g., Lua, JavaScript)

**Why Rejected**: Adds complexity, performance overhead, security risks. PHP plugins are native, faster, and easier to debug.

### Alternative 2: Annotation-Based Discovery

**Why Rejected**: Requires parsing all files at runtime. Configuration-based discovery is more explicit and performant.

### Alternative 3: Monolithic Server (No Plugins)

**Why Rejected**: Cannot support diverse metadata formats and custom requirements. Extensibility is core requirement.

---

## Consequences

### Positive Consequences

- **Extensibility**: Organizations can add custom metadata formats without forking
- **Community Growth**: Third-party plugins expand ecosystem
- **Clean Contracts**: PSR interfaces ensure compatibility
- **Testability**: Plugins can be tested independently

### Negative Consequences

- **Complexity**: Plugin system adds infrastructure code
- **Security Risk**: Malicious plugins could compromise server (mitigated by validation and reviews)

---

## Validation

- [x] MetadataFormatInterface defined with clear contract
- [x] Example plugin (DataCite) implements interface
- [x] Plugin registration via configuration works
- [x] Plugin validation prevents invalid plugins
- [x] Event listeners can modify behavior via PSR-14

---

## References

- [PSR-14 Event Dispatcher](https://www.php-fig.org/psr/psr-14/)
- [Composer Plugins](https://getcomposer.org/doc/articles/plugins.md)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
