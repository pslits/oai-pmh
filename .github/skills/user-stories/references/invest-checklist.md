# INVEST Validation Checklist

Run every user story through this checklist before it is considered sprint-ready. Each criterion must pass; flag and fix any that don't.

---

## The INVEST Criteria

### I — Independent

The story can be built and delivered without requiring another story to be completed first.

**Check:**
- [ ] No blocking dependency on another story in the same sprint
- [ ] Can be re-ordered in the backlog without breaking anything
- [ ] Team can pick this story in isolation and deliver it

**If it fails:** Identify the dependency. Either restructure the stories to remove the coupling, or explicitly document the dependency and ensure the blocking story is scheduled first.

---

### N — Negotiable

The story is a conversation starter, not a rigid contract. Implementation details are flexible.

**Check:**
- [ ] The story describes *what* and *why*, not *how*
- [ ] No specific UI element or technology is prescribed in the story statement
- [ ] The team can propose alternative implementations that still satisfy the acceptance criteria

**If it fails:** Remove implementation details from the story statement. Move them to Notes as suggestions, not requirements. Rewrite the "I want" clause to express the need, not the solution.

---

### V — Valuable

The story delivers clear, tangible value to a user or the business.

**Check:**
- [ ] The "So that" clause describes a real user benefit
- [ ] A user would recognize this as useful if it shipped alone
- [ ] The story is not a pure technical task (e.g., "refactor module X")

**If it fails:** Ask "Why does the user care?" If the answer is "they don't," the story is a technical task — either rewrite it from the user's perspective or classify it as a task under a real user story.

---

### E — Estimable

The team can estimate the effort with reasonable confidence.

**Check:**
- [ ] The story is clear enough that the team agrees on what needs to be built
- [ ] Major unknowns have been addressed or documented
- [ ] No more than one "we'd need to figure that out" in the story

**If it fails:** The story needs refinement. Identify what's unclear and either resolve it or extract a time-boxed spike story to answer the open questions first.

---

### S — Small

The story fits in a single sprint, ideally completable in 1–3 days.

**Check:**
- [ ] Estimated effort is ≤ 3 days for one developer
- [ ] Acceptance criteria count is 2–6 (not 10+)
- [ ] The "I want" clause has no "and" connecting two goals

**If it fails:** Split the story using the strategies in [splitting-guide.md](splitting-guide.md). Common splits: by workflow step, by user type, by happy-path vs. edge-case.

---

### T — Testable

The story has clear, specific acceptance criteria that can be verified objectively.

**Check:**
- [ ] Every acceptance criterion uses Given/When/Then or an equally concrete format
- [ ] No criterion uses vague language ("works correctly," "looks good," "fast")
- [ ] QA can write test cases directly from the criteria without additional context
- [ ] At least one negative/edge case criterion exists

**If it fails:** Rewrite criteria to be specific and observable. Replace "fast" with "within 2 seconds." Replace "handles errors" with a specific error scenario and expected behavior.

---

## Summary Scorecard

After validating a story, record the results:

| Criterion | Pass? | Issue | Recommended Fix |
|---|---|---|---|
| **I** — Independent | ✅ / ⚠️ | | |
| **N** — Negotiable | ✅ / ⚠️ | | |
| **V** — Valuable | ✅ / ⚠️ | | |
| **E** — Estimable | ✅ / ⚠️ | | |
| **S** — Small | ✅ / ⚠️ | | |
| **T** — Testable | ✅ / ⚠️ | | |

**Verdict:**
- All ✅: Sprint-ready
- 1–2 ⚠️: Needs minor revision — apply fixes and re-check
- 3+ ⚠️: Needs significant rework — revisit the story decomposition
