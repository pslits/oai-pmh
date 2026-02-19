# Event Storming Notation Reference

## Color Legend (All Levels)

| Color | Element | Used In | Description |
|---|---|---|---|
| 🟠 Orange | **Domain Event** | All levels | Something that happened, relevant to the business. Always past tense. |
| 🔴 Red/Magenta | **Hot Spot** | All levels | A problem, question, concern, or blocker needing attention. |
| 💚 Light Green | **Opportunity** | All levels | A suggestion or idea that could benefit the business. |
| 🟡 Yellow (small) | **Person/Role** | Big Picture + | A person playing a role in the event flow (e.g., Customer, CFO). |
| 🩷 Pink (large) | **System** | Big Picture + | An external or internal service treated as a black box (e.g., Email, CRM). |
| 🟣 Purple/Lilac | **Policy** | Process Modeling + | A business rule reacting to an event. "Whenever [Event], then [Command]." |
| 🟢 Green | **Read Model** | Process Modeling + | Information needed to make a decision (e.g., price, stock, reviews). |
| 🔵 Blue | **Command** | Process Modeling + | An instruction or action. Imperative form: verb + noun (e.g., "Place Order"). |
| 🟨 Yellow (large) | **Aggregate** | Software Design | A software component we design and own (white box). Replaces System for internal components. |
| ⬜ White | **UI Wireframe** | Software Design | When a Read Model isn't enough; shows interface layout and interactions. |
| ⬜ White | **Bounded Context** | Software Design | A self-contained subsystem designed for a specific domain or team. |

## Level Progression

Each level builds on the previous:

```
Big Picture:        Events + Hot Spots + Opportunities + People + Systems
Process Modeling:   + Policies + Commands + Read Models
Software Design:    + Aggregates + UI Wireframes + Bounded Contexts
```

## Grammar Rules

### Domain Events (All Levels)
- Always use **past tense**: "Order Placed", "Invoice Sent", "User Registered"
- Should be meaningful to domain experts, not just technical events
- Place along a left-to-right **timeline**

### Commands (Process Modeling+)
- Always use **imperative form**: "Place Order", "Send Invoice", "Register User"
- Must represent a real state change — avoid vague verbs like "Check", "Verify", "Review"
  - If it's about retrieving information → use a Read Model instead
  - If it's a real state change → rephrase clearly (e.g., "Check Order" → "Mark Order as Checked")

### Policies (Process Modeling+)
- Use **"Whenever [Event], then [Command]"** pattern
- Keywords: whenever, if, then, always, immediately
- Policies are the glue between Events and Commands
- A policy can trigger human intervention: Policy → [Human] → [Read Model] → Command

### Read Models (Process Modeling+)
- Represent the **information a user sees** before making a decision
- Examples: product images, price, stock availability, reviews, payment terms

### Systems (All Levels)
- Treated as **black boxes** — they receive Commands and produce Events
- For conversational systems (chatbots, phone calls): model by **exit conditions**, not every path

### Aggregates (Software Design)
- Replace Systems for components you **own and build** (white boxes)
- External systems stay pink; internal components become yellow Aggregates
- Each Aggregate has: Commands it handles (in) + Events it emits (out)
