# Story Splitting Guide

When a story is too large to fit in a single sprint (or takes more than 3 days), split it. This guide provides proven splitting strategies with examples.

---

## Splitting Strategies

Use these strategies in order of preference. Pick the first one that applies.

### 1. By Workflow Steps

Split along the steps a user takes to complete a task. Each step becomes its own story.

**Before (too large):**
> As a customer, I want to check out so that I can receive my order.

**After (split):**

| # | Story | Rationale |
|---|---|---|
| 1 | User can view cart summary | First step — see what you're buying |
| 2 | User can enter shipping address | Delivery details |
| 3 | User can select shipping method | Shipping options |
| 4 | User can enter payment details | Payment step |
| 5 | User can review and confirm order | Final confirmation |
| 6 | User receives order confirmation email | Post-checkout |

Each step delivers visible progress and is independently testable.

---

### 2. By User Type / Role

Different user roles get different stories, even if the feature area is the same.

**Before (too large):**
> As a user, I want to manage subscriptions so that I can control my account.

**After (split):**

| # | Story | Role |
|---|---|---|
| 1 | Free user can view available plans | Free user |
| 2 | Subscriber can change their plan | Subscriber |
| 3 | Subscriber can cancel their subscription | Subscriber |
| 4 | Admin can manage any user's subscription | Admin |

---

### 3. By Data Variation

When the same action applies to different data types or input formats, split by variation.

**Before (too large):**
> As a user, I want to import data so that I can migrate from my old system.

**After (split):**

| # | Story | Variation |
|---|---|---|
| 1 | User can import data from CSV file | CSV |
| 2 | User can import data from Excel file | Excel |
| 3 | User can import data via API | API |

Start with the most common or simplest variation.

---

### 4. By CRUD Operations

Split create, read, update, and delete into separate stories.

**Before (too large):**
> As a user, I want to manage my profile so that my information is up to date.

**After (split):**

| # | Story | Operation |
|---|---|---|
| 1 | User can view their profile | Read |
| 2 | User can edit profile fields | Update |
| 3 | User can upload a profile photo | Create (asset) |
| 4 | User can delete their account | Delete |

---

### 5. By Happy Path vs. Edge Cases

Build the core flow first. Handle error states, edge cases, and unusual inputs in follow-up stories.

**Before (too large):**
> As a user, I want to upload a file so that I can share documents with my team.

**After (split):**

| # | Story | Scope |
|---|---|---|
| 1 | User can upload a valid document (PDF, DOCX) | Happy path |
| 2 | System validates file type before upload | Validation |
| 3 | System handles files exceeding size limit | Edge case |
| 4 | System recovers gracefully from upload failure | Error state |

Always deliver the happy path first — it's the highest-value, lowest-risk slice.

---

### 6. By Business Rule Complexity

When a single feature has multiple business rules, each rule can be a story.

**Before (too large):**
> As a pricing admin, I want to configure discount rules so that promotions apply automatically.

**After (split):**

| # | Story | Rule |
|---|---|---|
| 1 | Admin can create a flat-amount discount | Simple rule |
| 2 | Admin can create a percentage discount | Simple rule |
| 3 | Admin can set date-range validity on a discount | Time-based rule |
| 4 | Admin can set minimum-order-value threshold | Conditional rule |
| 5 | System applies the best available discount automatically | Combination rule |

---

## Anti-Patterns: Bad Splits

These splits produce tasks, not stories. Avoid them.

| Bad Split | Why It's Bad | Better Approach |
|---|---|---|
| "Design the UI" / "Build the API" / "Write tests" | Horizontal layers — no single slice delivers user value | Split vertically so each story delivers through all layers |
| "Set up the database" | Technical prerequisite — user sees nothing | Embed database work inside the first story that needs it |
| "Research library options" | Not a user story — it's a spike | Write a time-boxed spike with clear output: "Decide on charting library (time-box: 4h)" |
| "Part 1" / "Part 2" / "Part 3" | Arbitrary splits with no user logic | Re-split by workflow, user type, or data variation |

---

## Decision Flowchart

When deciding whether and how to split:

1. **Is the story > 3 days of work?** → Split it
2. **Does it have > 6 acceptance criteria?** → Split it
3. **Does the "I want" contain "and"?** → Split along the "and"
4. **Does it span multiple user roles?** → Split by user type
5. **Does the user flow have 4+ steps?** → Split by workflow step
6. **Does it handle multiple data types?** → Split by data variation
7. **Can you separate happy path from error handling?** → Split by happy path vs. edge cases

If none of these apply and the story still feels too large, try splitting by business rule complexity.
