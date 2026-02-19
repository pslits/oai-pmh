```skill
---
name: feature-spec
description: Write clear, actionable software feature specifications. Use when users want to specify a feature, write a feature spec, document a software feature, create a feature definition, draft acceptance criteria, or describe what a feature should do. Helps transform vague ideas into structured, user-centric feature documents with measurable success criteria and testable acceptance conditions.
---

# Feature Specification Writer

Guide users through writing feature specifications that are user-centric, measurable, and implementation-ready. A feature is a single, deliverable unit of functionality that solves a specific user problem — not a broad capability like "Project Management."

## Workflow

### Step 1: Gather Context

Collect essential information before drafting. Ask the user:

1. **What problem does this feature solve?** — Concrete user pain point, not a technical wish
2. **Who experiences this problem?** — Specific user roles/personas affected
3. **How painful is it today?** — Quantify: time wasted, errors caused, workarounds used
4. **What does success look like?** — Measurable outcome (e.g., "reduces setup time by 50%")
5. **What's the scope boundary?** — What this feature is and is NOT

Accept answers in any format — shorthand, bullet points, stream-of-consciousness. Ask clarifying questions after the initial dump.

**If the user describes something too broad** (e.g., "user management," "reporting"), it's likely a capability, not a feature. Help decompose it:
- "That sounds like a capability with multiple features inside it. Let's break it down — what's the single most valuable piece we could deliver first?"
- Guide them to isolate one clear value per feature.

**If the user starts with technical details** (database schemas, API design), redirect:
- "Let's back up — what user problem does this solve? We'll get to the technical approach once we've nailed the user value."

### Step 2: Draft the Feature Spec

Use the template in [references/template.md](references/template.md). Fill in each section based on gathered context.

**Writing principles:**

- **User-first language** — Describe what users can do, not how the system works internally
- **Active voice** — "Users can browse templates" not "Templates are browsable by users"
- **Concrete over abstract** — "Saves 2 hours per project setup" not "Improves efficiency"
- **One feature = one clear value** — If the spec tries to deliver multiple values, split it
- **Past tense for events, imperative for actions** — "Order was placed" vs. "Place order"

**For each section, apply these readability rules:**

- Use clear headings and subheadings to create scannable structure
- Define technical terms on first use
- Include diagrams or flow descriptions for complex interactions
- Use tables for comparisons, options, or mappings
- Highlight critical warnings or constraints with callouts (⚠️, 💡)

### Step 3: Write Acceptance Criteria

Acceptance criteria define when the feature is done. Write them using Given/When/Then format or as concrete checklist items.

**Rules for good acceptance criteria:**
- Each criterion is independently testable
- Cover the happy path first, then edge cases
- Include negative cases (what should NOT happen)
- Specify performance thresholds if relevant
- Address error states and recovery

**Example:**
```
- [ ] Given a user on the "New Project" page, when they click "Browse Templates," then a gallery of available templates is displayed
- [ ] Given a user viewing a template, when they click "Preview," then a full preview renders within 2 seconds
- [ ] Given a user selects a template, when they click "Use Template," then all project fields are populated from the template
- [ ] Given no templates exist, when a user opens the gallery, then a helpful empty state with a "Create Template" link is shown
```

### Step 4: Validate the Spec

Run through the validation checklist in [references/validation-checklist.md](references/validation-checklist.md). For each category, flag items that are incomplete or unclear.

Present a summary:
- ✅ Passing items (brief)
- ⚠️ Items needing attention (with specific recommendations)

### Step 5: Refine

Walk through flagged items with the user. For each:
1. Explain why it matters
2. Propose a specific fix
3. Apply the fix once confirmed

Repeat validation after significant changes.

## Common Pitfalls to Catch

| Pitfall | Symptom | Fix |
|---|---|---|
| **Capability Confusion** | Spec describes a broad area, not a deliverable unit | Decompose into individual features with one value each |
| **Technical Trap** | Spec leads with database schemas or API design | Redirect to user problem and value; technical details go in requirements |
| **User Blindspot** | No real user pain point identified; assumptions instead of evidence | Ask: "Have you seen users struggle with this? How?" |
| **Vague Success** | "Make it better" or "Improve the experience" | Demand a number: time saved, errors reduced, adoption rate |
| **Kitchen Sink** | Too many acceptance criteria covering unrelated behaviors | Split into separate features; each spec should have 4-10 focused criteria |
| **Missing Boundaries** | No out-of-scope section; scope creep inevitable | Explicitly list what the feature does NOT do |

## Output Format

Produce the feature spec as a Markdown document following the template structure. Use the filename format `feature-<short-name>.md` (e.g., `feature-template-gallery.md`).

If the user wants multiple features specified, produce each as a separate document and create an index listing all features with one-line descriptions.
```
