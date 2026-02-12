# Architecture Decision Records (ADRs)

This directory contains Architecture Decision Records for the OAI-PMH Repository Server project.

## About ADRs

Architecture Decision Records capture important architectural decisions made in the project along with their context and consequences. They provide a historical record of why certain decisions were made and help maintain architectural consistency.

## ADR Format

Each ADR follows this structure:
- **Status**: Proposed, Accepted, Deprecated, or Superseded
- **Context**: The issue motivating this decision
- **Decision**: The change being proposed or made
- **Consequences**: The resulting context after applying the decision

## Index of ADRs

| ADR | Title | Status |
|-----|-------|--------|
| [ADR-0001](0001-tech-stack-selection.md) | Technology Stack Selection | Accepted |
| [ADR-0002](0002-layered-architecture.md) | Layered Architecture Pattern | Accepted |
| [ADR-0003](0003-database-abstraction.md) | Database Abstraction Strategy | Accepted |
| [ADR-0004](0004-plugin-architecture.md) | Plugin Architecture Design | Accepted |
| [ADR-0005](0005-caching-strategy.md) | Caching Strategy | Accepted |
| [ADR-0006](0006-resumption-token-implementation.md) | Resumption Token Implementation | Accepted |
| [ADR-0007](0007-security-authentication.md) | Security and Authentication Approach | Accepted |
| [ADR-0008](0008-configuration-management.md) | Configuration Management | Accepted |
| [ADR-0009](0009-event-driven-architecture.md) | Event-Driven Extension Points | Accepted |
| [ADR-0010](0010-xml-serialization.md) | XML Response Serialization | Accepted |

## Creating New ADRs

When creating a new ADR:
1. Copy the template from `adr-template.md`
2. Use the next sequential number (e.g., `0011-description.md`)
3. Fill in all sections with detailed information
4. Update this index with the new ADR
5. Get team review before marking as "Accepted"

## References

- [ADR GitHub Organization](https://adr.github.io/)
- [Documenting Architecture Decisions](https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions)
