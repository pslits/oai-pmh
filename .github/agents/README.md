# Custom Agents for OAI-PMH Project

This directory contains custom GitHub Copilot agents tailored for the OAI-PMH repository project. Each agent represents a specialized role with specific tools, instructions, and workflows.

## Available Agents

### 1. 👨‍💼 Business Analyst (`@business-analyst`)
**Purpose:** Lead requirement gathering and translate business needs into technical specifications.

**When to use:**
- Starting a new project or major feature
- Need to clarify requirements
- Creating comprehensive requirements documents

**Key capabilities:**
- Generate structured requirements questionnaires
- Transform answers into detailed requirements documents
- Validate completeness and consistency
- No code generation (focuses on gathering requirements)

**Typical workflow:**
1. User invokes `@business-analyst` with project description
2. Agent creates requirements questionnaire
3. User fills out questionnaire
4. Agent generates comprehensive requirements document
5. **Handoff to** `@solutions-architect` to design architecture

---

### 2. 🏗️ Solutions Architect (`@solutions-architect`)
**Purpose:** Transform requirements into comprehensive, production-ready technical architectures.

**When to use:**
- After requirements are gathered
- Need to create Architecture Decision Records (ADRs)
- Designing system architecture and technical implementation plans

**Key capabilities:**
- Create 8-12 Architecture Decision Records
- Generate file structure documentation
- Create comprehensive technical design documents (100+ pages)
- Develop phase-based implementation plans
- Create architecture diagrams (Mermaid.js)

**Typical workflow:**
1. Reviews requirements document
2. Creates ADR directory structure and individual ADRs
3. Documents file structure
4. Generates technical design document with implementation plan
5. **Handoff to** `@senior-engineer` to begin implementation

---

### 3. 👨‍💻 Senior Software Engineer (`@senior-engineer`)
**Purpose:** Deliver high-quality, maintainable code following TDD principles.

**When to use:**
- Implementing features
- Writing code that passes quality gates
- Following red-green-refactor TDD cycle

**Key capabilities:**
- Strict TDD workflow (Red-Green-Refactor)
- Quality gate enforcement (PHPUnit, PHPStan, PHPCS)
- Domain-Driven Design patterns
- Value object implementation
- Progress tracking with todo lists
- Git operations

**Quality gates (must pass before commit):**
```bash
vendor\bin\phpunit           # All tests pass
vendor\bin\phpstan analyse   # PHPStan Level 8 clean
vendor\bin\phpcs             # PSR-12 compliant
```

**Typical workflow:**
1. Reviews architecture documents and ADRs
2. Writes failing test first (Red)
3. Implements minimum code to pass (Green)
4. Refactors while keeping tests passing
5. Runs quality gates
6. Updates progress tracking
7. **Handoff to** `@qa-auditor` for code review

---

### 4. 🔍 QA & Security Auditor (`@qa-auditor`)
**Purpose:** Provide rigorous, evidence-based critiques of code implementations.

**When to use:**
- After feature implementation
- Before merging to main branch
- Security vulnerability assessment needed
- Code quality review required

**Key capabilities:**
- Evidence-based analysis (runs actual tools)
- OWASP Top 10 security scanning
- Edge case identification
- Test coverage analysis
- Requirement traceability verification
- Comprehensive review reports (50-100+ pages)

**Review areas:**
- ✅ Requirement traceability
- ⚠️ Logic errors & edge cases
- 🔒 Security vulnerabilities
- 📊 Code quality & documentation
- 🧪 Test coverage

**Typical workflow:**
1. Gathers evidence (runs phpunit, phpstan, phpcs)
2. Systematic code audit
3. Security vulnerability scanning
4. Generates comprehensive review report
5. **Handoff to** `@senior-engineer` to fix issues

---

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Development Lifecycle                     │
└─────────────────────────────────────────────────────────────┘

1. REQUIREMENTS PHASE
   ┌─────────────────────┐
   │ @business-analyst   │ → Creates questionnaire
   │                     │ → Generates requirements doc
   └──────────┬──────────┘
              │ handoff
              ▼
2. ARCHITECTURE PHASE
   ┌─────────────────────┐
   │ @solutions-architect│ → Creates ADRs (8-12)
   │                     │ → Technical design doc
   │                     │ → Implementation plan
   └──────────┬──────────┘
              │ handoff
              ▼
3. IMPLEMENTATION PHASE
   ┌─────────────────────┐
   │ @senior-engineer    │ → TDD cycle (Red-Green-Refactor)
   │                     │ → Quality gates enforcement
   │                     │ → Progress tracking
   └──────────┬──────────┘
              │ handoff
              ▼
