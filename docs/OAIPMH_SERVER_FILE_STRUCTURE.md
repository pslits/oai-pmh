# OAI-PMH Repository Server - File Structure

**Document Version:** 1.0  
**Date:** 2026-02-10  
**Related ADRs:** ADR-0002 (Layered Architecture)

---

## Directory Structure Overview

```
oai-pmh-server/
├── .github/
│   ├── adr/                          # Architecture Decision Records
│   │   ├── README.md
│   │   ├── adr-template.md
│   │   ├── 0001-tech-stack-selection.md
│   │   ├── 0002-layered-architecture.md
│   │   └── ...
│   ├── workflows/                    # GitHub Actions CI/CD
│   │   ├── ci.yml                    # Run tests, quality checks
│   │   ├── deploy.yml                # Deployment automation
│   │   └── security-scan.yml         # Dependency scanning
│   └── ISSUE_TEMPLATE/
│       ├── bug_report.md
│       ├── feature_request.md
│       └── plugin_submission.md
│
├── bin/
│   └── oai-pmh                       # CLI entry point
│
├── config/
│   ├── config.yaml                   # Main configuration
│   ├── config.example.yaml           # Example template for users
│   ├── database_mapping.yaml         # Database schema mapping
│   ├── database_mapping.example.yaml
│   ├── metadata_formats.yaml         # Metadata format plugins config
│   ├── dependencies.php              # DI container bindings
│   ├── routes.php                    # HTTP routes (if routing used)
│   ├── .env                          # Environment variables (gitignored)
│   └── .env.example                  # Example environment file
│
├── docs/
│   ├── README.md                     # Documentation index
│   ├── installation.md               # Installation guide
│   ├── configuration.md              # Configuration reference
│   ├── database-mapping.md           # Database mapping guide
│   ├── plugin-development.md         # How to create plugins
│   ├── api-reference.md              # Generated API docs
│   ├── troubleshooting.md            # Common issues
│   ├── migration/                    # Migration guides
│   │   ├── from-dspace.md
│   │   ├── from-eprints.md
│   │   └── from-other-oai-servers.md
│   ├── examples/                     # Configuration examples
│   │   ├── dspace-mapping.yaml
│   │   ├── eprints-mapping.yaml
│   │   └── custom-mapping.yaml
│   └── architecture/                 # Architecture diagrams
│       ├── system-overview.md
│       ├── data-flow.md
│       └── deployment-patterns.md
│
├── public/
│   └── index.php                     # HTTP entry point
│
├── src/
│   ├── Application/                  # APPLICATION LAYER
│   │   ├── Handler/                  # OAI-PMH verb handlers
│   │   │   ├── IdentifyHandler.php
│   │   │   ├── GetRecordHandler.php
│   │   │   ├── ListRecordsHandler.php
│   │   │   ├── ListIdentifiersHandler.php
│   │   │   ├── ListSetsHandler.php
│   │   │   └── ListMetadataFormatsHandler.php
│   │   ├── Service/
│   │   │   ├── ResumptionTokenService.php
│   │   │   ├── MetadataFormatRegistry.php
│   │   │   └── SetHierarchyService.php
│   │   ├── DTO/                      # Data Transfer Objects
│   │   │   ├── IdentifyResponse.php
│   │   │   ├── RecordResponse.php
│   │   │   ├── ListRecordsResponse.php
│   │   │   ├── ListResponse.php
│   │   │   └── ErrorResponse.php
│   │   └── Validator/
│   │       ├── OaiPmhValidator.php
│   │       └── ParameterValidator.php
│   │
│   ├── Domain/                       # DOMAIN LAYER
│   │   ├── Entity/
│   │   │   ├── Record.php
│   │   │   ├── RecordHeader.php
│   │   │   ├── Set.php
│   │   │   └── MetadataFormat.php
│   │   ├── ValueObject/              # Additional VOs (beyond library)
│   │   │   ├── RecordIdentifier.php
│   │   │   ├── SetSpec.php
│   │   │   ├── OaiVerb.php
│   │   │   └── ResumptionToken.php
│   │   ├── Collection/
│   │   │   ├── RecordCollection.php
│   │   │   ├── RecordHeaderCollection.php
│   │   │   └── SetCollection.php
│   │   ├── Repository/               # Repository interfaces
│   │   │   └── RepositoryInterface.php
│   │   ├── Service/
│   │   │   └── SetHierarchyService.php
│   │   └── Exception/
│   │       ├── OaiPmhException.php
│   │       ├── BadArgumentException.php
│   │       ├── BadResumptionTokenException.php
│   │       ├── BadVerbException.php
│   │       ├── CannotDisseminateFormatException.php
│   │       ├── IdDoesNotExistException.php
│   │       ├── NoRecordsMatchException.php
│   │       ├── NoMetadataFormatsException.php
│   │       └── NoSetHierarchyException.php
│   │
│   ├── Infrastructure/               # INFRASTRUCTURE LAYER
│   │   ├── Repository/
│   │   │   ├── DoctrineRecordRepository.php
│   │   │   ├── Adapter/
│   │   │   │   ├── QueryAdapterInterface.php
│   │   │   │   ├── MySqlQueryAdapter.php
│   │   │   │   ├── PostgreSqlQueryAdapter.php
│   │   │   │   └── AbstractQueryAdapter.php
│   │   │   └── Mapping/
│   │   │       ├── DatabaseMapping.php
│   │   │       ├── ColumnMapping.php
│   │   │       ├── MetadataMapping.php
│   │   │       └── SetMapping.php
│   │   ├── Cache/
│   │   │   ├── CachedRepositoryDecorator.php
│   │   │   ├── CacheWarmer.php
│   │   │   └── CacheInvalidator.php
│   │   ├── Config/
│   │   │   ├── YamlConfigLoader.php
│   │   │   ├── ConfigValidator.php
│   │   │   └── EnvironmentVariableResolver.php
│   │   ├── Event/
│   │   │   ├── EventDispatcher.php
│   │   │   └── Subscriber/
│   │   │       ├── LoggingSubscriber.php
│   │   │       └── MetricsSubscriber.php
│   │   ├── Logging/
│   │   │   ├── MonologFactory.php
│   │   │   └── Processor/
│   │   │       ├── RequestIdProcessor.php
│   │   │       └── ContextProcessor.php
│   │   ├── Authentication/
│   │   │   ├── AuthenticationProviderInterface.php
│   │   │   ├── BasicAuthProvider.php
│   │   │   ├── ApiKeyProvider.php
│   │   │   └── AuthenticationManager.php
│   │   ├── RateLimiting/
│   │   │   ├── RateLimiter.php
│   │   │   ├── RateLimitStorage.php
│   │   │   └── RedisRateLimitStorage.php
│   │   ├── Plugin/
│   │   │   ├── PluginDiscovery.php
│   │   │   ├── PluginLoader.php
│   │   │   └── PluginValidator.php
│   │   └── Persistence/
│   │       ├── ConnectionFactory.php
│   │       └── Migration/
│   │           ├── MigrationRunner.php
│   │           └── Migrations/
│   │               └──Version20260210000000_InitialSchema.php
│   │
│   ├── Presentation/                 # PRESENTATION LAYER
│   │   ├── Http/
│   │   │   ├── Controller/
│   │   │   │   └── OaiPmhController.php
│   │   │   ├── Middleware/
│   │   │   │   ├── AuthenticationMiddleware.php
│   │   │   │   ├── RateLimitingMiddleware.php
│   │   │   │   ├── LoggingMiddleware.php
│   │   │   │   ├── ErrorHandlerMiddleware.php
│   │   │   │   └── CacheMiddleware.php
│   │   │   └── ResponseFactory.php
│   │   ├── Serializer/
│   │   │   ├── OaiPmhXmlSerializer.php
│   │   │   ├── ErrorSerializer.php
│   │   │   ├── IdentifySerializer.php
│   │   │   ├── RecordSerializer.php
│   │   │   └── SetSerializer.php
│   │   └── Metrics/
│   │       ├── PrometheusExporter.php
│   │       └── HealthCheckEndpoint.php
│   │
│   └── Plugin/                       # Plugin interfaces and base classes
│       ├── MetadataFormat/
│       │   ├── MetadataFormatInterface.php
│       │   ├── AbstractMetadataFormat.php
│       │   └── OaiDc/
│       │       └── OaiDcFormat.php   # Default Dublin Core implementation
│       ├── Authentication/
│       │   └── AuthenticationProviderInterface.php
│       └── Event/
│           ├── RecordRetrievedEvent.php
│           ├── OaiRequestReceivedEvent.php
│           ├── OaiResponseGeneratedEvent.php
│           └── CacheMissEvent.php
│
├── tests/
│   ├── Unit/
│   │   ├── Application/
│   │   │   ├── Handler/
│   │   │   │   ├── IdentifyHandlerTest.php
│   │   │   │   ├── GetRecordHandlerTest.php
│   │   │   │   └── ...
│   │   │   └── Service/
│   │   │       ├── ResumptionTokenServiceTest.php
│   │   │       └── MetadataFormatRegistryTest.php
│   │   ├── Domain/
│   │   │   ├── Entity/
│   │   │   │   ├── RecordTest.php
│   │   │   │   └── SetTest.php
│   │   │   └── ValueObject/
│   │   │       ├── RecordIdentifierTest.php
│   │   │       └── SetSpecTest.php
│   │   └── Infrastructure/
│   │       ├── Repository/
│   │       │   └── DoctrineRecordRepositoryTest.php
│   │       └── Cache/
│   │           └── CachedRepositoryDecoratorTest.php
│   │
│   ├── Integration/
│   │   ├── Repository/
│   │   │   ├── MySqlRepositoryTest.php
│   │   │   └── PostgreSqlRepositoryTest.php
│   │   ├── Database/
│   │   │   ├── MappingTest.php
│   │   │   └── QueryBuilderTest.php
│   │   └── Http/
│   │       └── OaiPmhEndpointTest.php
│   │
│   ├── Compliance/
│   │   ├── OaiPmhValidatorTest.php   # Tests against OAI-PMH spec
│   │   └── XmlSchemaValidationTest.php
│   │
│   ├── Performance/
│   │   ├── LargeDatasetBenchmark.php
│   │   └── CachingBenchmark.php
│   │
│   ├── Fixtures/
│   │   ├── DatabaseFixtures.php
│   │   ├── RecordFixtures.php
│   │   └── SampleData/
│   │       ├── records.xml
│   │       └── sample_database.sql
│   │
│   └── bootstrap.php                 # Test bootstrap
│
├── templates/                        # Optional: Twig templates for complex XML
│   └── oai/
│       ├── identify.xml.twig
│       └── error.xml.twig
│
├── var/                              # Runtime files (gitignored)
│   ├── cache/                        # Filesystem cache
│   ├── log/                          # Log files
│   └── tmp/                          # Temporary files
│
├── vendor/                           # Composer dependencies (gitignored)
│
├── .gitignore
├── .editorconfig
├── .php-cs-fixer.php                 # Code style configuration
├── composer.json
├── composer.lock
├── phpunit.xml
├── phpstan.neon
├── phpcs.xml
├── README.md
├── LICENSE.txt
├── CHANGELOG.md
├── CONTRIBUTING.md
└── docker-compose.yml                # Docker setup for demo/development
```

