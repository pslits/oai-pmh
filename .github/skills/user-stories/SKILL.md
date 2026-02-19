```skill
---
name: user-stories
description: Write well-structured user stories under a feature. Use when users want to break a feature into user stories, write user stories, create acceptance criteria for stories, decompose features into deliverable work items, or draft stories for sprint planning. Produces stories that follow the standard "As a / I want / So that" format, pass INVEST criteria, and include testable Given/When/Then acceptance criteria.
---

# User Story Writer

Break features into clear, actionable user stories that communicate value, enable estimation, and are ready for sprint planning.

## Relationship to Features

A **feature** is a single deliverable unit of functionality that solves a user problem. A **user story** is a thin vertical slice of a feature — one small, valuable increment that a team can build, test, and ship within a sprint.

This skill assumes the feature is already defined (ideally via the `feature-spec` skill). If the user provides only a vague idea, prompt them to clarify the feature scope first.

## Workflow

### Step 1: Understand the Feature

Before writing stories, confirm understanding of:

1. **What is the feature?** — One-sentence summary of the user-facing capability
2. **Who are the users?** — Specific roles/personas affected
3. **What is the user flow?** — End-to-end interaction from the user's perspective
4. **What are the acceptance criteria at the feature level?** — Definition of done for the whole feature
5. **What are the scope boundaries?** — What's in and out of scope

If a feature spec document exists, read it. If not, ask the user for this context in whatever format they prefer.

### Step 2: Decompose into Stories

Break the feature into stories using these splitting strategies (in order of preference):

1. **By workflow steps** — Each step in the user flow becomes a story
2. **By user type** — Different users get different stories
3. **By data variations** — Different input types or data handled separately
4. **By CRUD operations** — View, create, edit, delete as separate stories
5. **By happy path vs. edge cases** — Core path first, then error handling and edge cases

See [references/splitting-guide.md](references/splitting-guide.md) for detailed patterns and examples.

**Splitting rules:**
- Each story must deliver user-visible value on its own
- Each story must be independently testable
- Each story must fit in one sprint (ideally 1–3 days of work)
- Never split by technical layer (frontend/backend/database) — that produces tasks, not stories

### Step 3: Write Each Story

Use the template in [references/template.md](references/template.md). Every story must include:

1. **Title** — Short, action-oriented (`User can save draft order`)
2. **Story statement** — `As a [role], I want [goal], so that [benefit]`
3. **Acceptance criteria** — Given/When/Then format, independently testable
4. **Notes** — Dependencies, constraints, design references (optional)

**Writing principles:**
- **User language, not system language** — "I can see my order history" not "System retrieves order records from database"
- **One goal per story** — If you write "and" in the goal, it's probably two stories
- **Benefit must be real** — "So that I can complete my purchase" not "So that the system stores data"
- **Active voice, present tense** — "User sees confirmation" not "Confirmation is displayed"
- **Concrete over vague** — "within 2 seconds" not "quickly"

### Step 4: Write Acceptance Criteria

Each story needs 2–6 acceptance criteria in Given/When/Then (Gherkin) format:

```
Given [precondition / context]
When [action the user takes]
Then [observable expected result]
```

**Rules for good acceptance criteria:**
- Each criterion is independently testable
- Cover the happy path first
- Include at least one negative/edge case per story
- Specify performance thresholds if relevant
- State what should NOT happen where ambiguity exists
- QA must be able to write test cases directly from these — no guessing
- Avoid vague language: "works correctly," "looks good," "fast" are not testable

**Bad criteria vs. good criteria:**

| Bad | Good |
|---|---|
| "Cart works correctly" | "Given items in cart, when user refreshes page, then cart items persist" |
| "Fast page load" | "Given user navigates to dashboard, when page loads, then content renders within 1.5 seconds" |
| "Handles errors" | "Given payment service is unavailable, when user clicks Pay, then user sees 'Payment temporarily unavailable' message and cart is preserved" |

### Step 5: Validate with INVEST

Run every story through the INVEST checklist in [references/invest-checklist.md](references/invest-checklist.md). For each story, confirm:

- **I**ndependent — Can be built and delivered without other stories
- **N**egotiable — Details can be discussed; it's a conversation, not a contract
- **V**aluable — Delivers clear value to a user or the business
- **E**stimable — Team can estimate effort with reasonable confidence
- **S**mall — Fits in one sprint (1–3 days ideal)
- **T**estable — Acceptance criteria are specific and verifiable

Flag any story that fails a criterion. Propose a fix:
- Fails **Independent**: Identify and document the dependency, or restructure
- Fails **Valuable**: Rewrite the benefit or merge with another story
- Fails **Small**: Split further using the splitting strategies
- Fails **Testable**: Rewrite acceptance criteria to be concrete

Present a summary per story: ✅ passing | ⚠️ needs attention (with recommendation).

### Step 6: Order Stories

Suggest a logical implementation order based on:

1. **Dependencies** — Stories that unblock others come first
2. **User value** — Highest-value stories prioritized
3. **Risk** — High-risk/uncertainty stories early (fail fast)
4. **Happy path first** — Core flow before edge cases

Present the ordered backlog as a numbered list with one-line rationale per story.

## Common Pitfalls

| Pitfall | Symptom | Fix |
|---|---|---|
| **Technical task masquerading as story** | "Set up database schema" — no user role, no value | Rewrite from user perspective or demote to a task under a real story |
| **Epic disguised as story** | Story has 10+ acceptance criteria or "and" in the goal | Split into smaller stories |
| **Missing benefit** | "So that the data is stored" — no user value | Ask: "Why does the user care?" |
| **Vague acceptance criteria** | "Works correctly," "Looks good" | Rewrite with specific Given/When/Then |
| **Solution in the story** | "As a user, I want a dropdown menu" — prescribes UI | Rewrite around the need: "I want to select my country from a list" |
| **Horizontal slicing** | "Build the API," "Build the UI" — technical layers | Re-slice vertically: each story delivers through all layers |

## Output Format

Produce stories as a single Markdown document titled `stories-<feature-short-name>.md`.

Structure:
```markdown
# Stories: [Feature Name]

> [One-line feature summary]

## Story Map

[Ordered list of all stories with status indicators]

## Stories

### Story 1: [Title]
[Full story with acceptance criteria]

### Story 2: [Title]
...

## Dependencies
[Cross-story dependencies if any]
```

If the user provides multiple features, produce a separate story document per feature.
```
