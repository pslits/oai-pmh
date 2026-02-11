# ADR-0001: Technology Stack Selection

**Status**: Accepted  
**Date**: 2026-02-10  
**Deciders**: Solutions Architect, Development Team  
**Technical Story**: Repository Server Requirements - Section 4.1

---

## Context

The OAI-PMH Repository Server must support very large datasets (5M+ records), provide high performance (<1s response time), and maintain strict compliance with PHP best practices and OAI-PMH 2.0 specification. The technology stack must enable:

- **Reusability**: Easy deployment by any organization
- **Scalability**: Horizontal and vertical scaling capabilities
- **Maintainability**: Modern PHP standards, strong typing, comprehensive testing
- **Extensibility**: Plugin architecture for custom metadata formats and authentication
- **Integration**: Compatibility with existing value objects library (`pslits/oai-pmh`)

### Forces at Play

- **PHP Version**: Need modern language features (typed properties, constructor promotion, JIT) while maintaining reasonable compatibility
- **Database Support**: Must support multiple RDBMS (MySQL, PostgreSQL) without vendor lock-in
- **Standards Compliance**: PSR compliance required for interoperability and professionalism
- **Performance**: High-throughput requirements necessitate efficient dependencies
- **Ecosystem**: Must leverage mature, well-maintained libraries

---

## Decision

We will use the following technology stack for the OAI-PMH Repository Server:

### Core Platform

- **PHP 8.0+**: Minimum supported version
  - Leverages typed properties, constructor promotion, union types, match expressions
  - JIT compiler for improved performance
  - Strong type safety via strict_types declarations
- **Composer 2.0+**: Dependency management and autoloading
- **PSR Standards**: Strict compliance with PHP-FIG standards

### Web Server & Runtime

- **Apache 2.4+ or Nginx 1.18+**: With PHP-FPM for optimal performance
- **PHP Extensions Required**:
  - `pdo_mysql` (MySQL support)
  - `pdo_pgsql` (PostgreSQL support)
  - `mbstring` (UTF-8 handling)
  - `xml` / `libxml` (OAI-PMH XML responses)
  - `json` (configuration, logging)
  - `redis` (optional, for caching/rate limiting)

### Key Libraries & Frameworks

| Component | Library | Version | PSR Compliance | Rationale |
|-----------|---------|---------|----------------|-----------|
| **Database Abstraction** | Doctrine DBAL | ^3.0 | N/A | Database-agnostic queries, type mapping, query builder |
| **HTTP Foundation** | Nyholm/psr7 | ^1.5 | PSR-7, PSR-17 | HTTP request/response abstraction |
| **Dependency Injection** | PHP-DI/PHP-DI | ^7.0 | PSR-11 | Auto-wiring, configuration-based DI |
| **Event Dispatcher** | Symfony EventDispatcher | ^6.0 | PSR-14 | Event-driven extension points |
| **Logging** | Monolog | ^3.0 | PSR-3 | Structured logging, multiple handlers |
| **Caching** | Symfony Cache | ^6.0 | PSR-6, PSR-16 | Multiple backends (Redis, APCu, filesystem) |
| **Configuration** | Symfony Config + Yaml | ^6.0 | N/A | YAML parsing, validation, merging |
| **HTTP Client** | Guzzle | ^7.0 | PSR-7, PSR-18 | For testing and external requests |

### Database Systems

- **MySQL 5.7+**: Primary RDBMS target (most common in repository deployments)
- **PostgreSQL 10+**: Secondary RDBMS target (preferred by research institutions)
- **MariaDB 10.3+**: Implicitly supported via MySQL compatibility

### Caching Layer (Optional but Recommended)

- **Redis 5.0+**: Primary cache backend (distributed, fast, supports rate limiting)
- **Memcached 1.5+**: Alternative cache backend
- **APCu**: Single-server in-memory cache alternative
- **Filesystem**: Development/fallback cache

