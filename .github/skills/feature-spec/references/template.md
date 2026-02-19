# Feature Spec Template

Use this template for every feature specification. Fill in each section; remove nothing. If a section is not applicable, write "N/A — [reason]" so reviewers know it was considered.

---

```markdown
# Feature: [Clear, User-Focused Name]

> One-sentence summary of what this feature does for users.

## Problem Statement

### What problem are we solving?
[Describe the specific user pain point. Be concrete — include observed behaviors, not assumptions.]

### Who is affected?
[List the user roles/personas who experience this problem. Be specific.]

| Persona | Role | Pain Level | Current Workaround |
|---|---|---|---|
| [Name] | [Role] | [High/Medium/Low] | [What they do today] |

### How painful is it today?
[Quantify the impact: time wasted, error rates, support tickets, revenue lost, etc.]

## User Value

### Why is this valuable?
[What measurable improvement does this deliver? Connect to user outcomes, not system capabilities.]

### Success Metrics
[Define 2-4 specific, measurable indicators of success.]

| Metric | Current Baseline | Target | Measurement Method |
|---|---|---|---|
| [e.g., Project setup time] | [e.g., 2 hours] | [e.g., 30 minutes] | [e.g., Time tracking in app] |

## Feature Description

### Overview
[What the feature does, written from the user's perspective. Describe the experience, not the implementation.]

### User Flow
[Step-by-step description of how a user interacts with this feature. Use numbered steps.]

1. User navigates to [starting point]
2. User sees [what's presented]
3. User does [action]
4. System responds with [result]
5. User can then [next action]

### Key Behaviors
[Bullet list of the most important behaviors and rules.]

- [Behavior 1]
- [Behavior 2]
- [Behavior 3]

## Technical Requirements

### Required Capabilities
- [What the system must be able to do]

### Performance Requirements
- [Response time targets]
- [Throughput needs]
- [Concurrency expectations]

### Security Requirements
- [Authentication/authorization needs]
- [Data protection requirements]
- [Compliance considerations]

### Dependencies
- [Other features, services, or systems this depends on]
- [Third-party integrations needed]

## Acceptance Criteria

[Each criterion must be independently testable. Cover happy path, edge cases, and error states.]

### Happy Path
- [ ] [Given/When/Then or concrete checklist item]
- [ ] [Given/When/Then or concrete checklist item]

### Edge Cases
- [ ] [Given/When/Then or concrete checklist item]
- [ ] [Given/When/Then or concrete checklist item]

### Error States
- [ ] [Given/When/Then or concrete checklist item]
- [ ] [Given/When/Then or concrete checklist item]

## Scope Boundaries

### In Scope
- [What this feature includes]

### Out of Scope
- [What this feature explicitly does NOT include — and why]

### Future Considerations
- [Things deliberately deferred to later iterations]

## Business Context

### Cost Estimate
[Rough development effort: T-shirt size or sprint count. Not a commitment — a conversation starter.]

### Risk Assessment
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| [Risk 1] | [H/M/L] | [H/M/L] | [How to address] |

### Timeline Expectations
[When this is expected to ship, or what it's blocked by.]
```

---

## Template Usage Notes

**Adapting the template to context:**

| Context | Adaptation |
|---|---|
| Small feature / bug fix | Collapse Problem Statement to 1-2 sentences; simplify Success Metrics to one target; skip Risk Assessment |
| Large / cross-team feature | Expand Dependencies and Risk Assessment; add a "stakeholders" subsection; consider splitting into sub-features |
| API / developer-facing feature | Add an "API Surface" section with endpoint signatures, request/response examples |
| User-facing UI feature | Add wireframe descriptions or link mockups; expand User Flow with UI state descriptions |
| Infrastructure / backend feature | Replace User Flow with "System Flow"; focus Performance and Security Requirements |

**Quality checks after filling in the template:**

1. Can a new team member read this and understand what to build without additional context?
2. Can QA write test cases directly from the acceptance criteria?
3. Does every acceptance criterion map to a user-visible outcome?
4. Is the scope clear enough that two developers would build roughly the same thing?
5. Would a product manager approve the success metrics as meaningful?
