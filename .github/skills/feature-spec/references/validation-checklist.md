# Feature Spec Validation Checklist

Use this checklist to validate a feature specification before it is considered complete. Walk through each category and check every item. Flag anything incomplete or unclear.

## User Value & Problem

- [ ] A specific, observable user problem is identified (not an assumption)
- [ ] Target users/personas are named with their roles
- [ ] The pain is quantified (time, money, errors, support tickets)
- [ ] The proposed value is measurable, not vague ("improves experience")
- [ ] Success metrics have baselines and targets
- [ ] The feature delivers one clear value (not bundling multiple values)

## Clarity & Readability

- [ ] A non-technical stakeholder can understand the problem and value sections
- [ ] Technical terms are defined on first use
- [ ] Active voice is used throughout
- [ ] The user flow is described step-by-step from the user's perspective
- [ ] No jargon without explanation; no acronyms without expansion
- [ ] Headings and structure make the document scannable

## Acceptance Criteria

- [ ] Each criterion is independently testable
- [ ] Happy path is fully covered
- [ ] At least 2 edge cases are identified
- [ ] At least 1 error state is specified with expected recovery behavior
- [ ] Performance thresholds are stated where relevant (response time, load)
- [ ] Negative cases are included (what should NOT happen)
- [ ] QA could write test cases directly from the criteria without additional context

## Scope & Boundaries

- [ ] In-scope items are explicitly listed
- [ ] Out-of-scope items are explicitly listed with rationale
- [ ] The feature is a deliverable unit, not a broad capability
- [ ] Future considerations are noted but clearly deferred
- [ ] Scope is tight enough that two developers would build roughly the same thing

## Technical Requirements

- [ ] Required capabilities are listed (what the system must do)
- [ ] Performance requirements have specific targets
- [ ] Security requirements are addressed (auth, data protection, compliance)
- [ ] Dependencies on other features/services/systems are identified
- [ ] Feasibility has been considered (no impossible requirements)
- [ ] Architecture alignment has been checked (follows existing patterns)

## Business Context

- [ ] Development cost is estimated (even rough T-shirt sizing)
- [ ] Key risks are identified with mitigations
- [ ] The feature aligns with stated business goals or product strategy
- [ ] Timeline expectations are noted
- [ ] Maintenance and operational costs are considered

## Documentation & Completeness

- [ ] All template sections are filled in (or marked N/A with rationale)
- [ ] The spec is self-contained — no critical information lives only in someone's head
- [ ] Diagrams or flow descriptions exist for complex interactions
- [ ] The spec has been reviewed by at least one person other than the author
- [ ] A rollback or reversal strategy exists for the feature

## Scoring

After completing the checklist, tally the results:

| Category | Checked | Total | Status |
|---|---|---|---|
| User Value & Problem | _ | 6 | |
| Clarity & Readability | _ | 6 | |
| Acceptance Criteria | _ | 7 | |
| Scope & Boundaries | _ | 5 | |
| Technical Requirements | _ | 6 | |
| Business Context | _ | 5 | |
| Documentation & Completeness | _ | 5 | |
| **Total** | _ | **40** | |

**Rating:**
- 36-40: Ready for development
- 28-35: Needs minor revisions — address flagged items
- 20-27: Significant gaps — revisit context gathering
- Below 20: Not ready — restart from problem discovery