### Development & Quality Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **PHPUnit** | ^9.6 | Unit, integration, and compliance testing |
| **PHPStan** | ^1.10 | Static analysis at level 8 |
| **PHP_CodeSniffer** | ^3.7 | PSR-12 compliance checking |
| **Psalm** | ^5.0 | Additional static analysis (optional) |
| **Xdebug** | ^3.0 | Code coverage and debugging |
| **PHPBench** | ^1.2 | Performance benchmarking |

---

## Alternatives Considered

### Alternative 1: PHP 7.4 Compatibility

**Description**: Support PHP 7.4 to maximize compatibility with older servers.

**Pros**:
- Wider deployment compatibility
- Works on older OS distributions (e.g., Ubuntu 18.04 LTS)

**Cons**:
- Missing modern language features (constructor promotion, match, mixed type, union types)
- No JIT compiler performance benefits
- PHP 7.4 reaches end-of-life (November 2022 - already past)
- Less type safety (e.g., no `mixed` type)

**Why Rejected**: PHP 7.4 is already EOL. Modern projects should target actively maintained PHP versions. PHP 8.0+ features significantly improve code quality and maintainability.

### Alternative 2: Laravel Framework

**Description**: Build on top of Laravel framework for rapid development.

**Pros**:
- Batteries-included framework (ORM, routing, caching, queues)
- Large community and ecosystem
- Built-in testing tools and conventions

**Cons**:
- Heavy framework for a focused OAI-PMH server (overhead)
- Opinionated structure may conflict with DDD approach
- Additional learning curve for contributors
- Harder to use as a library (requires Laravel app structure)
- Tighter coupling to framework evolution

**Why Rejected**: OAI-PMH server is a specialized library/application. A micro-framework or component-based approach provides better flexibility and lighter weight. Domain-Driven Design is easier with standalone components.

### Alternative 3: Pure PDO (No Doctrine DBAL)

**Description**: Use native PDO directly without abstraction layer.

**Pros**:
- Zero abstraction overhead
- Direct control over all SQL
- Slightly better performance for simple queries

**Cons**:
- Must manually handle database differences (MySQL vs PostgreSQL syntax)
- No query builder (complex queries become unwieldy)
- No type mapping (manual serialization/deserialization)
- Harder to test (mocking PDO is cumbersome)

**Why Rejected**: Supporting multiple databases (MySQL, PostgreSQL, future others) without an abstraction layer leads to code duplication and complex conditional logic. Doctrine DBAL provides clean abstraction with minimal overhead.

### Alternative 4: Custom XML Library

**Description**: Build custom XML generation instead of using standard libraries.

**Pros**:
- Potentially optimized for OAI-PMH specific use case
- Full control over output format

**Cons**:
- Re-inventing the wheel
- Potential for XML injection vulnerabilities
- No standard compliance guarantees
- Maintenance burden

**Why Rejected**: PHP's built-in `XMLWriter` and `SimpleXML` are mature, secure, and performant. Custom implementation adds unnecessary risk and maintenance overhead.

---

## Consequences

### Positive Consequences

- **Modern PHP Features**: Constructor promotion, typed properties, match expressions improve code clarity and maintainability
- **Type Safety**: PHPStan Level 8 compliance prevents entire categories of bugs
- **Database Flexibility**: Doctrine DBAL enables supporting MySQL, PostgreSQL, and future databases with minimal code changes
- **Standards Compliance**: PSR interfaces ensure interoperability with other PHP libraries
- **Mature Ecosystem**: All selected libraries are industry-standard, well-documented, and actively maintained
- **Testing Quality**: PHPUnit + PHPStan + PHPCS provide comprehensive quality assurance
- **Performance**: PHP 8.0+ JIT compiler and efficient libraries support 5M+ record repositories
- **Plugin Architecture**: PSR-14 Event Dispatcher and PSR-11 Container enable clean extensibility

### Negative Consequences

