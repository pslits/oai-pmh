# Data Schema: features-data.json

Each issue is one JSON object in a top-level array. All string values must use plain ASCII
where possible — avoid em-dashes (`—`), smart quotes, and `&` in values that will be
interpolated into PowerShell string literals.

## Required Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | string | Unique feature ID, e.g. `"F-001"`. Used as the key in the issue map. |
| `title` | string | Issue title as it appears on GitHub. |
| `description` | string | Body text for the Description section. |
| `blockedBy` | string[] | IDs of features this one depends on. Empty array if none. |
| `blocks` | string[] | IDs of features that depend on this one. Empty array if none. |

## Optional Metadata Fields

Any additional fields will be rendered as `**Key:** value` frontmatter lines at the top of
the issue body. Common extras:

| Field | Example value |
|-------|---------------|
| `priority` | `"MVP"` |
| `phase` | `"Phase 3 - Protocol Core"` |
| `context` | `"Protocol"` |

## Minimal Example

```json
[
  {
    "id": "F-001",
    "title": "F-001: Project Foundation and Shared Contracts",
    "priority": "MVP",
    "phase": "Phase 0 - Foundation",
    "context": "Cross-cutting",
    "description": "Establish the project skeleton and shared PHP interfaces.",
    "blockedBy": [],
    "blocks": ["F-002", "F-003"]
  },
  {
    "id": "F-002",
    "title": "F-002: Configuration Management",
    "priority": "MVP",
    "phase": "Phase 1 - Configuration",
    "context": "Configuration",
    "description": "Implement the Configuration aggregate.",
    "blockedBy": ["F-001"],
    "blocks": []
  }
]
```

## String Safety Rules

- Replace `—` (em-dash) with `-`
- Replace `&` with `and` (the script renders these into PS strings)
- Do NOT use `\n` escape in JSON values — use normal text. The script handles newlines.
- Avoid backtick `` ` `` characters in description text (PS escape character).