---

## Directory Purpose Descriptions

### Root Level

| Directory/File | Purpose |
|----------------|---------|
| `.github/` | GitHub-specific files (CI/CD, ADRs, issue templates) |
| `bin/` | CLI scripts (migrations, cache clear, validation) |
| `config/` | Configuration files (YAML, DI bindings, routing) |
| `docs/` | Documentation (guides, API reference, examples) |
| `public/` | Web server document root (single index.php entry point) |
| `src/` | Application source code (4-layer architecture) |
| `tests/` | Test suite (unit, integration, compliance, performance) |
| `var/` | Runtime generated files (cache, logs) |
| `vendor/` | Composer dependencies |
| `composer.json` | Dependency management and package metadata |
| `phpunit.xml` | PHPUnit configuration |
| `phpstan.neon` | PHPStan static analysis configuration |
| `phpcs.xml` | PHP_CodeSniffer coding standards configuration |

### Source Code Layers (`src/`)

| Layer | Path | Responsibility |
|-------|------|----------------|
| **Domain** | `src/Domain/` | Business logic, entities, value objects, repository interfaces |
| **Application** | `src/Application/` | Use cases, OAI-PMH verb handlers, services, DTOs |
| **Infrastructure** | `src/Infrastructure/` | Database, cache, config, logging, authentication implementations |
| **Presentation** | `src/Presentation/` | HTTP controllers, XML serialization, middleware |
| **Plugin** | `src/Plugin/` | Plugin interfaces and base implementations |

