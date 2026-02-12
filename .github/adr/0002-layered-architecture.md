# ADR-0002: Layered Architecture Pattern

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect, Development Team  
**Technical Story**: Repository Server Requirements - Section 4.2.1

---

## Context

The OAI-PMH Repository Server must build upon the existing value objects library (`pslits/oai-pmh`) while implementing complex functionality including database access, HTTP request handling, caching, authentication, and plugin management. The architecture must:

- **Maintain Domain Integrity**: Preserve the Domain-Driven Design principles established in the value objects library
- **Enable Testing**: All layers must be independently testable
- **Support Extensibility**: Plugin architecture for metadata formats, authentication, and storage adapters
- **Ensure Maintainability**: Clear separation of concerns, predictable code organization
- **Facilitate Scalability**: Stateless design enabling horizontal scaling

### Forces at Play

- **Existing Domain Layer**: Value objects library provides immutable, validated domain primitives
- **Multiple Concerns**: HTTP, database, caching, authentication, XML serialization, business logic
- **Plugin Architecture**: Need clear extension points without modifying core code
- **Testing Complexity**: End-to-end functionality spans HTTP → Business Logic → Database
- **Team Understanding**: Architecture must be understandable to PHP developers familiar with DDD

---

## Decision

We will implement a **4-Layer Architecture** with clear separation between Domain, Application, Infrastructure, and Presentation layers, following Domain-Driven Design principles.

### Architectural Layers

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│  (HTTP Controllers, XML Serializers, Error Formatters)      │
│  - Handles HTTP requests/responses                          │
│  - Delegates to Application Layer                           │
│  - Serializes responses to OAI-PMH XML                      │
└──────────────────────┬──────────────────────────────────────┘
                       │ Uses
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                         │
│  (Use Cases, Request Handlers, DTOs, Services)              │
│  - OAI-PMH verb handlers (GetRecordHandler, etc.)           │
│  - Orchestrates domain objects and infrastructure           │
│  - No HTTP or database implementation details               │
└──────────────────────┬──────────────────────────────────────┘
                       │ Uses                    Uses
                       ▼                          ▼
┌────────────────────────────────┐    ┌────────────────────────┐
│      DOMAIN LAYER              │    │  INFRASTRUCTURE LAYER  │
│  (Value Objects, Entities,     │    │  (Repositories,        │
│   Domain Services)             │    │   Cache, Events,       │
│  - BaseURL, Email, etc.        │    │   Config, Logging)     │
│  - Business rules & validation │    │  - Database access     │
│  - Framework-independent       │    │  - External services   │
└────────────────────────────────┘    └────────────────────────┘
```

### Layer Responsibilities

#### 1. Presentation Layer

**Location**: `src/Presentation/`

**Responsibilities**:
- Handle HTTP requests (routing, parameter extraction)
- Invoke appropriate Application Layer handlers
- Serialize responses to OAI-PMH XML format
- Format error responses with proper HTTP status codes
- No business logic

**Components**:
- `Http/Controller/OaiPmhController.php`: Main entry point, routes to verb handlers
- `Serializer/OaiPmhXmlSerializer.php`: Converts DTOs to OAI-PMH XML
- `Serializer/ErrorSerializer.php`: Formats OAI-PMH error responses
- `Middleware/RateLimitingMiddleware.php`: Rate limiting
- `Middleware/AuthenticationMiddleware.php`: Authentication
- `Middleware/LoggingMiddleware.php`: Request/response logging

**Example**:
```php
// src/Presentation/Http/Controller/OaiPmhController.php
final class OaiPmhController
{
    public function __construct(
        private RequestHandlerRegistry $handlerRegistry,
        private OaiPmhXmlSerializer $serializer,
        private ErrorSerializer $errorSerializer
    ) {}
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $verb = $request->getQueryParams()['verb'] ?? null;
            $handler = $this->handlerRegistry->getHandler($verb);
            $result = $handler->handle($request);
            return $this->serializer->serialize($result);
        } catch (OaiPmhException $e) {
            return $this->errorSerializer->serializeError($e);
        }
    }
}
```

#### 2. Application Layer

**Location**: `src/Application/`

**Responsibilities**:
- Implement OAI-PMH protocol logic (the "use cases")
- Orchestrate Domain objects and Infrastructure services
- No HTTP details, no database queries, no XML generation
- Input: Simple DTOs or value objects
- Output: DTOs containing response data

**Components**:
- `Handler/IdentifyHandler.php`: Handles Identify verb
- `Handler/GetRecordHandler.php`: Handles GetRecord verb
- `Handler/ListRecordsHandler.php`: Handles ListRecords verb
- `Handler/ListIdentifiersHandler.php`: Handles ListIdentifiers verb
- `Handler/ListSetsHandler.php`: Handles ListSets verb
- `Handler/ListMetadataFormatsHandler.php`: Handles ListMetadataFormats verb
- `Service/ResumptionTokenService.php`: Manages resumption tokens
- `Service/MetadataFormatRegistry.php`: Registers and retrieves metadata format plugins
- `DTO/IdentifyResponse.php`: Transfer object for Identify response
- `DTO/RecordResponse.php`: Transfer object for record data

**Example**:
```php
// src/Application/Handler/GetRecordHandler.php
final class GetRecordHandler implements RequestHandlerInterface
{
    public function __construct(
        private RepositoryInterface $repository,
        private MetadataFormatRegistry $formatRegistry,
        private EventDispatcherInterface $eventDispatcher
    ) {}
    
