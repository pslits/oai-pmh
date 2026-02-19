```skill
---
name: story-reader
description: Read the GitHub issue (user story) linked to the current Git branch and present structured implementation context. Use when (1) starting work on a feature branch, (2) needing to understand the story or acceptance criteria for the current branch, (3) a software engineer agent needs implementation context from the originating issue, (4) checking which story a branch was created from, or (5) asked to read, fetch, or show the story for the current branch. Triggers on phrases like "read the story", "what story is this branch for", "show the issue", "get the acceptance criteria", or "what am I working on".
compatibility: Requires git CLI and the VS Code GitHub Pull Requests extension (github-pull-request_issue_fetch tool)
---

# Story Reader

## Workflow

### Step 1: Identify the current branch

Run `git branch --show-current` in the terminal to get the current branch name.

If the branch is `main`, `master`, or `develop`, inform the user there is no associated story and stop.

### Step 2: Extract the issue number

Branch names follow the pattern `{issue-number}-{description}`, where the issue number is the leading digits before the first hyphen.

Extract by capturing all digits before the first `-`:

| Branch name | Extracted issue |
|---|---|
| `59-us-00101-project-namespace-scaffolding` | `59` |
| `42-us-00203-implement-metadata-prefix` | `42` |
| `7-fix-email-validation` | `7` |

If no leading digits are found, ask the user for the issue number.

### Step 3: Determine the repository

Run `git remote get-url origin` and parse the owner and repo name from the URL.

Common formats:

| Remote URL | Owner | Repo |
|---|---|---|
| `git@github.com:pslits/oai-pmh.git` | `pslits` | `oai-pmh` |
| `https://github.com/pslits/oai-pmh.git` | `pslits` | `oai-pmh` |
| `https://github.com/pslits/oai-pmh` | `pslits` | `oai-pmh` |

Extract by capturing the two path segments before `.git` (if present) from the remote URL.

### Step 4: Fetch the issue

Call `github-pull-request_issue_fetch` with:

- `issueNumber`: the extracted number from Step 2
- `repo.owner`: the owner from Step 3
- `repo.name`: the repo name from Step 3

If the API returns an error or no result, report the failure with the attempted issue number and repository, then stop.

### Step 5: Present the story

Format the fetched issue using this template:

```markdown
## Story: {title}

**Issue:** #{issue-number}
**Author:** {author}
**Assignees:** {assignees or "unassigned"}

---

### Story Statement
{Extract the "As a … / I want … / So that …" block from the issue body.
 If no standard story statement exists, summarize the issue goal in one sentence.}

### Acceptance Criteria
{List all Given/When/Then criteria as checkboxes.
 If acceptance criteria use a different format, preserve the original format as checkboxes.
 If none are present, note "No acceptance criteria defined in the issue."}

### Notes & Constraints
{Extract dependencies, design references, technical constraints, or links.
 If none, omit this section entirely.}
```

### Step 6: Offer next steps

After presenting the story, suggest:

1. Create a todo list from the acceptance criteria
2. Begin TDD implementation following the story requirements

## Error Handling

| Condition | Action |
|---|---|
| Branch is `main`, `master`, or `develop` | Inform user there is no associated story |
| No leading digits in branch name | Ask the user for the issue number |
| GitHub API error or issue not found | Report the error with the attempted issue number |
```