### Testing Structure (`tests/`)

| Directory | Purpose |
|-----------|---------|
| `Unit/` | Fast, isolated tests with mocked dependencies |
| `Integration/` | Tests with real database, cache, etc. |
| `Compliance/` | OAI-PMH specification compliance validation |
| `Performance/` | Benchmarks and performance regression tests |
| `Fixtures/` | Test data and sample databases |

---

## Namespace Mapping

```
OaiPmh\Application\*         → src/Application/*
OaiPmh\Domain\*              → src/Domain/*
OaiPmh\Infrastructure\*      → src/Infrastructure/*
OaiPmh\Presentation\*        → src/Presentation/*
OaiPmh\Plugin\*              → src/Plugin/*
OaiPmh\Tests\*               → tests/*
```

---

## Key Files Detail

### Entry Points

**HTTP Entry Point** (`public/index.php`):
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OaiPmh\Infrastructure\Config\YamlConfigLoader;
use OaiPmh\Presentation\Http\Controller\OaiPmhController;

$config = YamlConfigLoader::load(__DIR__ . '/../config/config.yaml');
$container = require __DIR__ . '/../config/dependencies.php';

$controller = $container->get(OaiPmhController::class);
$request = \Nyholm\Psr7\Factory\Psr17Factory::fromGlobals();
$response = $controller->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();
```

**CLI Entry Point** (`bin/oai-pmh`):
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('OAI-PMH Repository Server', '1.0.0');

// Register commands
$application->add(new \OaiPmh\Cli\Command\MigrateCommand());
$application->add(new \OaiPmh\Cli\Command\CacheClearCommand());
$application->add(new \OaiPmh\Cli\Command\ValidateMappingCommand());

$application->run();
```

