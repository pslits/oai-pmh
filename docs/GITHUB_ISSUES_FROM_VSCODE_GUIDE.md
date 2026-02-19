# Creating GitHub Issues from VS Code — Complete Guide

**Version:** 1.0  
**Date:** February 19, 2026  
**Author:** Paul Slits  
**Applies to:** Any project using VS Code + PowerShell 5.1 on Windows  
**Tested on:** `pslits/oai-pmh` — 39 issues, labels, Projects v2 board, dependency links

---

## Table of Contents

1. [Overview](#1-overview)
2. [Prerequisites](#2-prerequisites)
3. [Tooling Architecture](#3-tooling-architecture)
4. [File Structure](#4-file-structure)
5. [Step 1 — Prepare the Data File](#5-step-1--prepare-the-data-file)
6. [Step 2 — Run the Bulk Create Script](#6-step-2--run-the-bulk-create-script)
7. [Step 3 — (Optional) Replace Bodies with Full Source Files](#7-step-3--optional-replace-bodies-with-full-source-files)
8. [Step 4 — Clean Up Temp Files](#8-step-4--clean-up-temp-files)
9. [Two-Pass Strategy for Dependency Links](#9-two-pass-strategy-for-dependency-links)
10. [GitHub API Patterns](#10-github-api-patterns)
11. [Common Pitfalls and Fixes](#11-common-pitfalls-and-fixes)
12. [Running from the VS Code Terminal](#12-running-from-the-vs-code-terminal)
13. [Validating Results](#13-validating-results)
14. [Quick-Reference Cheat Sheet](#14-quick-reference-cheat-sheet)

---

## 1. Overview

This guide explains how to create GitHub issues **in bulk** from VS Code using PowerShell
scripts and the GitHub REST/GraphQL APIs. No `gh` CLI is required. The approach supports:

- Creating dozens (or hundreds) of labelled issues from a structured JSON file
- Automatically assigning issues to a **GitHub Projects v2** board
- Cross-linking **dependency relationships** between issues (`blocked by` / `blocks`)
- Optionally replacing issue bodies with full markdown source files

The workflow is built around two reusable scripts in
`.github/skills/github-bulk-issues/scripts/`.

---

## 2. Prerequisites

### 2.1 Local Tools

| Tool | Requirement | Notes |
|------|-------------|-------|
| VS Code | Any recent version | Terminal used for all commands |
| PowerShell | 5.1+ (Windows built-in) | Already available on Windows |
| Git | Any recent version | Must be authenticated to GitHub |
| Git Credential Manager | Bundled with Git for Windows | Stores the GitHub token |

> No `gh` CLI needed. No personal access tokens (PATs) to manually manage.
> The scripts retrieve the token stored by Git Credential Manager automatically.

### 2.2 GitHub Authentication

The scripts retrieve your GitHub token from the Windows credential store via Git Credential
Manager. This works automatically if you have ever authenticated with GitHub through Git
(e.g., via `git clone` or `git push` from a private repo):

```powershell
# Test that the token retrieval works
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
if ($Token) { Write-Host "Token found: OK" } else { Write-Host "No token — re-authenticate with Git" }
```

If no token is found, run any authenticated Git operation (e.g., `git fetch`) in VS Code
to trigger the credential store.

### 2.3 Repository Permissions

Your GitHub account must have:

- **Write** access to the repository (to create issues and labels)
- **Project admin** access if you want to create new Projects v2 boards

---

## 3. Tooling Architecture

```
.github/skills/github-bulk-issues/
├── SKILL.md                              # Skill entry point (summary + workflow)
├── references/
│   └── data-schema.md                   # JSON input schema documentation
└── scripts/
    ├── create-github-issues.ps1          # Main script: create + link + add to project
    └── update-issues-full-content.ps1   # Optional: replace bodies with source files
```

The scripts use only two GitHub APIs:

| API | Purpose |
|-----|---------|
| **REST API v3** | Create issues, create/check labels, patch issue bodies |
| **GraphQL API v4** | Find/create Projects v2 boards, add issues to a board |

---

## 4. File Structure

Before running, set up your data and source files:

```
project root/
├── .github/
│   └── skills/
│       └── github-bulk-issues/
│           └── scripts/
│               ├── create-github-issues.ps1
│               └── update-issues-full-content.ps1
├── bin/                                  # Generated: issue-map.json goes here
│   └── features-data.json               # Your input file (created by you)
└── docs/
    └── features/                         # Optional: source markdown files per issue
        ├── F-001_PROJECT_FOUNDATION.md
        ├── F-002_CONFIGURATION.md
        └── ...
```

---

## 5. Step 1 — Prepare the Data File

Create `bin/features-data.json`. Each object in the array represents one GitHub issue.

### 5.1 Required and Optional Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ✅ | Unique feature ID, e.g. `"F-001"`. Key in the issue map. |
| `title` | string | ✅ | Issue title as it appears on GitHub. |
| `description` | string | ✅ | Body text for the Description section. |
| `blockedBy` | string[] | ✅ | IDs of features this one depends on. Use `[]` if none. |
| `blocks` | string[] | ✅ | IDs of features that depend on this one. Use `[]` if none. |
| `priority` | string | optional | e.g. `"MVP"`, `"High"`, `"Low"` |
| `phase` | string | optional | e.g. `"Phase 1 - Configuration"` |
| `context` | string | optional | Bounded context or domain area |

Optional metadata fields are automatically rendered as **Key:** value lines at the top of
each issue body.

### 5.2 Minimal JSON Example

```json
[
  {
    "id": "F-001",
    "title": "F-001: Project Foundation and Shared Contracts",
    "priority": "MVP",
    "phase": "Phase 0 - Foundation",
    "context": "Cross-cutting",
    "description": "Establish the project skeleton and shared PHP interfaces.",
    "blockedBy": [],
    "blocks": ["F-002", "F-003"]
  },
  {
    "id": "F-002",
    "title": "F-002: Configuration Management",
    "priority": "MVP",
    "phase": "Phase 1 - Configuration",
    "context": "Configuration",
    "description": "Implement the Configuration aggregate.",
    "blockedBy": ["F-001"],
    "blocks": []
  }
]
```

### 5.3 String Safety Rules

PowerShell parses the data file content differently from a plain JSON reader. Follow these
rules to avoid parse errors:

| Character | Problem | Safe Alternative |
|-----------|---------|-----------------|
| `—` (em-dash, U+2014) | `Unexpected token` parse error | Replace with `-` |
| `&` | `The ampersand character is not allowed` | Replace with `and` |
| `` ` `` | PowerShell escape character | Avoid in text values |
| `\n` inside JSON string value | Literal backslash-n | Write normal text; the script handles newlines |

> **Tip:** Validate your JSON before running. Paste it into
> [jsonlint.com](https://jsonlint.com) or run:
> ```powershell
> Get-Content bin\features-data.json -Raw | ConvertFrom-Json | Measure-Object
> ```

---

## 6. Step 2 — Run the Bulk Create Script

### 6.1 Script Parameters

| Parameter | Required | Default | Description |
|-----------|----------|---------|-------------|
| `-Repo` | ✅ | — | `"owner/repo"` format |
| `-Label` | ✅ | — | Label name to apply to all issues. Created if missing. |
| `-ProjectName` | ✅ | — | Projects v2 board name. Created if missing. |
| `-DataFile` | optional | `features-data.json` next to the script | Path to data file |

### 6.2 Open the VS Code Terminal

Use the integrated terminal (`Ctrl+`` ` `` `) or **Terminal → New Terminal**. Make sure
it uses PowerShell (not CMD or Git Bash):

```powershell
# Verify you're in PowerShell
$PSVersionTable.PSVersion
```

### 6.3 Run the Script

From the repository root:

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues\scripts\create-github-issues.ps1 `
    -Repo        "owner/repo" `
    -Label       "feature" `
    -ProjectName "My Board" `
    -DataFile    "bin\features-data.json"
```

Replace `owner/repo`, `"feature"`, and `"My Board"` with your actual values.

### 6.4 What the Script Does (Three Passes)

```
Pass 1 — Create issues
  ✔ Verify/create label
  ✔ Find/create Projects v2 board
  ✔ POST each issue with plain F-xxx dependency IDs
  ✔ Save bin/issue-map.json (F-xxx → issue number)

Pass 2 — Patch dependency links
  ✔ Re-PATCH every issue body
  ✔ Replace "F-002" → "#21 (F-002)" using issueMap

Pass 3 — Add to project board
  ✔ Get each issue's GraphQL node ID
  ✔ addProjectV2ItemById for each issue
```

### 6.5 Expected Console Output

```
Loaded 39 features from bin\features-data.json

=== Label 'feature' ===
  Already exists

=== Project 'OAI Repository' ===
  Found (number: 1)

=== Creating 39 issues ===
  [OK] #20 - F-001: Project Foundation and Shared Contracts
  [OK] #21 - F-002: Configuration Management
  ...

Issue map saved to bin\issue-map.json

=== Updating dependency links ===
  [OK] Updated #20 (F-001)
  [OK] Updated #21 (F-002)
  ...

=== Adding to project 'OAI Repository' ===
  [OK] Added #20 (F-001)
  [OK] Added #21 (F-002)
  ...

=== DONE — 39 issues created ===
```

---

## 7. Step 3 — (Optional) Replace Bodies with Full Source Files

If you have detailed markdown files for each issue (e.g., feature specifications), you can
replace the short description with the full file content.

### 7.1 File Naming Convention

Files must be named so that the feature ID can be extracted by regex. The default pattern
matches `F-001` at the start of the filename:

```
docs/features/F-001_PROJECT_FOUNDATION.md
docs/features/F-002_CONFIGURATION.md
```

### 7.2 Run the Update Script

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues\scripts\update-issues-full-content.ps1 `
    -Repo         "owner/repo" `
    -SourceDir    "docs\features" `
    -IssueMapFile "bin\issue-map.json"
```

### 7.3 Parameters

| Parameter | Required | Default | Description |
|-----------|----------|---------|-------------|
| `-Repo` | ✅ | — | `"owner/repo"` |
| `-SourceDir` | ✅ | — | Directory with source markdown files |
| `-FilePattern` | optional | `"*.md"` | Glob pattern for source files |
| `-IdPattern` | optional | `"^(F-\d+)"` | Regex to extract ID from filename |
| `-IssueMapFile` | optional | `issue-map.json` next to script | Path to the issue map |

### 7.4 Important: Use `File::ReadAllText()`, Not `Get-Content -Raw`

This is a critical pitfall (see [Lesson 8](#lesson-8--get-content--raw-returns-a-psobject-not-a-plain-string)).
The script already uses the correct method internally. If you write any variation of this
step yourself, always use:

```powershell
# CORRECT
$body = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)

# WRONG — returns PSObject with metadata; GitHub API returns HTTP 422
$body = Get-Content $file.FullName -Raw -Encoding utf8
```

---

## 8. Step 4 — Clean Up Temp Files

After verifying the results on GitHub, remove the temporary files:

```powershell
Remove-Item bin\features-data.json, bin\issue-map.json -Force
```

Or keep `issue-map.json` for auditing purposes — it maps each feature ID to its GitHub
issue number and is useful for future cross-referencing.

---

## 9. Two-Pass Strategy for Dependency Links

Issue numbers are not known until after creation. This makes it impossible to write correct
`#21 (F-002)` links in a single pass.

### Solution: Two-Pass Approach

```
Pass 1: Create issues
  → Write: "Blocked by: F-001"        (plain ID, no issue number yet)
  → Result: issues #20, #21 created
  → Save: issueMap = { "F-001": 20, "F-002": 21 }

Pass 2: Patch bodies
  → Read issueMap
  → Replace: "F-001" → "#20 (F-001)"
  → PATCH every issue: "Blocked by: #20 (F-001)"
```

### Why Not Single-Pass?

A single-pass approach requires creating issues in topological order (dependencies first),
which requires sorting the feature graph and handling circular dependencies. The two-pass
approach is simpler, more robust, and handles any graph shape.

### Visual Illustration

```
PASS 1 OUTPUT          PASS 2 OUTPUT
──────────────         ──────────────────────────
Blocked by: F-001  →   Blocked by: #20 (F-001)
Blocks: F-003      →   Blocks: #22 (F-003)
```

---

## 10. GitHub API Patterns

### 10.1 REST Helper Function

Use this pattern for all REST calls. Always use `-Compress` and `charset=utf-8`:

```powershell
function Invoke-GH {
    param([string]$Method, [string]$Url, [object]$Body = $null)
    $p = @{ Method = $Method; Uri = $Url; Headers = $Headers }
    if ($null -ne $Body) {
        $p.Body        = ($Body | ConvertTo-Json -Depth 10 -Compress)
        $p.ContentType = "application/json; charset=utf-8"
    }
    return Invoke-RestMethod @p
}
```

### 10.2 GraphQL Helper Function

```powershell
function Invoke-GQL {
    param([string]$Query)
    return (Invoke-GH POST "https://api.github.com/graphql" @{ query = $Query })
}
```

### 10.3 Token Retrieval (No `gh` CLI)

```powershell
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
if (-not $Token) { Write-Error "No GitHub token found. Authenticate via Git first."; exit 1 }
```

### 10.4 Key REST Endpoints

| Action | Method | Endpoint |
|--------|--------|----------|
| Create issue | POST | `https://api.github.com/repos/{owner}/{repo}/issues` |
| Patch issue body | PATCH | `https://api.github.com/repos/{owner}/{repo}/issues/{number}` |
| Create label | POST | `https://api.github.com/repos/{owner}/{repo}/labels` |
| Check label | GET | `https://api.github.com/repos/{owner}/{repo}/labels/{name}` |

### 10.5 Key GraphQL Operations

**Find owner and existing projects:**
```graphql
query {
  repositoryOwner(login: "owner") {
    id
    projectsV2(first: 25) {
      nodes { id title number }
    }
  }
}
```

**Create a project:**
```graphql
mutation {
  createProjectV2(input: {ownerId: "NODE_ID", title: "My Project"}) {
    projectV2 { id title number }
  }
}
```

**Get an issue's node ID (needed for project board):**
```graphql
query {
  repository(owner: "owner", name: "repo") {
    issue(number: 42) { id }
  }
}
```

**Add issue to project board:**
```graphql
mutation {
  addProjectV2ItemById(input: {projectId: "PROJ_ID", contentId: "ISSUE_NODE_ID"}) {
    item { id }
  }
}
```

> **GitHub Projects v2 has no REST endpoint.** All board operations must go through
> GraphQL. A `GET /repos/{owner}/{repo}/projects` call returns HTTP 404 for Projects v2.

---

## 11. Common Pitfalls and Fixes

These are real issues encountered when running the bulk creation workflow. Each has a
root cause, a symptom, and a confirmed fix.

---

### Lesson 1 — Em-dash and `&` in PowerShell strings

**Symptom:**
```
Unexpected token 'Foundation' in expression or statement.
The ampersand (&) character is not allowed.
```

**Root cause:** Unicode em-dash `—` (U+2014) and the `&` character inside double-quoted
PowerShell strings cause parse errors.

**Fix:** Use a JSON data file for all user-visible strings. PowerShell's `ConvertFrom-Json`
handles special characters cleanly. Replace em-dashes with `-` and `&` with `and` in your
data values.

---

### Lesson 2 — `$variable:` triggers "drive letter" parse error

**Symptom:**
```
Variable reference is not valid. ':' was not followed by a valid variable name character.
```

**Root cause:** Inside a double-quoted string, `$issueNumber:` is parsed as a PS drive
reference (like `$env:PATH`).

**Fix:**
```powershell
# BAD
"[FAIL] Update #$issueNumber: $_"

# GOOD
$err = $_
"[FAIL] Update issue $issueNumber - $err"
```

---

### Lesson 3 — Here-strings inside array or hashtable literals

**Symptom:** Cascading parse errors across the entire script block.

**Root cause:** `@"..."@` here-strings cannot be nested inside `@(...)` or `@{...}` array
/ hashtable literals.

**Fix:** Use string concatenation instead:
```powershell
# BAD
$features = @(
    @{ body = @"
line 1
line 2
"@ }
)

# GOOD
$body = "line 1`n" + "line 2"
```

---

### Lesson 4 — `gh` CLI not installed

**Symptom:** `gh: command not found` or `The term 'gh' is not recognized`.

**Root cause:** The `gh` CLI is not installed by default.

**Fix:** Retrieve the token directly from Windows credential manager via Git:
```powershell
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
```

This works on any Windows machine where you have previously run `git push` or `git clone`
against GitHub over HTTPS.

---

### Lesson 5 — GitHub Projects v2 has no REST endpoint

**Symptom:** HTTP 404 when calling `/repos/{owner}/{repo}/projects` for a Projects v2
board.

**Root cause:** Projects v2 boards (the modern GitHub Projects experience) exist only in
the GraphQL API. The REST `/projects` endpoint only covers the older Projects v1 (classic).

**Fix:** Use GraphQL mutations for all project operations. See
[§ 10.5 Key GraphQL Operations](#105-key-graphql-operations).

---

### Lesson 6 — Missing `-Compress` or wrong `ContentType`

**Symptom:** Malformed request body or unexpected API errors.

**Root cause:** `ConvertTo-Json` without `-Compress` emits multi-line JSON. Some GitHub API
endpoints also reject bodies without the correct charset.

**Fix:**
```powershell
$p.Body        = ($Body | ConvertTo-Json -Depth 10 -Compress)
$p.ContentType = "application/json; charset=utf-8"
```

Always set both `-Compress` and `charset=utf-8`.

---

### Lesson 7 — HTTP 429 from too many rapid API calls

**Symptom:** `Invoke-RestMethod` returns HTTP 429 (Too Many Requests) or issues start
failing after the first batch.

**Root cause:** GitHub rate-limits unauthenticated and certain authenticated calls.
On repos with many issues, rapid back-to-back calls trigger the secondary rate limit.

**Fix:** Add `Start-Sleep` between API calls:
```powershell
Start-Sleep -Milliseconds 600   # between issue-creation POSTs
Start-Sleep -Milliseconds 400   # between PATCH / GraphQL calls
```

---

### Lesson 8 — `Get-Content -Raw` returns a PSObject, not a plain string

**Symptom:** GitHub API returns HTTP 422 with a body like:
```json
{"message":"Invalid request.\n\nFor 'properties/body',
{\"value\" => \"# Feature F-001...\", \"PSPath\" => \"C:\\\\...\",
\"PSDrive\" => {\"Name\" => \"C\", ...}"}
```

**Root cause:** `Get-Content -Raw` returns a `PSObject` with PowerShell filesystem
metadata attached (`PSPath`, `PSDrive`, `PSChildName`, `PSParentPath`, plus dozens of
`System.Management.Automation` assembly type names). When passed to `ConvertTo-Json`,
all metadata is serialised into the request body, making it hundreds of kilobytes and
completely invalid as an issue body.

**Fix:**
```powershell
# BAD — returns PSObject
$body = Get-Content $file.FullName -Raw -Encoding utf8

# GOOD — returns a guaranteed plain string
$body = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
```

This is the single most common PowerShell-to-GitHub-API mistake. **Always use
`File::ReadAllText()`** when reading file content to send to an API.

---

### Lesson 9 — `$PSScriptRoot` is null when launched via `powershell -File`

**Symptom:** Paths built with `Join-Path $PSScriptRoot "..."` resolve to the wrong
directory or throw errors.

**Root cause:** `$PSScriptRoot` is reliably populated only when dot-sourcing (`. script.ps1`)
or importing a module. When launching via `powershell -File path\to\script.ps1` from a
different working directory, `$PSScriptRoot` can be `$null`.

**Fix:**
```powershell
# BAD (unreliable with -File)
$FeaturesDir = Join-Path $PSScriptRoot "..\docs\features"

# GOOD — use MyInvocation
$ScriptDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$FeaturesDir = Join-Path $ScriptDir "..\docs\features"

# ALSO GOOD for non-interactive automation
$FeaturesDir = "C:\absolute\path\to\docs\features"
```

---

### Lesson 10 — Background terminal output shows stale content

**Symptom:** After running a background terminal command in VS Code (e.g., via Copilot
agent `isBackground: true`), the terminal output shows the tail of a **previous** command's
buffer. You may see `PREVIOUS OUTPUT TRUNCATED` with stale
`System.Management.Automation` type names.

**Root cause:** VS Code reuses background terminal buffers. `get_terminal_output` reads
whatever is currently in the buffer, which may not have been cleared from the previous
command.

**Fix option 1:** Run in foreground and capture to a log file:
```powershell
powershell -File script.ps1 *>&1 | Out-File log.txt -Encoding utf8
Get-Content log.txt | Select-String "\[OK\]|\[FAIL\]"
```

**Fix option 2:** Validate by querying the GitHub API directly instead of relying on
terminal output:
```powershell
# Verify an issue was created
Invoke-RestMethod -Headers $Headers `
    -Uri "https://api.github.com/repos/owner/repo/issues?labels=feature&per_page=5"
    | Select-Object number, title
```

---

## 12. Running from the VS Code Terminal

### 12.1 Terminal Setup

Open the integrated terminal (`Ctrl+`` ` ```) and verify you are in the repository root:

```powershell
Get-Location
# Should output: C:\xampp8.0\htdocs\oai-pmh (or your project root)
```

If not at the root:
```powershell
Set-Location "C:\path\to\your\project"
```

### 12.2 Execution Policy

If you see a "scripts are disabled" error:
```powershell
# For the current session only (safe, does not persist)
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
```

Or prefix the script invocation with `-ExecutionPolicy Bypass`:
```powershell
powershell -ExecutionPolicy Bypass -File .\script.ps1 ...
```

### 12.3 Recommended: Foreground Terminal for Scripted Operations

When creating many issues, always run in a **foreground** terminal and capture output to a
file. This avoids stale buffer issues (see [Lesson 10](#lesson-10--background-terminal-output-shows-stale-content)):

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues\scripts\create-github-issues.ps1 `
    -Repo        "owner/repo" `
    -Label       "feature" `
    -ProjectName "My Board" `
    -DataFile    "bin\features-data.json" `
    *>&1 | Tee-Object -FilePath "bin\create-issues.log"
```

The `Tee-Object` sends output to both the terminal and a log file simultaneously.

### 12.4 Reviewing the Log

```powershell
# Summary: count OK and FAIL lines
Select-String "\[OK\]|\[FAIL\]" bin\create-issues.log | Measure-Object

# Show only failures
Select-String "\[FAIL\]" bin\create-issues.log
```

---

## 13. Validating Results

After running the script, verify the results via the GitHub API rather than trusting
terminal output alone.

### 13.1 Check Issue Count

```powershell
# Count open issues with your label (paginates at 100; adjust per_page as needed)
$Headers = @{
    "Authorization"        = "Bearer $Token"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}
$issues = Invoke-RestMethod `
    -Headers $Headers `
    -Uri "https://api.github.com/repos/owner/repo/issues?labels=feature&state=open&per_page=100"
$issues.Count
```

### 13.2 Check Issue Map Completeness

```powershell
$map = Get-Content bin\issue-map.json | ConvertFrom-Json
$map.PSObject.Properties | Select-Object Name, Value | Format-Table
```

Expected: one row per feature ID, each with a non-null issue number.

### 13.3 Spot-Check a Specific Issue

```powershell
$issue = Invoke-RestMethod -Headers $Headers `
    -Uri "https://api.github.com/repos/owner/repo/issues/20"
$issue.title
$issue.body
$issue.labels.name
```

### 13.4 Verify Project Board Membership

Open GitHub in the browser:  
`https://github.com/orgs/{org}/projects` or `https://github.com/{owner}/{repo}/projects`

Each issue should appear as a card on the board.

---

## 14. Quick-Reference Cheat Sheet

### Full Run — All Four Steps

```powershell
# Step 1: Validate your data file
Get-Content bin\features-data.json | ConvertFrom-Json | Measure-Object

# Step 2: Create issues
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues\scripts\create-github-issues.ps1 `
    -Repo "owner/repo" -Label "feature" -ProjectName "My Board" `
    -DataFile "bin\features-data.json" `
    *>&1 | Tee-Object -FilePath "bin\create-issues.log"

# Step 3 (optional): Replace bodies with full source files
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues\scripts\update-issues-full-content.ps1 `
    -Repo "owner/repo" -SourceDir "docs\features" `
    -IssueMapFile "bin\issue-map.json" `
    *>&1 | Tee-Object -FilePath "bin\update-issues.log"

# Step 4: Clean up
Remove-Item bin\features-data.json, bin\issue-map.json -Force
```

### Pitfall Quick-Reference

| # | Symptom | Root Cause | Fix |
|---|---------|------------|-----|
| 1 | `Unexpected token` parse error | Em-dash / `&` in PS string | Use JSON data file |
| 2 | `Variable reference is not valid` | `$var:` in string | Rename var or restructure |
| 3 | Cascading parse errors | Here-string inside `@()` | Use string concatenation |
| 4 | `gh: command not found` | `gh` CLI not installed | Use `git credential-manager get` |
| 5 | HTTP 404 on project endpoint | Projects v2 is GraphQL-only | Use GraphQL mutations |
| 6 | Malformed request body | Missing `-Compress` / charset | Add `-Compress` and `charset=utf-8` |
| 7 | HTTP 429 Too Many Requests | Rapid API calls | Add `Start-Sleep` between calls |
| 8 | HTTP 422, body contains `PSPath` | `Get-Content -Raw` returns PSObject | Use `File::ReadAllText()` |
| 9 | Wrong file paths | `$PSScriptRoot` null with `-File` | Use `$MyInvocation` or absolute paths |
| 10 | Stale terminal output | Background terminal buffer reuse | Capture to file or query API directly |

---

## Related Files

| File | Purpose |
|------|---------|
| [.github/skills/github-bulk-issues/SKILL.md](.github/skills/github-bulk-issues/SKILL.md) | Skill entry point with workflow summary |
| [.github/skills/github-bulk-issues/references/data-schema.md](.github/skills/github-bulk-issues/references/data-schema.md) | JSON data file schema and examples |
| [.github/skills/github-bulk-issues/scripts/create-github-issues.ps1](.github/skills/github-bulk-issues/scripts/create-github-issues.ps1) | Main bulk create script |
| [.github/skills/github-bulk-issues/scripts/update-issues-full-content.ps1](.github/skills/github-bulk-issues/scripts/update-issues-full-content.ps1) | Optional: replace bodies with source files |
| [docs/GITHUB_BULK_ISSUES_LEARNINGS.md](GITHUB_BULK_ISSUES_LEARNINGS.md) | Detailed lessons learned with code examples |

---

*Last updated: February 19, 2026*
