# Process Modeling Event Storming

## Purpose

Detail a specific business process from start to finish. Shift from broad exploration to focused, sequential design of a single end-to-end process. Typically follows a Big Picture session that identified a key area to zoom into.

**Team size:** 4-8 people working together sequentially (not in parallel like Big Picture).

## The Process Modeling Grammar

Unlike Big Picture, Process Modeling follows a precise, repeatable pattern:

```
🟠 Event → 🟣 Policy → 🔵 Command → 🩷 System → 🟠 Event
```

If the policy requires human intervention, insert:

```
🟣 Policy → 🟡 [Human] → 🟢 [Read Model] → 🔵 Command
```

This strict flow mirrors how real business applications operate and directly feeds into Software Design.

Even when Commands seem redundant (repeating the event phrasing), each Command will later map to a specific feature in a system.

## Facilitation Steps

### 1. Frame the Process

Clearly define:
- **Starting trigger**: What event kicks off this process?
- **End goal**: What's the desired outcome?
- **Scope**: What's in and out of bounds?

### 2. Map the Happy Path

Map the most common, successful scenario first — from trigger to goal.

Three valid starting strategies:
- **Forward**: Start from the initial trigger and work forward
- **Backward**: Start from the final outcome and work backward
- **Key events first**: Brainstorm key events, then connect them

For each step, apply the grammar: Event → Policy → Command → System → Event.

**Rules of the game** — frame it as collaborative, not competitive:
1. Complete the most important and valuable path first (the happy path)
2. Then the most important alternative path
3. Don't cover all edge cases — most value comes from just a handful of alternative flows

### 3. Mark Alternative Paths

As the happy path is mapped, any alternative flows, exceptions, or questions should be marked with 🔴 Hot Spots. Don't explore them yet — just mark them.

### 4. Explore Alternatives

Once the happy path is complete, systematically address hot spots:
- Detail the most valuable alternative paths
- The goal isn't to map every possibility — just ensure all significant scenarios are understood

### 5. Identify Read Models

For each point where a human makes a decision, ask: **"What information does this person need to see?"**

Examples:
- Before "Add to Basket": product images, features, reviews, price
- Before "Approve Invoice": payment terms, expected bills, invoice history
- Before "Schedule Interview": candidate profile, team availability, role requirements

### 6. Validate the Flow

Walk through the complete process:
- Does every event lead somewhere?
- Does every command have a clear trigger (policy or human decision)?
- Are there dead ends or circular loops?
- Are the exit conditions for conversational systems (chatbots, phone calls) modeled as outcomes rather than paths?

## Element Details

### Policies
- The glue between an Event and a subsequent Command
- Pattern: **"Whenever [Event], then [Command]"**
- Keywords: whenever, if, then, always, immediately
- Example: "Whenever an Invoice with recurring terms is received, then schedule payment immediately"

### Commands
- Represent intent to change system state
- Phrased as **verb + noun**: Place Order, Cancel Subscription, Send Email
- **Avoid vague commands**: Check, Verify, Review → rephrase or replace with Read Model
  - "Check Order" → "Mark Order as Checked" (real state change)
  - "Review Application" → Read Model showing application details + "Approve Application" or "Reject Application"

### Systems
- Receive Commands, produce Events
- Treated as black boxes
- **Conversational systems** (chatbots, phone calls): model exit conditions, not every path
  - Example exits: "Customer Satisfied", "Customer Abandoned Conversation", "Chatbot Answered"

### Read Models
- Information a user needs before acting
- Place between Policy and Command when human decision is required
- Be specific: list the actual data fields needed

## Process Modeling Output Template

```markdown
## Process: [Name]
## Trigger: 🟠 [Starting Event]
## Goal: 🟠 [End Event]

### Happy Path

1. 🟠 [Event]
   → 🟣 [Policy]: "Whenever [event], then [command]"
     → 🟡 [Person] reviews 🟢 [Read Model: data fields]
       → 🔵 [Command]
         → 🩷 [System]
           → 🟠 [Event]

2. 🟠 [Event]
   → 🟣 [Policy]: "Whenever [event], then [command]"
     → 🔵 [Command]
       → 🩷 [System]
         → 🟠 [Event]

...continues to goal...

### Alternative Path: [Name]

Branches from step [N] when [condition]:

1. 🟠 [Event from happy path]
   → 🟣 [Alternative Policy]
     → 🔵 [Command]
       → 🩷 [System]
         → 🟠 [Different Event]

### Read Models Identified

| Read Model | Used By | Data Fields | Before Command |
|---|---|---|---|
| [Name] | 🟡 [Role] | field1, field2, field3 | 🔵 [Command] |

### Hot Spots / Open Questions
1. 🔴 [Issue]
2. 🔴 [Issue]
```

## Example: Newsletter Double Opt-In

```
🟠 Newsletter Sign-Up Requested
  → 🟣 Request Policy: "Whenever sign-up requested, send confirmation email"
    → 🔵 Send Confirm Email
      → 🩷 CRM
        → 🟠 Confirm Email Sent

🟠 Confirm Email Sent
  → 🟡 User reviews 🟢 Confirm Email Request
    → 🔵 Confirm Email
      → 🩷 CRM
        → 🟠 Email Confirmed

🟠 Email Confirmed
  → 🟣 Confirmation Policy: "Whenever email confirmed, accept subscription"
    → 🔵 Accept Subscription
      → 🩷 CRM
        → 🟠 Subscription Accepted
```
