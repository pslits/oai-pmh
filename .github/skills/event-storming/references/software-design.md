# Software Design Event Storming

## Purpose

Translate a Process Model into software architecture by identifying Bounded Contexts and Aggregates. This is where DDD (Domain-Driven Design) meets Event Storming.

**Two key goals:**
1. **Bounded Contexts** — high-level system boundaries (often identified during Big Picture)
2. **Aggregates** — internal components within a Bounded Context (defined here)

## Prerequisites

A completed Process Model (or at minimum a well-understood process flow with Events, Commands, Policies, and Systems identified).

## Facilitation Steps

### 1. Identify Bounded Contexts

Bounded Contexts are self-contained subsystems, each designed for a specific domain or team.

**How to find boundaries:**
- **Linguistic borders**: Different departments use different words for similar things. These mismatches signal where one context ends and another begins.
- **Organizational borders**: Different teams with different goals and expertise.
- **Pivotal events from Big Picture**: Transitions between pivotal events often align with context boundaries.

**Key principle**: Don't force everyone into a single unified model. Let each team work within its own Bounded Context — a system optimized for their language, goals, and expertise.

Each Bounded Context becomes a purpose-built tool for its domain experts — clear, focused, and free from cross-departmental compromises.

### 2. Transform Systems to Aggregates

For each Bounded Context, review the process model and:

- **Features you'll build**: Replace 🩷 System (pink) with 🟨 Aggregate (yellow)
- **External systems/tools**: Keep as 🩷 System (pink) — they stay as black boxes

This signals the crucial shift:
- 🩷 Pink Systems = external black boxes you interact with
- 🟨 Yellow Aggregates = internal white boxes you design and own

### 3. Define Aggregate Boundaries

For each Aggregate, collect:

1. **All 🔵 Commands it handles** (input — place on one side)
2. **All 🟠 Events it emits** (output — place on the other side)

This layout defines the Aggregate's **public API**: what it accepts (Commands) and what it produces (Events).

### 4. Validate Aggregate Completeness

Review each Aggregate for missing lifecycle steps:

- Does it have a creation command? (e.g., "Create Order")
- Does it have update commands? (e.g., "Update Shipping Address")
- Does it have a completion/termination command? (e.g., "Mark as Checked Out")
- Are all state transitions represented?

**Common gap**: You see "Update Shipping Address" but no "Create Order" — you've missed the starting point.

### 5. Review Cross-Context Communication

How do Bounded Contexts talk to each other?
- Through **Domain Events** — one context emits an event, another reacts via a Policy
- Identify which events cross boundaries
- These become integration points (event bus, message queue, API calls)

## Software Design Output Template

```markdown
## System: [Name]
## Based on Process: [Process Model name]

### Bounded Contexts

#### Context: [Name]
- **Domain**: [What this context is responsible for]
- **Team**: [Who owns this]
- **Ubiquitous Language**: [Key terms specific to this context]

##### Aggregates

###### 🟨 [Aggregate Name]

**Commands (in):**
- 🔵 [Command 1]
- 🔵 [Command 2]
- 🔵 [Command 3]

**Events (out):**
- 🟠 [Event 1]
- 🟠 [Event 2]
- 🟠 [Event 3]

**Lifecycle:**
1. Created by: 🔵 [Command] → 🟠 [Event]
2. Updated by: 🔵 [Command] → 🟠 [Event]
3. Completed by: 🔵 [Command] → 🟠 [Event]

##### External Systems
- 🩷 [System Name] — [what it does, why it's external]

#### Context: [Name]
...

### Cross-Context Integration

| Source Context | Event | Target Context | Policy | Command |
|---|---|---|---|---|
| [Context A] | 🟠 [Event] | [Context B] | 🟣 [Policy] | 🔵 [Command] |

### Architecture Notes
- [Key design decisions]
- [Trade-offs considered]
```

## Example: Shopping Cart Aggregate

```
🟨 Shopping Cart

Commands (in):              Events (out):
🔵 Create Empty Cart    →  🟠 Cart Created
🔵 Add Item             →  🟠 Item Added
🔵 Remove Item          →  🟠 Item Removed
🔵 Update Quantity      →  🟠 Quantity Updated
🔵 Add Customer         →  🟠 Customer Added
🔵 Mark as Checked Out  →  🟠 Cart Checked Out
```

## Tips

- **Start with one Bounded Context** — don't try to design the entire system at once
- **Aggregates should be small** — if an aggregate handles too many commands, it may need splitting
- **Events crossing boundaries = integration contracts** — treat them with extra care
- **Ubiquitous Language matters** — each context gets its own vocabulary; don't force shared terms
- **External vs. internal is a strategic choice** — sometimes you build, sometimes you buy/integrate