- **PHP 8.0 Minimum**: May not work on older servers (mitigation: PHP 8.0 released Dec 2020, now mature and widely available)
- **Learning Curve**: Contributors must understand DDD, PSR standards, and selected libraries
- **Dependency Count**: Multiple libraries increase composer.json complexity (mitigation: all dependencies are stable and well-maintained)

### Neutral Consequences

- **Composer Required**: All deployments must use Composer (industry standard for modern PHP)
- **Extension Requirements**: Redis extension optional but recommended for production deployments
- **Multiple Database Drivers**: PDO extensions for MySQL and PostgreSQL must be installed

---

## Compliance

### Alignment with Requirements

- **Requirement 4.1.1 (Core Technologies)**: ✅ PHP 8.0+, Composer, Apache/Nginx, MySQL 5.7+, PostgreSQL 10+, Redis 5.0+
- **Requirement 4.1.2 (Development Tools)**: ✅ PHPUnit 9.6+, PHPStan Level 8, PHP_CodeSniffer
- **Requirement 4.3.1 (Coding Standards)**: ✅ PSR-1, PSR-3, PSR-4, PSR-6, PSR-7, PSR-11, PSR-12, PSR-14
- **Requirement 3.1 (Performance)**: ✅ PHP 8 JIT compiler, efficient libraries, caching support
- **Requirement 2.3.1 (Database-Driven Architecture)**: ✅ Doctrine DBAL for MySQL and PostgreSQL

### Alignment with Architectural Principles

- **Separation of Concerns**: PSR interfaces decouple components
- **Open/Closed Principle**: PSR-14 Event Dispatcher allows extensions without modification
- **Dependency Inversion**: PSR-11 Container enables dependency injection
- **Testability**: All components are mockable and testable

---

## Implementation Guidance

### Required Actions

1. **Create composer.json** with all required dependencies
2. **Configure autoloading** using PSR-4 standard
3. **Set up directory structure** following best practices
4. **Configure quality tools** (phpunit.xml, phpstan.neon, phpcs.xml)
5. **Establish CI/CD pipeline** (GitHub Actions) running all quality checks
6. **Document installation requirements** in README.md

### Dependencies

- **Value Objects Library**: `pslits/oai-pmh` must be required as dependency
- **PHP Extensions**: Installation documentation must list required and optional extensions
- **Database Setup**: Documentation must cover MySQL and PostgreSQL setup

### Timeline

- **Week 1**: Create composer.json, directory structure, CI/CD configuration
- **Week 2**: Configure quality tools, establish coding standards
- **Week 3**: Set up dependency injection container, basic infrastructure
- **Week 4**: Validate all tooling works with sample code

---

## Validation

### Success Criteria

- [x] `composer install` successfully installs all dependencies
- [x] PHPStan Level 8 runs without errors on sample code
- [x] PHP_CodeSniffer validates PSR-12 compliance
- [x] PHPUnit runs tests with code coverage
- [x] CI/CD pipeline runs all quality checks automatically
- [x] Sample code demonstrates Doctrine DBAL database queries
- [x] Sample code demonstrates PSR-7 HTTP request handling
- [x] Sample code demonstrates PSR-3 logging

### Testing Strategy

- **Unit Tests**: Mock all external dependencies using PSR interfaces
- **Integration Tests**: Test with real MySQL and PostgreSQL databases
- **Compliance Tests**: Validate OAI-PMH XML responses against schema
- **Performance Tests**: Benchmark with PHPBench, verify <1s response time targets

---

## References

- [PHP 8.0 Release Notes](https://www.php.net/releases/8.0/en.php)
- [Doctrine DBAL Documentation](https://www.doctrine-project.org/projects/dbal.html)
- [PHP-FIG PSR Standards](https://www.php-fig.org/psr/)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- [Symfony Components](https://symfony.com/components)
- [PHPStan Documentation](https://phpstan.org/)
- [OAI-PMH 2.0 Specification](https://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm)

---

## Revision History

| Date | Version | Changes | Author |
|------|---------|---------|--------|
| 2026-02-10 | 1.0 | Initial version | Solutions Architect |