    public function handle(ServerRequestInterface $request): RecordResponse
    {
        // Extract parameters using Value Objects
        $identifier = new RecordIdentifier($params['identifier']);
        $metadataPrefix = new MetadataPrefix($params['metadataPrefix']);
        
        // Fetch record from repository (Infrastructure)
        $record = $this->repository->getRecord($identifier);
        
        if ($record === null) {
            throw new IdDoesNotExistException($identifier);
        }
        
        // Get metadata format plugin
        $format = $this->formatRegistry->getFormat($metadataPrefix);
        
        if (!$format->supports($record)) {
            throw new CannotDisseminateFormatException($metadataPrefix, $identifier);
        }
        
        // Dispatch event for extensibility
        $this->eventDispatcher->dispatch(new RecordRetrievedEvent($record));
        
        // Return DTO (Presentation layer will serialize)
        return new RecordResponse($record, $format);
    }
}
```

#### 3. Domain Layer

**Location**: `vendor/pslits/oai-pmh/src/Domain/` (existing library) + `src/Domain/` (server-specific domain logic)

**Responsibilities**:
- Define value objects (BaseURL, Email, ProtocolVersion, etc.) - **already exists in library**
- Define entities (Record, Set, MetadataFormat) - **new in server**
- Encapsulate business rules and validation
- Completely framework-independent, no infrastructure dependencies

**Components** (new in server):
- `Entity/Record.php`: Represents an OAI-PMH record with header and metadata
- `Entity/RecordHeader.php`: Record identifier, datestamp, setSpec, deleted status
- `Entity/Set.php`: Represents an OAI-PMH set with spec, name, description
- `Repository/RepositoryInterface.php`: Abstract interface for data access
- `Service/SetHierarchyService.php`: Business logic for hierarchical sets
- `Exception/OaiPmhException.php`: Base domain exception
- `Exception/IdDoesNotExistException.php`: Record not found
- `Exception/CannotDisseminateFormatException.php`: Format not supported

**Example**:
```php
// src/Domain/Entity/Record.php
final class Record
{
    public function __construct(
        private RecordIdentifier $identifier,
        private UTCdatetime $datestamp,
        private array $setSpecs, // array of SetSpec value objects
        private bool $deleted,
        private array $metadata  // associative array of metadata fields
    ) {}
    
    public function getIdentifier(): RecordIdentifier
    {
        return $this->identifier;
    }
    
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    
    public function belongsToSet(SetSpec $setSpec): bool
    {
        foreach ($this->setSpecs as $spec) {
            if ($spec->equals($setSpec)) {
                return true;
            }
        }
        return false;
    }
}
```

#### 4. Infrastructure Layer

**Location**: `src/Infrastructure/`

**Responsibilities**:
- Implement interfaces defined in Domain and Application layers
- Database access (Repository implementations)
- Caching (Redis, APCu, filesystem)
- Configuration loading (YAML parsing)
- Logging (Monolog integration)
- Event dispatching (Symfony EventDispatcher)
- External service integrations

**Components**:
- `Repository/DoctrineRecordRepository.php`: Doctrine DBAL implementation of RepositoryInterface
- `Repository/Adapter/MySqlAdapter.php`: MySQL-specific queries
- `Repository/Adapter/PostgreSqlAdapter.php`: PostgreSQL-specific queries
- `Cache/RedisCacheAdapter.php`: Redis caching implementation (PSR-6/16)
- `Cache/CachedRepositoryDecorator.php`: Decorator adding caching to any repository
- `Config/YamlConfigLoader.php`: Loads and validates YAML configuration
- `Event/EventDispatcher.php`: PSR-14 event dispatcher
- `Logging/MonologFactory.php`: Creates configured Monolog logger
- `Authentication/AuthenticationProviderInterface.php`: Interface for auth plugins
- `Authentication/BasicAuthProvider.php`: HTTP Basic Auth implementation

**Example**:
```php
// src/Infrastructure/Repository/DoctrineRecordRepository.php
final class DoctrineRecordRepository implements RepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private DatabaseMapping $mapping
    ) {}
    
    public function getRecord(RecordIdentifier $identifier): ?Record
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
           ->from($this->mapping->getRecordTable())
           ->where($qb->expr()->eq(
               $this->mapping->getIdentifierField(),
               $qb->createNamedParameter($identifier->getValue())
           ));
        
        $row = $qb->executeQuery()->fetchAssociative();
        
        if ($row === false) {
            return null;
        }
        
        return $this->mapRowToRecord($row);
    }
    
    private function mapRowToRecord(array $row): Record
    {
        return new Record(
            identifier: new RecordIdentifier($row[$this->mapping->getIdentifierField()]),
            datestamp: new UTCdatetime($row[$this->mapping->getDatestampField()]),
            setSpecs: $this->extractSetSpecs($row),
            deleted: (bool) $row[$this->mapping->getDeletedField()],
            metadata: $this->extractMetadata($row)
        );
    }
}
```

### Inter-Layer Communication

**Dependency Direction**: All dependencies point inward toward the Domain Layer

```
Presentation → Application → Domain
                            ↗
