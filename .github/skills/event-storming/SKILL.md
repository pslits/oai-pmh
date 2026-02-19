```skill
---
name: event-storming
description: Facilitates Event Storming workshops for exploring complex business domains, modeling processes, and designing software architecture. Use when users want to perform event storming, map business processes with domain events, identify bounded contexts and aggregates, or collaboratively explore a domain using DDD (Domain-Driven Design) techniques. Covers Big Picture, Process Modeling, and Software Design levels.
---

# Event Storming Agent

Facilitate Event Storming sessions by guiding users through collaborative domain exploration. Event Storming (created by Alberto Brandolini) uses colored sticky notes on a timeline to map business processes, surface problems, and design software systems.

## When to Use

- User wants to explore or map a business domain or process
- User mentions "event storming", "domain events", "DDD", or "bounded contexts"
- User wants to identify problems/opportunities in a business workflow
- User is designing a software system from business processes
- User wants to break a monolith into services or define microservice boundaries

## Workflow

### Step 1: Determine the Level

Ask the user which level of Event Storming they need:

| Level | Purpose | When to Use |
|---|---|---|
| **Big Picture** | Explore a broad domain, build shared understanding, identify top problems | Scope is large or unclear; project kickoff; cross-team alignment |
| **Process Modeling** | Detail a specific business process end-to-end | Starting point and goal are clear; designing a specific workflow |
| **Software Design** | Define aggregates, bounded contexts, and system architecture | Ready to translate a process model into software components |

If unclear, default to **Big Picture** — it's the most general starting point and can feed into the other levels.

### Step 2: Gather Context

Before starting, collect:

1. **Domain**: What business domain or process are we exploring?
2. **Scope**: What's in scope and out of scope?
3. **Goal**: What outcome does the user want? (shared understanding, identify problems, design a system, etc.)
4. **As-is vs. To-be**: Are we mapping the current state or designing a future state?
5. **Stakeholders**: Who are the key roles/departments involved?

Let users dump context in any format — shorthand, bullet points, or stream-of-consciousness. Ask clarifying questions after.

### Step 3: Facilitate the Session

Load the appropriate reference file for detailed facilitation instructions:

- **Big Picture**: See [references/big-picture.md](references/big-picture.md)
- **Process Modeling**: See [references/process-modeling.md](references/process-modeling.md)
- **Software Design**: See [references/software-design.md](references/software-design.md)

For notation/color reference at any level: See [references/notation.md](references/notation.md)

### Step 4: Produce Outputs

After the session, produce structured outputs appropriate to the level:

**Big Picture outputs:**
- Timeline of domain events organized by phases/swimlanes
- List of pivotal events
- Hot spots (problems) ranked by priority
- Opportunities identified
- Recommended next steps (e.g., which area to Process Model next)

**Process Modeling outputs:**
- Complete process flow: Event → Policy → Command → System → Event chains
- Happy path documented end-to-end
- Key alternative paths
- Read models identified (information needed for decisions)
- Hot spots and open questions

**Software Design outputs:**
- Bounded contexts identified with boundaries justified
- Aggregates defined with their commands and events
- External systems vs. internal components distinguished
- API surface per aggregate (commands in, events out)

## Output Format

Present Event Storming results using structured Markdown. Use color indicators to match traditional sticky note colors:

- 🟠 **Domain Event** — something that happened (past tense, e.g., "Order Placed")
- 🔵 **Command** — an action/intent (imperative, e.g., "Place Order")
- 🟣 **Policy** — a business rule ("Whenever X, then Y")
- 🟢 **Read Model** — information needed to make a decision
- 🩷 **System** — external or internal service (black box)
- 🟡 **Person/Role** — a human actor
- 🔴 **Hot Spot** — a problem, question, or concern
- 💚 **Opportunity** — an idea or improvement
- 🟨 **Aggregate** — a software component we own and design (Software Design level)

### Example: Process Flow Rendering

```
🟠 Invoice Received
  → 🟣 Recurring Payment Policy: "Whenever invoice with recurring terms, schedule immediately"
    → 🟡 Accountant reviews 🟢 Payment Terms + Expected Bills
      → 🔵 Schedule Payment
        → 🩷 Payment System
          → 🟠 Payment Scheduled

  → 🟣 Unusual Payment Policy: "Whenever invoice with unusual terms, request review"
    → 🔵 Ask for Review
      → 🩷 Email System
        → 🟠 Review Requested
```

## Facilitation Principles

- **Start messy, refine later** — chaos is expected in early phases
- **Disagreement is valuable** — it surfaces hidden assumptions
- **Use past tense for events** — "Order Placed" not "Place Order"
- **Use imperative for commands** — "Place Order" not "Order Placed"
- **Avoid vague commands** — "Check Order" → rephrase as "Mark Order as Checked" or add a Read Model
- **Policies use "Whenever... then..."** pattern
- **Timebox exploration** — move forward even with gaps; mark hot spots for later
- **Pivotal events** anchor the timeline — don't overthink which ones to pick
```
