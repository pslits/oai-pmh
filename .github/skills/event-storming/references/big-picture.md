# Big Picture Event Storming

## Purpose

Maximize learning by collaboratively exploring a business domain. Create shared understanding across departments and agree on which problems are most critical to solve.

**Good for:** project kickoffs, startup kickoffs, organization reboots, onboarding new team members, retrospectives.

## Facilitation Steps

### 1. Frame the Problem

Confirm Big Picture is the right method:
- What problem are we solving? What's the scope? What outcome is expected?
- If exploring the whole business/organization → Big Picture is the right fit
- If focusing on a specific process → consider Process Modeling instead (though a limited Big Picture first may help)
- Decide: mapping **as-is** state or envisioning a **to-be** future?

### 2. Chaotic Exploration (~30 min)

Ask participants to brainstorm Domain Events (🟠 orange) and place them — all at the same time, in no particular order.

**Prompting techniques when people get stuck:**
- "Do you receive payments at some point?"
- "Are contracts signed at some point?"
- "What happens at the end of the month?"
- "What triggers work in your department?"
- "What do you hand off to other teams?"

This phase will feel chaotic — that's perfectly fine. Structure comes later. Don't worry about capturing every event; missing items can be added later.

**In a text-based session:** Ask the user to list as many domain events as they can think of, in any order. Accept shorthand. Encourage quantity over quality at this stage.

### 3. Enforce the Timeline

Arrange all Domain Events on a single timeline from left to right. This is where real collaboration begins.

**Sorting strategies to suggest when it gets messy:**
- **Pivotal Events** — identify anchor points in the flow and arrange everything relative to them
- **Swimlanes** — separate parallel activities (e.g., by department or role)
- **Temporal Milestones** — time-based anchors ("end of month", "quarterly review")
- **Chapter Sorting** — group events by themes or phases
- **Combinations** — mix strategies as needed

When disagreements arise, mark a 🔴 Hot Spot and move on. Revisit all open issues together later.

### 4. Identify Pivotal Events

Mark key pivotal events that:
- Are especially relevant to the business
- Mark transitions between phases
- Help distribute the flow and create structure

These become anchors. All other events are positioned relative to them. Don't overthink the selection — adjust later as the structure evolves.

**Pivotal events also suggest candidate system boundaries** — where one bounded context may end and another begins.

### 5. Add People & Systems

Identify key roles (🟡 Person) and systems (🩷 System) involved in the flow:
- Place them where they play a meaningful role
- This step often surfaces many Hot Spots

Skip this step if the issues are already clear, the system doesn't exist yet, or the information is already well-known. Sometimes highlighting only external dependencies is enough.

### 6. Explicit Walkthrough

Ask participants to narrate the timeline in order:
- Each person walks along the timeline and explains the chain of events
- Others challenge unclear parts, fill gaps, suggest improvements
- Rearrange events, add missing ones, place Hot Spots and Opportunities
- Keep narrator turns short (~5-10 minutes each)

This is usually the most time-consuming step — a few hours is normal.

### 7. Reverse Narrative

Do a final pass from **end to start**:
- "For an invoice to be paid, it must first be sent" → "Invoice Sent" should appear before "Invoice Paid"
- This reveals missing events surprisingly often
- At the end, ask: "Is anything still missing?"

### 8. Problems & Opportunities (10-15 min)

Dedicated time to surface lingering issues:
- 🔴 **Hot Spots** for problems you're aware of but don't yet know how to solve
- 💚 **Opportunities** where a solution is already in mind

### 9. Arrow Voting — Pick the Right Problem

Give each participant 2-3 votes. Frame it as:

> "Everything we've captured is important, but now pick the two most urgent problems or opportunities we should focus on."

Voting only makes sense once everything is visible. Usually a few top issues stand out clearly.

### 10. Wrap Up

Possible outcomes:
- **Shared understanding achieved** + top problems identified → act on top 2-3 issues
- **Need more detail** → continue with Process Modeling on the highest-priority area
- **Ready for system design** → proceed to Software Design Event Storming

Document the results: timeline of events, pivotal events, hot spots (ranked), opportunities, and agreed next steps.

## Big Picture Output Template

```markdown
## Domain: [Name]
## Scope: [What's included/excluded]
## Type: [As-is / To-be]

### Pivotal Events
1. 🟠 [Event Name] — [Why it's pivotal]
2. 🟠 [Event Name] — [Why it's pivotal]

### Timeline by Phase

#### Phase 1: [Name]
- 🟠 [Event]
- 🟠 [Event]
- 🟡 [Person/Role involved]
- 🩷 [System involved]

#### Phase 2: [Name]
...

### Hot Spots (Ranked by Priority)
1. 🔴 [Problem] — [votes/severity]
2. 🔴 [Problem] — [votes/severity]

### Opportunities
1. 💚 [Opportunity] — [potential impact]
2. 💚 [Opportunity] — [potential impact]

### Recommended Next Steps
- [ ] [Action item]
- [ ] [Action item]
```