---

## Configuration Files Detail

### Main Configuration (`config/config.yaml`)

Centralizes all server configuration. See ADR-0008 for full example.

### Database Mapping (`config/database_mapping.yaml`)

Defines how existing database schema maps to OAI-PMH model. See ADR-0003 for full example.

### Dependency Injection (`config/dependencies.php`)

PHP-DI container configuration:
```php
<?php
use function DI\create;
use function DI\get;

return [
    \Doctrine\DBAL\Connection::class => /* factory */,
    \OaiPmh\Domain\Repository\RepositoryInterface::class => get(\OaiPmh\Infrastructure\Repository\DoctrineRecordRepository::class),
    // ... more bindings
];
```

---

## Deployment Considerations

### Production File Structure

```
/var/www/oai-pmh-server/
├── current/                  # Symlink to current release
│   ├──public/
│   ├── src/
│   ├── vendor/
│   └── ...
├── releases/
│   ├── 2026-02-10_12-00-00/
│   └── 2026-02-09_10-30-00/
├── shared/
│   ├── config/
│   │   ├── config.yaml       # Persistent configuration
│   │   └── .env
│   └── var/
│       ├── cache/
│       └── log/
```

### Docker Structure

```
docker/
├── Dockerfile
├── docker-compose.yml
├── php/
│   └── php.ini
├── nginx/
│   └── default.conf
└── mysql/
    └── init.sql
```

---

## File Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| **Classes** | PascalCase | `OaiPmhController.php` |
| **Interfaces** | PascalCase + Interface suffix | `RepositoryInterface.php` |
| **Tests** | PascalCase + Test suffix | `RecordTest.php` |
| **Config** | lowercase_underscore.yaml | `database_mapping.yaml` |
| **Docs** | lowercase-hyphen.md | `plugin-development.md` |

---

## Version Control Exclusions (.gitignore)

```
# Dependencies
/vendor/

# Environment
.env
config/config.yaml

# Runtime
/var/cache/*
/var/log/*
/var/tmp/*

# IDE
.idea/
.vscode/
*.swp

# OS
.DS_Store
Thumbs.db

# Test artifacts
/coverage/
.phpunit.result.cache
```

---

## Build Artifacts

During CI/CD:
- `coverage/` - Code coverage reports
- `build/` - PHPStan, PHPCS reports
- `dist/` - Distribution package (if applicable)

---

## References

- ADR-0002: Layered Architecture Pattern
- ADR-0003: Database Abstraction Strategy
- PSR-4 Autoloading Standard

---

**Document maintained by**: Solutions Architect  
**Last updated**: 2026-02-10
