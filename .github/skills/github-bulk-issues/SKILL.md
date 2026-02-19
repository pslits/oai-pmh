---
name: github-bulk-issues
description: >
  Creates GitHub issues in bulk from a JSON data file, labels them, adds them to a
  GitHub Projects v2 board, and cross-links dependency relations in issue bodies.
  Use this skill when asked to create multiple GitHub issues from feature specs,
  planning documents, or any structured list — especially when issues need labels,
  project assignment, and dependency/blocking relationships between them.
---

# GitHub Bulk Issue Creator

Creates labelled GitHub issues with dependency links and adds them to a GitHub Projects v2
board. Uses only PowerShell + the GitHub REST and GraphQL APIs — no `gh` CLI required.

## Workflow

### Step 1 — Prepare the data file

Create `features-data.json` (see `references/data-schema.md` for the schema).  
Each object needs: `id`, `title`, `description`, `blockedBy[]`, `blocks[]`, plus any
metadata fields you want rendered in the issue body (priority, phase, context, etc.).

### Step 2 — Run the script

```powershell
powershell -ExecutionPolicy Bypass -File .github\skills\github-bulk-issues\scripts\create-github-issues.ps1 `
    -Repo "owner/repo" -Label "feature" -ProjectName "My Project"
```

The script (see `scripts/create-github-issues.ps1`):
1. Retrieves the GitHub token from the Windows credential manager automatically.
2. Creates or verifies the target label.
3. Finds or creates the GitHub Projects v2 board by name.
4. Creates every issue with the label applied.
5. Re-patches every issue body to replace `F-xxx` dependency IDs with live `#n (F-xxx)` links (two-pass strategy).
6. Adds every issue to the project board via GraphQL.
7. Saves `issue-map.json` (featureId → issue number) for post-run auditing.

### Step 3 — (Optional) Replace issue bodies with full source file content

If each issue has a corresponding source file (e.g., a feature spec markdown), update all
issue bodies with the complete file text:

```powershell
powershell -ExecutionPolicy Bypass -File .github\skills\github-bulk-issues\scripts\update-issues-full-content.ps1 `
    -Repo "owner/repo" -SourceDir "docs\features" -IssueMapFile "bin\issue-map.json"
```

### Step 4 — Clean up temp files

```powershell
Remove-Item bin\features-data.json, bin\issue-map.json -Force
```

---

## Pitfalls & Lessons Learned

All 10 lessons (PowerShell parse errors, API quirks, terminal output reuse, etc.) are
documented in detail at:

**[docs/GITHUB_BULK_ISSUES_LEARNINGS.md](../../../../docs/GITHUB_BULK_ISSUES_LEARNINGS.md)**

Quick-reference table:

| # | Root cause | Fix |
|---|------------|-----|
| 1 | Em-dash / `&` in PS string | Use JSON data file |
| 2 | `$var:` parsed as PS drive | Rename var or restructure string |
| 3 | Here-string inside `@()` | Use string concatenation |
| 4 | `gh` CLI not installed | Use `git credential-manager get` |
| 5 | Projects v2 is GraphQL-only | Use GraphQL mutations |
| 6 | Missing `-Compress` / charset | Add `-Compress` and `charset=utf-8` |
| 7 | Too many rapid API calls | Add `Start-Sleep` between calls |
| 8 | `Get-Content -Raw` returns PSObject | Use `File::ReadAllText()` |
| 9 | `$PSScriptRoot` null with `-File` | Use `$MyInvocation` or absolute paths |
| 10 | Background terminal buffer reuse | Capture to file or query API directly |

---

## Two-pass approach for dependency links

Because issue numbers are not known until after creation, use a two-pass strategy:

1. **Pass 1 (create):** Render `blockedBy`/`blocks` as plain `F-xxx` IDs.
2. **Pass 2 (update):** After all issues exist, re-render bodies replacing `F-xxx` with
   `#n (F-xxx)` using the `$issueMap` hashtable, then `PATCH` each issue.

This avoids complex ordering logic and keeps the script simple.
