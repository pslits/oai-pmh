# User Story Template

Use this template for every user story. Fill in each section; mark any section not applicable as "N/A — [reason]."

---

```markdown
## Story: [Short, Action-Oriented Title]

**As a** [specific user role/persona],
**I want** [one clear goal],
**So that** [measurable benefit / reason it matters].

### Acceptance Criteria

#### Happy Path

- [ ] **Given** [precondition/context],
      **When** [user action],
      **Then** [expected observable result]

- [ ] **Given** [precondition/context],
      **When** [user action],
      **Then** [expected observable result]

#### Edge Cases

- [ ] **Given** [unusual but valid precondition],
      **When** [user action],
      **Then** [expected behavior]

#### Error States

- [ ] **Given** [error condition or system failure],
      **When** [user action],
      **Then** [graceful handling / user-visible feedback]

### Notes

- **Dependencies**: [Other stories this depends on, or "None"]
- **Design**: [Link to mockup/wireframe, or "N/A"]
- **Constraints**: [Performance targets, security requirements, or "None"]
- **Estimation**: [T-shirt size: XS / S / M / L — or story points if team uses them]
```

---

## Template Usage Notes

**Sizing guidance:**

| Size | Typical Effort | When to Use |
|---|---|---|
| XS | < 0.5 day | Config change, copy update, simple toggle |
| S | 0.5–1 day | Single behavior, one screen, straightforward logic |
| M | 1–3 days | Multiple behaviors, some edge cases, moderate complexity |
| L | 3–5 days | Complex logic, multiple integrations — consider splitting |

If a story is larger than L, it should be split.

**Acceptance criteria count guidance:**

| Count | Signal |
|---|---|
| 0–1 | Too few — story is under-specified; add at least one happy path + one edge/error case |
| 2–4 | Good — focused, testable, clear |
| 5–6 | Acceptable — make sure they all belong to one coherent story |
| 7+ | Too many — story is likely too large; split it |

**Adapting the template:**

| Context | Adaptation |
|---|---|
| Bug fix story | Precondition = "Given the bug exists"; acceptance = "bug is resolved + regression criteria" |
| API / backend story | Replace "user" with "API consumer" or "calling service"; acceptance criteria use request/response format |
| Non-functional story (performance, security) | Role = the affected user; benefit = the user-facing impact of the improvement |
| Spike / research story | Acceptance criteria = specific questions answered or decisions made, with a time-box |

**Quality checks after filling in the template:**

1. Could a developer start working on this without asking clarifying questions?
2. Could QA write test cases directly from the acceptance criteria?
3. Does the "So that" express genuine user value (not just "data is saved")?
4. Is the story small enough to complete in 1–3 days?
5. Does every acceptance criterion describe an observable outcome?
