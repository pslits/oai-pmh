# OAI-PMH Project Skills

Custom skills for the OAI-PMH project that extend Claude's capabilities with specialized knowledge about value objects, domain-driven design patterns, and OAI-PMH protocol compliance.

## Available Skills

### 1. Value Object Validator

**Location:** `value-object-validator/`

**Purpose:** Validates PHP value objects against OAI-PMH project standards

**Use cases:**
- Review new value object implementations
- Validate refactoring changes (parameter renaming, method extraction)
- Check compliance with PSR-12, PHPStan Level 8
- Verify OAI-PMH 2.0 specification adherence
- Ensure domain-specific getter patterns (NOT getValue())

**Quick usage in Copilot:**
```
@workspace Using the value-object-validator skill from .github/skills/value-object-validator/SKILL.md, validate src/Domain/ValueObject/BaseURL.php
```

**Command line usage:**
```bash
php .github/skills/value-object-validator/scripts/validate_vo.php src/Domain/ValueObject/BaseURL.php
```

**What it checks:**
- ✅ File headers (@author, @copyright, @license, @link, @since)
- ✅ Class structure (final, private properties, immutability)
- ✅ Domain-specific getters (e.g., getBaseUrl() - REQUIRED)
- ✅ Required methods (constructor, equals(), __toString())
- ✅ Validation logic (split into focused methods)
- ✅ Documentation (OAI-PMH spec references, complete docblocks)
- ✅ Naming conventions (descriptive parameters, not $value or $other)
- ✅ Code quality (PHPStan Level 8, PSR-12 compliance)
- ✅ Test coverage (BDD-style tests, all scenarios)

**Resources:**
- `SKILL.md` - Main skill documentation
- `scripts/validate_vo.php` - Automated validation script
- `references/vo-checklist.md` - Complete 90+ point checklist
- `references/oai-pmh-requirements.md` - OAI-PMH compliance guide
- `assets/value-object-template.php` - Template following all standards

## VSCode Integration

### Method 1: Reference Skills in Copilot Chat

```
@workspace Using the value-object-validator skill, check my new ProtocolVersion value object
```

### Method 2: Auto-load Skills in Settings

Add to [.vscode/settings.json](../.vscode/settings.json):

```json
{
    "github.copilot.chat.codeGeneration.instructions": [
        {
            "file": ".github/copilot-instructions.md"
        },
        {
            "file": ".github/skills/value-object-validator/SKILL.md"
        }
    ]
}
```

### Method 3: Create Snippets

Add to `.vscode/oai-pmh.code-snippets`:

```json
{
  "Validate Value Object": {
    "prefix": "@validate-vo",
    "body": [
      "@workspace Using .github/skills/value-object-validator/SKILL.md,",
      "validate $1"
    ],
    "description": "Validate value object against OAI-PMH standards"
  }
}
```

## Adding New Skills

To create additional custom skills for this project:

1. **Create directory structure:**
   ```
   .github/skills/your-skill-name/
   ├── SKILL.md                 # Main skill documentation
   ├── LICENSE.txt              # MIT License
   ├── scripts/                 # Executable scripts (optional)
   ├── references/              # Reference documentation (optional)
   └── assets/                  # Templates, examples (optional)
   ```

2. **Write SKILL.md with:**
   - YAML frontmatter (name, description, license)
   - Clear instructions for using the skill
   - Examples and usage patterns
   - References to bundled resources

3. **Follow skill-creator pattern from:**
   `.github/copilot-skills/skills/skill-creator/SKILL.md`

4. **Test the skill:**
   - Use in Copilot Chat
   - Verify instructions are clear
   - Test any bundled scripts

5. **Document in this README**

## Skill Development Resources

- **Skill Creator Guide:** `.github/copilot-skills/skills/skill-creator/SKILL.md`
- **Example Skills:** `.github/copilot-skills/skills/` (in submodule)
- **Project Standards:** `.github/copilot-instructions.md`
- **Agent Roles:** `.github/agents/`

## Directory Structure

```
.github/
├── skills/                              # Custom OAI-PMH project skills
│   ├── README.md                        # This file
│   └── value-object-validator/          # Value object validation skill
│       ├── SKILL.md
│       ├── LICENSE.txt
│       ├── scripts/
│       │   └── validate_vo.php
│       ├── references/
│       │   ├── vo-checklist.md
│       │   └── oai-pmh-requirements.md
│       └── assets/
│           └── value-object-template.php
├── copilot-skills/                      # Anthropic Skills submodule
│   └── skills/                          # Reusable skills from Anthropic
│       └── skill-creator/               # Skill creation framework
└── copilot-instructions.md              # Project coding standards
```

## Best Practices

1. **Keep skills focused** - One skill per specific task
2. **Be concise** - Context window is shared resource
3. **Include examples** - Show don't tell
4. **Test thoroughly** - Validate scripts work correctly
5. **Document clearly** - Future you will thank you
6. **Version control** - Track changes to skills
7. **Reference project standards** - Link to .github/copilot-instructions.md

## Related Documentation

- [Project Coding Standards](../copilot-instructions.md)
- [Agent Roles and Workflows](../agents/README.md)
- [Value Objects Index](../../docs/VALUE_OBJECTS_INDEX.md)
- [Architecture Documentation](../../docs/)

---

*Skills are modular capabilities that extend Claude's expertise with project-specific knowledge and workflows.*