Infrastructure ──────────────┘
```

- **Presentation depends on**: Application (handlers), Infrastructure (DI container)
- **Application depends on**: Domain (entities, VOs), Infrastructure interfaces only (via DI)
- **Domain depends on**: Nothing (pure PHP, isolated)
- **Infrastructure depends on**: Domain (implements interfaces), Application (implements interfaces)

### Dependency Injection

All dependencies are injected via constructor injection using PSR-11 Container (PHP-DI):

```php
// config/dependencies.php
return [
    // Infrastructure
    Connection::class => function (ContainerInterface $c) {
        return DriverManager::getConnection($c->get('config.database'));
    },
    
    RepositoryInterface::class => function (ContainerInterface $c) {
        $repo = new DoctrineRecordRepository(
            $c->get(Connection::class),
            $c->get(DatabaseMapping::class)
        );
        // Wrap with caching decorator if enabled
        if ($c->get('config.cache.enabled')) {
            $repo = new CachedRepositoryDecorator($repo, $c->get(CacheInterface::class));
        }
        return $repo;
    },
    
    // Application
    GetRecordHandler::class => DI\autowire()
        ->constructorParameter('repository', DI\get(RepositoryInterface::class)),
    
    // Presentation
    OaiPmhController::class => DI\autowire(),
];
```

---

## Alternatives Considered

### Alternative 1: Monolithic Architecture

**Description**: Single layer with controllers directly accessing database and generating XML.

**Pros**:
- Simpler initially (fewer files, less abstraction)
- Faster to prototype
- Less boilerplate

**Cons**:
- Tight coupling between HTTP, business logic, and database
- Very difficult to test (no isolation)
- Cannot easily swap database or cache implementations
- Plugin architecture nearly impossible
- Violates Single Responsibility Principle
- Difficult to understand for new contributors

**Why Rejected**: Technical debt accumulates rapidly. As requirements grow (plugins, multiple databases, caching, authentication), refactoring becomes prohibitively expensive. Layered architecture prevents this.

### Alternative 2: Hexagonal Architecture (Ports & Adapters)

**Description**: Core business logic (hexagon) surrounded by ports (interfaces) and adapters (implementations).

**Pros**:
- Very clean separation between core logic and external systems
- Highly testable
- Industry-recognized pattern
- Excellent for DDD

**Cons**:
- More complex terminology (ports, adapters, primary/secondary)
- Can be over-engineered for simpler applications
- Requires strong understanding of pattern

**Why Rejected**: While hexagonal architecture is excellent, the 4-layer approach is more widely understood in the PHP community and achieves the same goals with more familiar terminology. The difference is minimal for this use case.

### Alternative 3: CQRS (Command Query Responsibility Segregation)

**Description**: Separate models for read operations (queries) and write operations (commands).

**Pros**:
- Optimized read and write paths
- Scalable for complex domains
- Clear separation of concerns

**Cons**:
- Overkill for OAI-PMH (read-heavy, minimal writes)
- Adds complexity without clear benefit
- Requires more infrastructure (separate read/write models)

**Why Rejected**: OAI-PMH protocol is read-only (no write operations in the protocol). CQRS adds unnecessary complexity. If write operations are added in the future (admin API), CQRS can be reconsidered.

---

## Consequences

### Positive Consequences

- **Testability**: Each layer can be tested independently with mocks
- **Maintainability**: Clear separation makes code easy to navigate and understand
- **Extensibility**: Plugins implement infrastructure interfaces without touching core code
- **Flexibility**: Database, cache, authentication can be swapped by changing DI bindings
- **Team Productivity**: Developers can work on different layers with minimal conflicts
- **Code Quality**: Enforced separation prevents shortcuts and technical debt
- **Reusability**: Domain layer is pure PHP, can be extracted to a separate library if needed

### Negative Consequences

- **Initial Complexity**: More files and abstractions upfront
- **Learning Curve**: Contributors must understand layered architecture and DDD
- **Boilerplate**: Some code duplication (e.g., DTOs mirroring entities)
- **Performance Overhead**: Minimal overhead from additional abstraction (mitigated by caching and PHPStan ensuring type safety)

### Neutral Consequences

- **Directory Structure**: More directories required (src/Domain, src/Application, src/Infrastructure, src/Presentation)
- **Dependency Injection**: All classes use constructor injection (enforces best practices)

---

## Compliance

### Alignment with Requirements

- **Requirement 4.2.1 (Domain-Driven Design)**: ✅ Clear Domain, Application, Infrastructure layers
- **Requirement 3.4.1 (Plugin System)**: ✅ Infrastructure interfaces enable plugins
- **Requirement 3.1.2 (Scalability)**: ✅ Stateless Application layer supports horizontal scaling
- **Requirement 4.3.2 (Testing Requirements)**: ✅ Layered architecture enables unit and integration testing

### Alignment with Architectural Principles

- **Separation of Concerns**: ✅ Each layer has well-defined responsibility
- **Dependency Inversion**: ✅ High-level modules (Application) depend on abstractions (RepositoryInterface), not concretions
- **Open/Closed Principle**: ✅ Layers are open for extension (plugins) but closed for modification
- **Single Responsibility**: ✅ Each class has one reason to change
- **Interface Segregation**: ✅ Interfaces are specific to use cases

---

## Implementation Guidance

### Required Actions

1. **Create directory structure**: `src/Domain/`, `src/Application/`, `src/Infrastructure/`, `src/Presentation/`
2. **Define repository interfaces** in Domain layer
3. **Implement request handlers** in Application layer for each OAI-PMH verb
4. **Implement repository adapters** in Infrastructure layer (MySQL, PostgreSQL)
5. **Implement HTTP controller** in Presentation layer
6. **Configure dependency injection** container
7. **Write unit tests** for each layer independently

### Dependencies

- **Value Objects Library**: Domain layer uses `pslits/oai-pmh` value objects
- **PSR-11 Container**: PHP-DI for dependency injection
- **PSR-7 HTTP**: Nyholm/psr7 for request/response abstraction

### Layer Communication Guidelines

**✅ Allowed**:
- Presentation → Application (invoke handlers)
- Application → Domain (use entities, value objects)
- Application → Infrastructure interfaces (via DI)
- Infrastructure → Domain (implement interfaces, use value objects)

**❌ Forbidden**:
- Domain → Infrastructure (no database in domain)
- Domain → Application (domain is independent)
- Application → Presentation (application doesn't know about HTTP)
- Infrastructure → Presentation (infrastructure doesn't know about HTTP)

---

## Validation

### Success Criteria

- [x] Directory structure matches defined layers
- [x] Each layer has clear, documented responsibilities
- [x] Domain layer has zero dependencies on other layers
- [x] Application layer has no HTTP or database implementation code
- [x] Infrastructure implements all repository interfaces
- [x] Presentation handles HTTP only, delegates to Application
- [x] PHPStan verifies dependency directions
- [x] Unit tests for each layer run in isolation
- [x] Integration tests verify layers work together

### Testing Strategy

- **Unit Tests (Layer Isolation)**:
  - Domain: Test entities and value objects with pure PHP
  - Application: Test handlers with mocked repositories
  - Infrastructure: Test repositories with test database
  - Presentation: Test controllers with mocked handlers

- **Integration Tests (Inter-Layer)**:
  - Full request → response flow with real dependencies
  - Verify DI container correctly wires all layers

- **Architecture Tests (phparkitect or deptrac)**:
  - Enforce layer dependencies (prevent violations)
  - Verify Domain has no external dependencies
  - Verify Application doesn't use HTTP or database directly

---

## References

- [Domain-Driven Design by Eric Evans](https://www.domainlanguage.com/ddd/)
- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)
- [PHP-DI Documentation](https://php-di.org/)
- [PSR-11 Container Interface](https://www.php-fig.org/psr/psr-11/)
- [Symfony Best Practices - Architecture](https://symfony.com/doc/current/best_practices.html)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