4. REVIEW PHASE
   ┌─────────────────────┐
   │ @qa-auditor         │ → Evidence collection
   │                     │ → Code audit
   │                     │ → Security scan
   │                     │ → Review report
   └──────────┬──────────┘
              │ handoff (if issues found)
              ▼
         Back to Step 3 (fix issues)
         OR
         ✅ Approve & Merge
```

---

## How to Use

### Invoking an Agent

In the GitHub Copilot Chat view:

```
@business-analyst I need to create a new OAI-PMH metadata format plugin
```

```
@solutions-architect Design the architecture based on docs/REPOSITORY_SERVER_REQUIREMENTS.md
```

```
@senior-engineer Implement the BaseURL value object following TDD
```

```
@qa-auditor Review the recent RepositoryIdentity implementation
```

### Using Handoffs

After an agent completes a task, you'll see handoff buttons like:
- **"Design Architecture"** → Switches to `@solutions-architect`
- **"Start Implementation"** → Switches to `@senior-engineer`
- **"Review Code Quality"** → Switches to `@qa-auditor`
- **"Fix Issues"** → Switches to `@senior-engineer`

Click these buttons to transition between phases with context preserved.

---

## Agent Configuration

### Tools Available to Each Agent

| Tool Category | Business Analyst | Solutions Architect | Senior Engineer | QA Auditor |
|--------------|------------------|---------------------|-----------------|------------|
| File Reading (readFile) | ✅ | ✅ | ✅ | ✅ |
| File Editing (editFiles) | ❌ | ❌ | ✅ | ❌ |
| File Creation (createFile) | ✅ | ✅ | ✅ | ❌ |
| Search (fileSearch, textSearch, codebase) | ✅ | ✅ | ✅ | ✅ |
| Tests (runTests) | ❌ | ❌ | ✅ | ✅ |
| Terminal (runInTerminal) | ❌ | ❌ | ✅ | ✅ |
| Git (gitkraken/*, changes) | ❌ | ❌ | ✅ | ✅ |
| Problems (problems) | ❌ | ❌ | ✅ | ✅ |
| Todo Lists (todos) | ✅ | ✅ | ✅ | ✅ |

### Model Configuration

All agents use **Claude Sonnet 4.5 (copilot)** for optimal performance on complex tasks.

---

## Best Practices

### 1. Follow the Workflow
Start with requirements → architecture → implementation → review. Don't skip phases.

### 2. Use Handoffs
Let agents pass context between phases using handoff buttons.

### 3. Trust the Process
- **Business Analyst**: Won't write code, only gathers requirements
- **Solutions Architect**: Won't implement, only designs
- **Senior Engineer**: Follows TDD strictly, enforces quality gates
- **QA Auditor**: Won't modify code, only documents findings

### 4. Review Agent Outputs
Each agent produces specific deliverables:
- Business Analyst → Requirements documents
- Solutions Architect → ADRs + Technical design
- Senior Engineer → Tested, quality-gated code
- QA Auditor → Review reports with actionable fixes

### 5. Iterative Refinement
Don't expect perfection in one pass. Use the review → fix → review cycle.

---

## Project-Specific Context

All agents are aware of:
- **Domain**: OAI-PMH 2.0 protocol implementation
- **Language**: PHP 8.0+
- **Standards**: PSR-12, PSR-4, DDD patterns
- **Quality Tools**: PHPUnit, PHPStan Level 8, PHPCS
- **Architecture**: Clean Architecture, Value Objects, Domain Entities

---

## Troubleshooting

### Agent Not Appearing in Dropdown
1. Check file is in `.github/agents/` directory
2. Ensure file has `.agent.md` extension
3. Verify YAML frontmatter is valid
4. Reload VS Code window

### Agent Not Using Expected Tools
1. Check `tools` array in YAML frontmatter
2. Verify tool names match VS Code tool identifiers
3. Some tools may not be available in your environment

### Handoffs Not Working
1. Verify target agent name in `handoffs.agent` field
2. Check target agent exists and is enabled
3. Ensure `handoffs` array is properly formatted in YAML

---

## Extending Agents

To modify an agent:

1. Open the `.agent.md` file
2. Update YAML frontmatter (tools, handoffs, description)
3. Modify markdown body (instructions, examples)
4. Save file (changes apply immediately)

To create a new agent:

```bash
# In Chat view, type:
/agents
# Select "Create new custom agent"
```

---

## References

- [VS Code Custom Agents Documentation](https://code.visualstudio.com/docs/copilot/customization/custom-agents)
- [GitHub Copilot Agent Tools](https://code.visualstudio.com/docs/copilot/agents/agent-tools)
- [Project Copilot Instructions](../copilot-instructions.md)

---

## License

These custom agents are part of the OAI-PMH project and are licensed under the MIT License.

**Author:** Paul Slits <paul.slits@gmail.com>  
**Copyright:** (c) 2025 Paul Slits  
**Link:** https://github.com/pslits/oai-pmh
