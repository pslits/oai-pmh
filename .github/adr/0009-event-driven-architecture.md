# ADR-0009: Event-Driven Extension Points

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect  
**Technical Story**: Repository Server Requirements - Section 3.4.2

---

## Context

Plugins need hooks to modify behavior without changing core code. Use cases:
- Enrich metadata before serialization
- Log specific events
- Trigger external systems when records accessed
- Custom validation

---

## Decision

Use **PSR-14 Event Dispatcher** with predefined event types.

### Event Types

```php
// Record Events
class RecordRetrievedEvent
{
    public function __construct(private Record $record) {}
    
    public function getRecord(): Record
    {
        return $this->record;
    }
    
    public function setRecord(Record $record): void
    {
        $this->record = $record;
    }
}

// Request Events
class OaiRequestReceivedEvent
{
    public function __construct(
        private OaiVerb $verb,
        private array $parameters
    ) {}
}

class OaiResponseGeneratedEvent
{
    public function __construct(
        private string $xmlResponse
    ) {}
}
```

### Event Registration

```yaml
# config/config.yaml
event_listeners:
  - event: OaiPmh\Event\RecordRetrievedEvent
    listener: App\Listener\RecordLogger
    priority: 10
    
  - event: OaiPmh\Event\RecordRetrievedEvent
    listener: App\Listener\MetadataEnricher
    priority: 5
```

---

## Validation

- [x] Events dispatched at correct times
- [x] Listeners can modify data
- [x] Priority ordering works correctly

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
