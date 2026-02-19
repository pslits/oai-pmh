# Creating GitHub Issues from VS Code — Using the `gh` CLI

**Version:** 1.1  
**Date:** February 19, 2026  
**Author:** Paul Slits  
**Applies to:** Any project using VS Code + PowerShell 5.1 on Windows with `gh` CLI installed  
**Companion to:** [GITHUB_ISSUES_FROM_VSCODE_GUIDE.md](GITHUB_ISSUES_FROM_VSCODE_GUIDE.md) (REST/GraphQL variant without `gh`)

---

## Table of Contents

1. [Overview and Differences from the REST Variant](#1-overview-and-differences-from-the-rest-variant)
2. [Prerequisites](#2-prerequisites)
3. [Tooling Architecture](#3-tooling-architecture)
4. [File Structure](#4-file-structure)
5. [Step 1 — Prepare the Data File](#5-step-1--prepare-the-data-file)
6. [Step 2 — Run the Bulk Create Script](#6-step-2--run-the-bulk-create-script)
7. [Step 3 — Add Feature Issues to Project Board](#7-step-3--add-feature-issues-to-project-board)
8. [Step 4 — (Optional) Replace Bodies with Full Source Files](#8-step-4--optional-replace-bodies-with-full-source-files)
9. [Step 5 — Clean Up Temp Files](#9-step-5--clean-up-temp-files)
10. [Adding User Stories to a Feature Issue](#10-adding-user-stories-to-a-feature-issue)
11. [Two-Pass Strategy for Dependency Links](#11-two-pass-strategy-for-dependency-links)
12. [gh CLI Patterns](#12-gh-cli-patterns)
13. [Common Pitfalls and Fixes](#13-common-pitfalls-and-fixes)
14. [Running from the VS Code Terminal](#14-running-from-the-vs-code-terminal)
15. [Validating Results](#15-validating-results)
16. [Quick-Reference Cheat Sheet](#16-quick-reference-cheat-sheet)

---

## 1. Overview and Differences from the REST Variant

This guide covers the same bulk issue creation workflow as the REST/GraphQL variant, but
uses the `gh` CLI instead of `Invoke-RestMethod`. The **architecture is identical** — data
file, create loop, dependency patch loop, project board loop — but the `gh` CLI removes
all auth and header boilerplate.

### What changes with `gh`

| Aspect | REST variant | `gh` CLI variant |
|--------|-------------|-----------------|
| Authentication | Manual token via `git credential-manager get` | `gh auth login` — handled automatically |
| HTTP headers | Manual `$Headers` hashtable for every call | Not needed — `gh` adds them |
| Issue creation | `Invoke-RestMethod POST` | `gh issue create` |
| Issue patch | `Invoke-RestMethod PATCH` | `gh issue edit` |
| Label creation | `Invoke-RestMethod POST` | `gh label create` |
| Projects v2 | Direct GraphQL via `Invoke-RestMethod` | `gh project item-add` or direct GraphQL via `gh api graphql` |
| Script length | ~180 lines | ~130 lines |

### What stays the same

- JSON data file with feature definitions
- Loop over features to create issues one by one
- Two-pass strategy: create first, patch dependency links second
- Rate-limit sleeps between API calls
- `File::ReadAllText()` for reading source files
- `issue-map.json` to track feature ID → issue number

---

## 2. Prerequisites

### 2.1 Install the `gh` CLI

Download from [cli.github.com](https://cli.github.com) or install via winget:

```powershell
winget install --id GitHub.cli
```

> **Note:** `gh` is not included with Git for Windows or VS Code — it must be installed
> separately. After installation, restart VS Code so the terminal picks up the updated
> `PATH`.

Verify installation:

```powershell
gh --version
# gh version 2.87.0 (2026-02-xx)
# https://github.com/cli/cli/releases/tag/v2.87.0
```

Check for updates at any time:

```powershell
winget upgrade --id GitHub.cli
```

### 2.2 Authenticate

```powershell
gh auth login
```

Follow the interactive prompts:

1. Select **GitHub.com**
2. Select **HTTPS**
3. Select **Login with a web browser** (or paste a token)
4. Complete the browser OAuth flow

Verify:

```powershell
gh auth status
# ✓ Logged in to github.com as YourUsername (oauth_token)
```

### 2.3 Required Permissions

The token used by `gh auth login` must have:

- `repo` scope — to create issues and labels
- `project` scope — to add items to Projects v2 boards

If `gh project item-add` fails with a permission error, re-authenticate and select
additional scopes:

```powershell
gh auth refresh --scopes repo,project
```

### 2.4 Other Tools

| Tool | Requirement | Notes |
|------|-------------|-------|
| VS Code | Any recent version | Integrated terminal used for all commands |
| PowerShell | 5.1+ (Windows built-in) | Already available on Windows |
| `gh` CLI | 2.x+ | See § 2.1 |

---

## 3. Tooling Architecture

```
.github/skills/github-bulk-issues-gh/
├── scripts/
│   ├── create-github-issues-gh.ps1          # Main script (gh CLI version)
│   └── update-issues-full-content-gh.ps1   # Optional: replace bodies with source files
└── references/
    └── data-schema.md                        # Same JSON schema as the REST variant
```

The script uses three `gh` commands plus one direct GraphQL call:

| Operation | Command |
|-----------|---------|
| Create issue | `gh issue create` |
| Patch issue body | `gh issue edit` |
| Create/check label | `gh label create` / `gh label list` |
| Add to Projects v2 | `gh project item-add` |

> **Note:** `gh project item-add` requires `gh` 2.31+. For older versions, fall back to
> the direct GraphQL pattern shown in [§ 10.3](#103-projects-v2--fall-back-to-direct-graphql).

---

## 4. File Structure

```
project root/
├── .github/
│   └── skills/
│       └── github-bulk-issues-gh/
│           └── scripts/
│               ├── create-github-issues-gh.ps1
│               └── update-issues-full-content-gh.ps1
├── bin/
│   ├── features-data.json               # Your input file (created by you)
│   └── issue-map.json                   # Generated: feature ID → issue number
└── docs/
    └── features/                         # Optional: source markdown files per issue
        ├── F-001_PROJECT_FOUNDATION.md
        └── ...
```

The `bin/features-data.json` schema is **identical** to the REST variant. See
[references/data-schema.md](.github/skills/github-bulk-issues/references/data-schema.md).

---

## 5. Step 1 — Prepare the Data File

Create `bin/features-data.json`. The schema is the same as the REST variant.

### 5.1 Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | string | ✅ | Unique feature ID, e.g. `"F-001"` |
| `title` | string | ✅ | Issue title as it appears on GitHub |
| `description` | string | ✅ | Body text for the Description section |
| `blockedBy` | string[] | ✅ | IDs of features this one depends on (`[]` if none) |
| `blocks` | string[] | ✅ | IDs of features that depend on this one (`[]` if none) |
| `priority` | string | optional | e.g. `"MVP"` |
| `phase` | string | optional | e.g. `"Phase 1 - Configuration"` |
| `context` | string | optional | Bounded context or domain area |

### 5.2 Example

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

The same rules apply as in the REST variant — the data is passed to PowerShell string
interpolation before being sent to `gh`:

| Character | Problem | Safe Alternative |
|-----------|---------|-----------------|
| `—` (em-dash) | `Unexpected token` parse error | Replace with `-` |
| `&` | PS parse error in double-quoted strings | Replace with `and` |
| `` ` `` | PowerShell escape character | Avoid in text values |

### 5.4 Validate Before Running

```powershell
Get-Content bin\features-data.json | ConvertFrom-Json | Measure-Object
# Should print Count : 39 (or however many features you have)
```

---

## 6. Step 2 — Run the Bulk Create Script

### 6.1 The Script

Save this as `.github/skills/github-bulk-issues-gh/scripts/create-github-issues-gh.ps1`:

```powershell
<#
.SYNOPSIS
    Creates GitHub issues in bulk using the gh CLI, assigns a label, cross-links
    dependencies, and adds every issue to a GitHub Projects v2 board.

.PARAMETER Repo
    GitHub repository in "owner/name" format.

.PARAMETER Label
    Label name to apply to every issue. Created if it doesn't exist.

.PARAMETER ProjectNumber
    GitHub Projects v2 board number (visible in the board URL).

.PARAMETER DataFile
    Path to the JSON data file.

.EXAMPLE
    .\create-github-issues-gh.ps1 -Repo "pslits/oai-pmh" -Label "feature" -ProjectNumber 1
#>
param(
    [Parameter(Mandatory)] [string] $Repo,
    [Parameter(Mandatory)] [string] $Label,
    [Parameter(Mandatory)] [int]    $ProjectNumber,
    [string] $DataFile = (Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Path) "..\..\..\..\bin\features-data.json")
)

$Owner    = $Repo.Split("/")[0]
$RepoName = $Repo.Split("/")[1]

# ── Load data ────────────────────────────────────────────────────────────────────
$features = [System.IO.File]::ReadAllText(
    (Resolve-Path $DataFile),
    [System.Text.Encoding]::UTF8
) | ConvertFrom-Json
Write-Host "Loaded $($features.Count) features from $DataFile" -ForegroundColor Cyan

# ── Label ─────────────────────────────────────────────────────────────────────────
Write-Host "`n=== Label '$Label' ===" -ForegroundColor Cyan
$existing = gh label list --repo $Repo --json name | ConvertFrom-Json |
    Where-Object { $_.name -eq $Label }
if ($existing) {
    Write-Host "  Already exists" -ForegroundColor Yellow
} else {
    gh label create $Label --repo $Repo --color "0075ca" --description "New feature or request"
    Write-Host "  Created" -ForegroundColor Green
}

# ── Pass 1: Create issues ─────────────────────────────────────────────────────────
Write-Host "`n=== Creating $($features.Count) issues ===" -ForegroundColor Cyan
$issueMap = @{}

foreach ($f in $features) {
    $blockedByStr = if ($f.blockedBy.Count -gt 0) { $f.blockedBy -join ", " } else { "(none)" }
    $blocksStr    = if ($f.blocks.Count    -gt 0) { $f.blocks    -join ", " } else { "(none)" }

    $body = "**Feature ID:** $($f.id)`n" +
            "**Priority:** $($f.priority)`n" +
            "**Phase:** $($f.phase)`n" +
            "**Bounded Context:** $($f.context)`n`n" +
            "## Description`n`n$($f.description)`n`n" +
            "## Dependencies`n`n" +
            "**Blocked by:** $blockedByStr`n`n" +
            "**Blocks:** $blocksStr"

    # Write body to temp file to avoid shell escaping issues with gh --body
    $tmpFile = [System.IO.Path]::GetTempFileName()
    [System.IO.File]::WriteAllText($tmpFile, $body, [System.Text.Encoding]::UTF8)

    try {
        $url    = gh issue create --repo $Repo --title $f.title --label $Label --body-file $tmpFile
        $number = $url -replace ".*/issues/", ""
        $issueMap[$f.id] = [int]$number
        Write-Host "  [OK] #$number - $($f.title)" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] $($f.id): $_" -ForegroundColor Red
    } finally {
        Remove-Item $tmpFile -Force -ErrorAction SilentlyContinue
    }

    Start-Sleep -Milliseconds 600
}

# Save map
$mapFile = Join-Path (Split-Path $DataFile) "issue-map.json"
$issueMap | ConvertTo-Json | Set-Content $mapFile -Encoding utf8
Write-Host "Issue map saved to $mapFile" -ForegroundColor Cyan

# ── Pass 2: Patch bodies with live #links ─────────────────────────────────────────
Write-Host "`n=== Updating dependency links ===" -ForegroundColor Cyan
foreach ($f in $features) {
    $n = $issueMap[$f.id]
    if (-not $n) { continue }

    $blockedByLinks = if ($f.blockedBy.Count -gt 0) {
        ($f.blockedBy | ForEach-Object {
            $m = $issueMap[$_]; if ($m) { "#$m ($_)" } else { $_ }
        }) -join ", "
    } else { "(none)" }

    $blocksLinks = if ($f.blocks.Count -gt 0) {
        ($f.blocks | ForEach-Object {
            $m = $issueMap[$_]; if ($m) { "#$m ($_)" } else { $_ }
        }) -join ", "
    } else { "(none)" }

    $newBody = "**Feature ID:** $($f.id)`n" +
               "**Priority:** $($f.priority)`n" +
               "**Phase:** $($f.phase)`n" +
               "**Bounded Context:** $($f.context)`n`n" +
               "## Description`n`n$($f.description)`n`n" +
               "## Dependencies`n`n" +
               "**Blocked by:** $blockedByLinks`n`n" +
               "**Blocks:** $blocksLinks"

    $tmpFile = [System.IO.Path]::GetTempFileName()
    [System.IO.File]::WriteAllText($tmpFile, $newBody, [System.Text.Encoding]::UTF8)

    try {
        gh issue edit $n --repo $Repo --body-file $tmpFile | Out-Null
        Write-Host "  [OK] Updated #$n ($($f.id))" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] #$n: $_" -ForegroundColor Red
    } finally {
        Remove-Item $tmpFile -Force -ErrorAction SilentlyContinue
    }

    Start-Sleep -Milliseconds 400
}

# ── Pass 3: Add to project board ──────────────────────────────────────────────────
Write-Host "`n=== Adding to project #$ProjectNumber ===" -ForegroundColor Cyan
foreach ($f in $features) {
    $n = $issueMap[$f.id]
    if (-not $n) { continue }

    try {
        gh project item-add $ProjectNumber --owner $Owner --url "https://github.com/$Repo/issues/$n"
        Write-Host "  [OK] Added #$n ($($f.id))" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] #$n: $_" -ForegroundColor Red
    }

    Start-Sleep -Milliseconds 400
}

Write-Host "`n=== DONE — $($issueMap.Count) issues created ===" -ForegroundColor Cyan
```

### 6.2 Key Difference: `--body-file` Instead of Inline Body

The `gh` CLI accepts `--body "text"` for inline body text, but this approach breaks on
multi-line strings in PowerShell because shell escaping becomes unreliable. **Always use
`--body-file`** with a temp file:

```powershell
# BAD — breaks on newlines, quotes, special characters
gh issue create --repo $Repo --title $f.title --body $body

# GOOD — write body to a temp file, pass the file path
$tmpFile = [System.IO.Path]::GetTempFileName()
[System.IO.File]::WriteAllText($tmpFile, $body, [System.Text.Encoding]::UTF8)
gh issue create --repo $Repo --title $f.title --body-file $tmpFile
Remove-Item $tmpFile -Force
```

This is the `gh` CLI equivalent of the `File::ReadAllText()` rule from the REST variant.

### 6.3 Run the Script

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues-gh\scripts\create-github-issues-gh.ps1 `
    -Repo          "owner/repo" `
    -Label         "feature" `
    -ProjectNumber 1 `
    -DataFile      "bin\features-data.json"
```

> **Finding the project number:** Open the board on GitHub. The URL is
> `https://github.com/users/{owner}/projects/{number}` or
> `https://github.com/orgs/{org}/projects/{number}`.
>
> **Automated lookup:** Use `.github/scripts/Get-ProjectNumber.ps1` to resolve the
> number by title without opening the browser:
> ```powershell
> powershell -ExecutionPolicy Bypass \
>     -File .github\scripts\Get-ProjectNumber.ps1 \
>     -Repo "owner/repo" -ProjectTitle "My Board"
> # Outputs: 5
> ```
> The result can be piped directly into `-ProjectNumber`:
> ```powershell
> $num = powershell -ExecutionPolicy Bypass \
>     -File .github\scripts\Get-ProjectNumber.ps1 \
>     -Repo "owner/repo" -ProjectTitle "My Board"
> ```

### 6.4 Expected Console Output

```
Loaded 39 features from bin\features-data.json

=== Label 'feature' ===
  Already exists

=== Creating 39 issues ===
  [OK] #20 - F-001: Project Foundation and Shared Contracts
  [OK] #21 - F-002: Configuration Management
  ...

Issue map saved to bin\issue-map.json

=== Updating dependency links ===
  [OK] Updated #20 (F-001)
  [OK] Updated #21 (F-002)
  ...

=== Adding to project #1 ===
  [OK] Added #20 (F-001)
  [OK] Added #21 (F-002)
  ...

=== DONE — 39 issues created ===
```

---

## 7. Step 3 — Add Feature Issues to Project Board

After creating issues, add all feature issues (those with titles matching `F-NNN:`) to
the Projects v2 board. This step is **required** when setting up a new project board, and
can be re-run safely — `gh project item-add` is idempotent for items already on the board.

### 7.1 Command

```powershell
$featureNums = (gh issue list --repo pslits/oai-pmh --state open --json number,title --limit 100 |
    ConvertFrom-Json) | Where-Object { $_.title -match '^F-\d+:' } |
    ForEach-Object { $_.number }

Write-Host "Found $($featureNums.Count) feature issues"
$ok = 0; $fail = 0
foreach ($n in $featureNums) {
    try {
        gh project item-add 5 --owner pslits --url "https://github.com/pslits/oai-pmh/issues/$n" | Out-Null
        Write-Host "  [OK] #$n" -ForegroundColor Green; $ok++
    } catch {
        Write-Host "  [FAIL] #$n : $_" -ForegroundColor Red; $fail++
    }
    Start-Sleep -Milliseconds 400
}
Write-Host "Done: $ok added, $fail failed" -ForegroundColor Cyan
```

> **Filter logic:** `Where-Object { $_.title -match '^F-\d+:' }` selects only issues
> whose titles start with `F-NNN:` (the feature issues). Non-feature issues such as
> bug reports, refactoring tasks, or integration tests are excluded automatically.

> **PS5.1 note:** Use `ForEach-Object { $_.number }` to extract a property from a
> `ConvertFrom-Json` result array, **not** `Select-Object -ExpandProperty number`.
> In PowerShell 5.1, `ConvertFrom-Json` returns a `PSCustomObject[]` where
> `Select-Object -ExpandProperty` may fail with "Property not found".

### 7.2 Verify

```powershell
gh project item-list 5 --owner pslits --format json |
    ConvertFrom-Json | ForEach-Object { $_.items } | Measure-Object
# Should show Count : 39
```

---

## 8. Step 4 — (Optional) Replace Bodies with Full Source Files

If you have detailed markdown files per issue, replace the generated bodies with them.

### 8.1 The Script

Save as `.github/skills/github-bulk-issues-gh/scripts/update-issues-full-content-gh.ps1`:

```powershell
<#
.SYNOPSIS
    Replaces every GitHub issue body with the full text of the corresponding source file,
    using gh CLI.

.PARAMETER Repo
    GitHub repository in "owner/name" format.

.PARAMETER SourceDir
    Directory containing the source markdown files.

.PARAMETER FilePattern
    Glob pattern for source files (default: "*.md").

.PARAMETER IdPattern
    Regex to extract the issue ID from the filename.
    Default: "^(F-\d+)" matches "F-001" from "F-001_SOME_FEATURE.md".

.PARAMETER IssueMapFile
    Path to the issue-map.json produced by the create script.
#>
param(
    [Parameter(Mandatory)] [string] $Repo,
    [Parameter(Mandatory)] [string] $SourceDir,
    [string] $FilePattern  = "*.md",
    [string] $IdPattern    = "^(F-\d+)",
    [string] $IssueMapFile = "bin\issue-map.json"
)

# Load issue map
$rawMap   = [System.IO.File]::ReadAllText(
    (Resolve-Path $IssueMapFile),
    [System.Text.Encoding]::UTF8
) | ConvertFrom-Json
$issueMap = @{}
$rawMap.PSObject.Properties | ForEach-Object { $issueMap[$_.Name] = $_.Value }
Write-Host "Loaded $($issueMap.Count) entries from $IssueMapFile" -ForegroundColor Cyan

$files = Get-ChildItem -Path $SourceDir -Filter $FilePattern | Sort-Object Name
Write-Host "Updating $($files.Count) files..." -ForegroundColor Cyan

foreach ($file in $files) {
    $match = [regex]::Match($file.BaseName, $IdPattern)
    if (-not $match.Success) {
        Write-Host "  [SKIP] $($file.Name) - no ID match" -ForegroundColor Yellow
        continue
    }
    $featureId = $match.Groups[1].Value
    $issueNum  = $issueMap[$featureId]
    if (-not $issueNum) {
        Write-Host "  [SKIP] $featureId - not in issue map" -ForegroundColor Yellow
        continue
    }

    # KEY: use File::ReadAllText, not Get-Content -Raw
    # Passing the file path directly to --body-file avoids the PSObject pitfall entirely
    try {
        gh issue edit $issueNum --repo $Repo --body-file $file.FullName
        Write-Host "  [OK] #$issueNum $featureId" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] #$issueNum $featureId : $_" -ForegroundColor Red
    }

    Start-Sleep -Milliseconds 400
}
```

> **Bonus:** When using `--body-file` with a file path, `gh` reads the file itself
> internally — bypassing the `Get-Content -Raw` PSObject pitfall entirely. No
> `File::ReadAllText()` needed in the update script.

### 8.2 Run the Script

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues-gh\scripts\update-issues-full-content-gh.ps1 `
    -Repo         "owner/repo" `
    -SourceDir    "docs\features" `
    -IssueMapFile "bin\issue-map.json"
```

---

## 9. Step 5 — Clean Up Temp Files

```powershell
Remove-Item bin\features-data.json, bin\issue-map.json -Force
```

---

## 10. Adding User Stories to a Feature Issue

Once feature issues exist, you can break each feature into **user stories** and create
them as GitHub issues labelled `story`, with the parent feature issue linked in the body.
This section documents the exact workflow used to create the 9 user stories for F-001.

### 10.1 Overview

The story workflow reuses the **REST variant** script
(`.github/skills/github-bulk-issues/scripts/create-github-issues.ps1`) with:

- A custom data file whose `id` fields follow the `US-NNN.NN` pattern.
- The `story` label instead of `feature`.
- An extra `**Feature:** #N (F-NNN)` line in each issue body linking to the parent.
- A follow-up patch on the parent feature issue to add a `## User Stories` tasklist.
- A full-body update using the REST `update-issues-full-content.ps1` script with a
  custom `-IdPattern`.

> **Why the REST variant, not `gh`?**  
> The REST script is invoked inline in the current PowerShell session
> (`Set-ExecutionPolicy Bypass -Scope Process; & "bin\create-stories.ps1"`) to avoid
> sub-shell parse errors. When called via `powershell -ExecutionPolicy Bypass -File script.ps1`,
> PowerShell 5.1 chokes on em-dashes (`—`) in `Write-Host` strings (unterminated string
> error) and on `#$n:` patterns inside double-quoted strings
> (`Variable reference is not valid: ':' was not followed by a valid variable name`).
> Both errors disappear when the script runs in the existing session with `& "script.ps1"`.
> See **Pitfall N** in the quick-reference table.

> **Personal account GraphQL note (Pitfall M):**  
> The `repositoryOwner(login: "...")` GraphQL query returns an empty `projectsV2` list
> for personal GitHub accounts — it only works for organization accounts. Use the
> `viewer` query instead to look up projects on a personal account:
> ```powershell
> # WRONG for personal accounts
> $q = "query { repositoryOwner(login: `"pslits`") { projectsV2(first:25){ nodes{id title} } } }"
>
> # CORRECT for personal accounts
> $q = "query { viewer { projectsV2(first:25){ nodes{id title number} } } }"
> $r = Invoke-GQL $q
> $project = $r.data.viewer.projectsV2.nodes | Where-Object { $_.title -eq "OAI Repository" } | Select-Object -First 1
> $projectId = $project.id
> ```

### 10.2 Step 1 — Prepare the Stories Data File

Create `bin/stories-{feature}-data.json`. The schema is identical to the features data
file, with these conventions:

| Field | Convention for stories |
|-------|------------------------|
| `id` | `"US-NNN.NN"` — e.g. `"US-001.01"` |
| `title` | `"US-NNN.NN: Story title"` |
| `priority` | Same as parent feature, e.g. `"MVP (MUST HAVE)"` |
| `phase` | Same as parent feature |
| `context` | The bounded context this story lives in |
| `description` | Full story text (As a / I want / So that) plus acceptance criteria summary |
| `blockedBy` | Other story IDs this story depends on (e.g. `["US-001.01"]`) |
| `blocks` | Story IDs that depend on this one |

Example entry:

```json
[
  {
    "id": "US-001.01",
    "title": "US-001.01: Project Namespace and Directory Scaffolding",
    "priority": "MVP (MUST HAVE)",
    "phase": "Phase 0 - Foundation and Cross-Cutting Infrastructure",
    "context": "Cross-cutting",
    "description": "As a core developer, I want a well-defined namespace structure...",
    "blockedBy": [],
    "blocks": ["US-001.02", "US-001.03"]
  }
]
```

**String safety rules** are the same as for features: avoid em-dashes (`-` instead of
`—`) and `&` characters in description text.

### 10.3 Step 2 — Create Story Issues (REST Script, Inline)

Save the script below to `bin\create-stories-{feature}.ps1`, then run it **in the
current session** (not via a `powershell -File` sub-shell — see Pitfall N):

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
& "bin\create-stories-{feature}.ps1"
```

The script runs four passes:

| Pass | Operation |
|------|-----------|
| Pass 1 | Create all story issues with placeholder dependency text |
| Pass 2 | Patch each body to replace `US-NNN.NN` IDs with live `#n (US-NNN.NN)` links |
| Pass 3 | Add every story issue to the project board via GraphQL (uses `viewer` — see Pitfall M) |
| Pass 4 | Add stories as sub-issues of the parent (fails on personal repos — see § 10.5) |

#### Full Script Template

```powershell
# bin\create-stories-{feature}.ps1
# Adapt: $Repo, $ParentIssue, $ParentFeatureId, $DataFile, $MapFile, $ProjectName

$Repo            = "pslits/oai-pmh"
$Owner           = "pslits"
$RepoName        = "oai-pmh"
$Label           = "story"
$ProjectName     = "OAI Repository"
$ParentIssue     = 20              # GitHub issue number of the parent feature
$ParentFeatureId = "F-001"         # Feature ID string used in back-link
$DataFile        = Join-Path $PSScriptRoot "stories-{feature}-data.json"
$MapFile         = Join-Path $PSScriptRoot "stories-{feature}-issue-map.json"

# Token
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=",""
if (-not $Token) { Write-Error "No GitHub token found."; exit 1 }

$Headers = @{
    "Authorization"        = "Bearer $Token"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

function Invoke-GH {
    param([string]$Method, [string]$Url, [object]$Body = $null)
    $p = @{ Method = $Method; Uri = $Url; Headers = $Headers }
    if ($null -ne $Body) { $p.Body = ($Body | ConvertTo-Json -Depth 10 -Compress); $p.ContentType = "application/json; charset=utf-8" }
    return Invoke-RestMethod @p
}
function Invoke-GQL { param([string]$Query); return (Invoke-GH POST "https://api.github.com/graphql" @{ query = $Query }) }

# Load data
$stories  = [System.IO.File]::ReadAllText((Resolve-Path $DataFile), [System.Text.Encoding]::UTF8) | ConvertFrom-Json
Write-Host "Loaded $($stories.Count) stories" -ForegroundColor Cyan

# Ensure label exists
try { Invoke-GH GET "https://api.github.com/repos/$Repo/labels/$Label" | Out-Null; Write-Host "  Label '$Label' exists" -ForegroundColor Yellow }
catch { Invoke-GH POST "https://api.github.com/repos/$Repo/labels" @{ name = $Label; color = "e11d48"; description = "User story" } | Out-Null; Write-Host "  Label '$Label' created" -ForegroundColor Green }

# Find project — use viewer for personal accounts (Pitfall M)
# repositoryOwner returns empty projectsV2 for personal GitHub accounts.
$pData     = (Invoke-GQL "query { viewer { projectsV2(first: 25) { nodes { id title number } } } }").data
$project   = $pData.viewer.projectsV2.nodes | Where-Object { $_.title -eq $ProjectName } | Select-Object -First 1
$projectId = $project.id
Write-Host "Project: $($project.title) (#$($project.number))" -ForegroundColor Cyan

# Pass 1: Create issues
$issueMap = @{}
foreach ($s in $stories) {
    $blockedByStr = if ($s.blockedBy.Count -gt 0) { $s.blockedBy -join ", " } else { "(none)" }
    $blocksStr    = if ($s.blocks.Count    -gt 0) { $s.blocks    -join ", " } else { "(none)" }
    $body = "**Story ID:** $($s.id)`n**Feature:** #${ParentIssue} ($ParentFeatureId)`n" +
            "**Priority:** $($s.priority)`n**Phase:** $($s.phase)`n**Bounded Context:** $($s.context)`n`n" +
            "## Description`n`n$($s.description)`n`n## Dependencies`n`n" +
            "**Blocked by:** $blockedByStr`n`n**Blocks:** $blocksStr"
    try {
        $issue = Invoke-GH POST "https://api.github.com/repos/$Repo/issues" @{ title = $s.title; body = $body; labels = @($Label) }
        $issueMap[$s.id] = $issue.number
        Write-Host "  [OK] #$($issue.number) - $($s.title)" -ForegroundColor Green
    } catch { Write-Host "  [FAIL] $($s.id): $PSItem" -ForegroundColor Red }
    Start-Sleep -Milliseconds 600
}
$issueMap | ConvertTo-Json | Set-Content $MapFile -Encoding utf8
Write-Host "Issue map saved to $MapFile" -ForegroundColor Cyan

# Pass 2: Patch bodies with live #links
foreach ($s in $stories) {
    $n = $issueMap[$s.id]; if (-not $n) { continue }
    $blockedByLinks = if ($s.blockedBy.Count -gt 0) { ($s.blockedBy | ForEach-Object { $m = $issueMap[$_]; if ($m) { "#${m} ($_)" } else { $_ } }) -join ", " } else { "(none)" }
    $blocksLinks    = if ($s.blocks.Count    -gt 0) { ($s.blocks    | ForEach-Object { $m = $issueMap[$_]; if ($m) { "#${m} ($_)" } else { $_ } }) -join ", " } else { "(none)" }
    $newBody = "**Story ID:** $($s.id)`n**Feature:** #${ParentIssue} ($ParentFeatureId)`n" +
               "**Priority:** $($s.priority)`n**Phase:** $($s.phase)`n**Bounded Context:** $($s.context)`n`n" +
               "## Description`n`n$($s.description)`n`n## Dependencies`n`n" +
               "**Blocked by:** $blockedByLinks`n`n**Blocks:** $blocksLinks"
    try { Invoke-GH PATCH "https://api.github.com/repos/$Repo/issues/${n}" @{ body = $newBody } | Out-Null; Write-Host "  [OK] Updated #${n} ($($s.id))" -ForegroundColor Green }
    catch { Write-Host "  [FAIL] #${n}: $PSItem" -ForegroundColor Red }
    Start-Sleep -Milliseconds 400
}

# Pass 3: Add to project board (viewer query — works for personal accounts)
foreach ($s in $stories) {
    $n = $issueMap[$s.id]; if (-not $n) { continue }
    $nodeId = (Invoke-GQL "query { repository(owner: `"$Owner`", name: `"$RepoName`") { issue(number: ${n}) { id } } }").data.repository.issue.id
    $result = Invoke-GQL "mutation { addProjectV2ItemById(input: {projectId: `"$projectId`", contentId: `"$nodeId`"}) { item { id } } }"
    if ($result.data.addProjectV2ItemById.item.id) { Write-Host "  [OK] #${n} added to project" -ForegroundColor Green }
    else { Write-Host "  [FAIL] #${n}: $($result.errors | ConvertTo-Json -Compress)" -ForegroundColor Red }
    Start-Sleep -Milliseconds 400
}

# Pass 4: Add as sub-issues of parent (404 on personal repos — see § 10.5)
foreach ($s in $stories) {
    $n = $issueMap[$s.id]; if (-not $n) { continue }
    try {
        Invoke-GH POST "https://api.github.com/repos/$Repo/issues/${ParentIssue}/sub_issues" @{ sub_issue_id = [int]$n } | Out-Null
        Write-Host "  [OK] #${n} -> sub-issue of #$ParentIssue" -ForegroundColor Green
    } catch { Write-Host "  [FAIL] #${n} (sub-issue): $PSItem" -ForegroundColor Red }
    Start-Sleep -Milliseconds 400
}

Write-Host "`nDone - $($issueMap.Count) story issues created" -ForegroundColor Cyan
```

> **Key syntax rules** to avoid parse errors in both inline and `& script.ps1` invocation:
> - Use `#${n}` and `#${ParentIssue}` — **not** `#$n` or `#$ParentIssue` — in
>   double-quoted strings. The colon following `#$n` in `"#$n:"` triggers Pitfall D.
> - Use `$PSItem` instead of `$_` in `catch` blocks inside strings.
> - Avoid em-dashes (`—`) in any `Write-Host` or string literal — use plain `-` instead.

### 10.4 Step 3 — Update Bodies with Full Source Content

Replace the generated bodies with the full markdown from the story files using the
existing REST update script with a custom `-IdPattern`:

```powershell
# Create the issue map (story ID -> issue number)
$map = @{
    "US-001.01" = 59; "US-001.02" = 60  # etc.
}
$map | ConvertTo-Json | Set-Content "bin\stories-issue-map.json" -Encoding utf8

# Token + headers setup (same as REST variant)
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=",""
$Headers = @{ "Authorization" = "Bearer $Token"; "Accept" = "application/vnd.github+json"; "X-GitHub-Api-Version" = "2022-11-28" }

# Update each story issue body with the full source file
$SourceDir = (Resolve-Path "docs\features\stories\F-001").Path
$IdPattern = "^(US-\d+\.\d+)"    # Matches US-001.01, US-001.02, ...

$rawMap = [System.IO.File]::ReadAllText((Resolve-Path "bin\stories-issue-map.json"),
    [System.Text.Encoding]::UTF8) | ConvertFrom-Json
$issueMap = @{}
$rawMap.PSObject.Properties | ForEach-Object { $issueMap[$_.Name] = $_.Value }

$files = Get-ChildItem -Path $SourceDir -Filter "*.md" | Sort-Object Name
foreach ($file in $files) {
    $match = [regex]::Match($file.BaseName, $IdPattern)
    if (-not $match.Success) { continue }
    $storyId  = $match.Groups[1].Value
    $issueNum = $issueMap[$storyId]
    if (-not $issueNum) { continue }

    $body    = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    $payload = @{ body = $body } | ConvertTo-Json -Depth 5 -Compress
    Invoke-RestMethod -Method PATCH `
        -Uri "https://api.github.com/repos/$Repo/issues/${issueNum}" `
        -Headers $Headers -Body $payload -ContentType "application/json; charset=utf-8" | Out-Null
    Write-Host "  [OK] #${issueNum} $storyId" -ForegroundColor Green
    Start-Sleep -Milliseconds 400
}
```

> **`IdPattern` for stories:** `"^(US-\d+\.\d+)"` matches filenames like `US-001.01.md`.
> The default pattern `"^(F-\d+)"` only matches feature files. Always pass `-IdPattern`
> when processing story files.

### 10.5 Step 4 — Link Stories to the Parent Feature Issue

#### Option A — GitHub Sub-Issues API (Teams/Enterprise only)

The GitHub REST sub-issues API (`POST /repos/{owner}/{repo}/issues/{issue_number}/sub_issues`)
returns **404 on personal repositories** — it is only available on GitHub Teams and
Enterprise plans as of February 2026. Attempting it on a personal repo:

```
{"message":"Not Found","documentation_url":"https://docs.github.com/rest/issues/sub-issues#add-sub-issue"}
```

#### Option B — Tasklist in Parent Issue Body (personal repos)

Append a `## User Stories` tasklist to the parent feature issue body. GitHub renders
tasklists as a tracked "x of N" progress indicator on the parent issue:

```powershell
$f001 = Invoke-RestMethod -Method GET `
    -Uri "https://api.github.com/repos/pslits/oai-pmh/issues/20" `
    -Headers $Headers

$storiesSection = "`n`n---`n`n## User Stories`n`n" +
    "- [ ] #59 US-001.01: Project Namespace and Directory Scaffolding`n" +
    "- [ ] #60 US-001.02: Repository Data Access Contracts`n" +
    "- [ ] #67 US-001.09: Quality Gate and Regression Verification"
    # ... all story issues

$newBody  = $f001.body + $storiesSection
$payload  = @{ body = $newBody } | ConvertTo-Json -Compress
Invoke-RestMethod -Method PATCH `
    -Uri "https://api.github.com/repos/pslits/oai-pmh/issues/20" `
    -Headers $Headers `
    -Body $payload -ContentType "application/json; charset=utf-8" | Out-Null
```

GitHub automatically renders `- [ ] #59 Story title` as a progress-tracked checklist and
creates a "tracked in" back-reference on each story issue.

#### Summary: Which option to use?

| Scenario | Recommended approach |
|----------|---------------------|
| GitHub Free / personal repo | Option B — tasklist in parent body |
| GitHub Teams or Enterprise | Option A — sub-issues API |
| Both work | Use Option A for richer UI; add Option B tasklist as fallback |

### 10.6 Step 5 — Clean Up

```powershell
Remove-Item "bin\stories-{feature}-data.json", "bin\stories-issue-map.json" -Force
```

### 10.7 Resulting GitHub State

After running the full story workflow for F-001:

| GitHub issue | Story ID | Label |
|-------------|----------|-------|
| #59 | US-001.01 | `story` |
| #60 | US-001.02 | `story` |
| ... | ... | `story` |
| #67 | US-001.09 | `story` |

- Each story body contains `**Feature:** #20 (F-001)` linking back to the parent.
- Dependency links between stories use live `#n (US-NNN.NN)` references.
- All stories are on the project board.
- F-001 (#20) body ends with a `## User Stories` tasklist showing 0/9 checked.
- Each story body contains the full markdown from its source file under
  `docs/features/stories/F-001/`.

---

## 11. Two-Pass Strategy for Dependency Links

Identical to the REST variant — the `gh` CLI does not change the fundamental problem that
issue numbers are unknown before creation.

```
Pass 1: gh issue create  →  issues #20, #21 created  →  save issueMap
Pass 2: gh issue edit    →  replace "F-001" → "#20 (F-001)" in every body
```

See [GITHUB_ISSUES_FROM_VSCODE_GUIDE.md § 9](GITHUB_ISSUES_FROM_VSCODE_GUIDE.md#9-two-pass-strategy-for-dependency-links)
for the full explanation.

---

---

## 12. gh CLI Patterns

### 10.1 Issue Operations

```powershell
# Create issue (always use --body-file for multi-line bodies)
$url    = gh issue create --repo $Repo --title "My title" --label "feature" --body-file body.txt
$number = $url -replace ".*/issues/", ""

# Edit issue body
gh issue edit 42 --repo $Repo --body-file newbody.txt

# Add labels to existing issue
gh issue edit 42 --repo $Repo --add-label "bug,needs-triage"

# Close an issue
gh issue close 42 --repo $Repo

# List issues with a label (JSON output)
gh issue list --repo $Repo --label "feature" --json number,title,state
```

### 10.2 Label Operations

```powershell
# List labels (JSON)
gh label list --repo $Repo --json name,color,description

# Create label
gh label create "feature" --repo $Repo --color "0075ca" --description "New feature"

# Check if label exists
$exists = gh label list --repo $Repo --json name |
    ConvertFrom-Json | Where-Object { $_.name -eq "feature" }
```

### 10.3 Projects v2 — `gh project` Commands

```powershell
# List projects — plain table output
gh project list --owner $Owner

# List projects — JSON output (note: --format json, NOT --json)
gh project list --owner $Owner --format json
# Returns: { "projects": [ { "number": 5, "title": "My Board", ... }, ... ] }
# Parse in PowerShell:
$projects = (gh project list --owner $Owner --format json | ConvertFrom-Json).projects

# Add an issue to a project board
gh project item-add $ProjectNumber --owner $Owner --url "https://github.com/$Repo/issues/$n"
```

> **Note:** `gh project list` uses `--format json` for JSON output, **not** `--json`.
> The JSON is wrapped in a `{ "projects": [...] }` object — access `.projects` after
> `ConvertFrom-Json` to get the array.
>
> Requires `gh` 2.31+ and the `project` scope. Run `gh auth refresh --scopes project`
> if you get a permission error.

### 10.4 Projects v2 — Fall Back to Direct GraphQL

If `gh project item-add` is not available or fails, use `gh api graphql` directly:

```powershell
# Get owner node ID and project ID
gh api graphql -f query='
  query {
    repositoryOwner(login: "owner") {
      id
      projectsV2(first: 25) {
        nodes { id title number }
      }
    }
  }
'

# Get issue node ID
gh api graphql -f query='
  query {
    repository(owner: "owner", name: "repo") {
      issue(number: 42) { id }
    }
  }
'

# Add issue to project
gh api graphql -f query='
  mutation {
    addProjectV2ItemById(input: {
      projectId: "PROJ_NODE_ID",
      contentId: "ISSUE_NODE_ID"
    }) {
      item { id }
    }
  }
'
```

In a script, pass variables via `-F` flags:

```powershell
gh api graphql `
    -f query='mutation($proj:ID!,$item:ID!){addProjectV2ItemById(input:{projectId:$proj,contentId:$item}){item{id}}}' `
    -f proj=$projectId `
    -f item=$issueNodeId
```

### 10.5 Capturing Issue Number from `gh issue create`

`gh issue create` returns the URL of the new issue on stdout:

```
https://github.com/owner/repo/issues/42
```

Extract the number with a string replace:

```powershell
$url    = gh issue create --repo $Repo --title "My issue" --body-file body.txt
$number = $url -replace ".*/issues/", ""
# $number = "42"
$issueMap[$f.id] = [int]$number
```

---

## 13. Common Pitfalls and Fixes

Most pitfalls from the REST variant still apply. The `gh` CLI removes the auth-related
ones but introduces its own. Below is the updated set.

---

### Pitfall A — `--body` breaks on multi-line text (NEW for `gh` variant)

**Symptom:** Issue created with a truncated or garbled body. PowerShell may also throw
parse errors when the body contains quotes or newlines.

**Root cause:** Passing multi-line strings via `--body "..."` in PowerShell is unreliable
due to shell escaping of newlines, backticks, and quotes.

**Fix:** Always write the body to a temp file and use `--body-file`:

```powershell
$tmpFile = [System.IO.Path]::GetTempFileName()
[System.IO.File]::WriteAllText($tmpFile, $body, [System.Text.Encoding]::UTF8)
gh issue create --repo $Repo --title $f.title --body-file $tmpFile
Remove-Item $tmpFile -Force
```

---

### Pitfall B — `gh project item-add` requires `project` scope (NEW for `gh` variant)

**Symptom:**
```
Error: your token has insufficient scopes
Required scopes: project
```

**Fix:**
```powershell
gh auth refresh --scopes project
```

Then re-run the script.

---

### Pitfall C — Em-dash and `&` in PowerShell strings

Same as Lesson 1 in the REST variant. The data file values are still interpolated into
PowerShell strings. Replace `—` with `-` and `&` with `and`.

---

### Pitfall D — `$variable:` triggers "drive letter" parse error

Same as Lesson 2 in the REST variant. Rename the variable or restructure the string.

---

### Pitfall E — Here-strings inside array/hashtable literals

Same as Lesson 3 in the REST variant. Use string concatenation.

---

### Pitfall F — HTTP 429 from too many rapid API calls

Same as Lesson 7 in the REST variant. The `gh` CLI still hits the same GitHub API rate
limits. Keep `Start-Sleep -Milliseconds 600` between issue creates.

---

### Pitfall G — `$PSScriptRoot` null when launched via `powershell -File`

Same as Lesson 9 in the REST variant. Use `$MyInvocation.MyCommand.Path`:

```powershell
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$DataFile  = Join-Path $ScriptDir "..\..\bin\features-data.json"
```

---

### Pitfall H — Background terminal output shows stale content

Same as Lesson 10 in the REST variant. Always run in foreground and capture to a log file:

```powershell
powershell -File script.ps1 *>&1 | Tee-Object -FilePath bin\create-issues.log
```

---

### Pitfall J — `Select-Object -ExpandProperty` fails on `ConvertFrom-Json` arrays in PS5.1 (NEW for `gh` variant)

**Symptom:**
```
Select-Object : Property "number" cannot be found.
```

**Root cause:** In PowerShell 5.1, `ConvertFrom-Json` returns a `PSCustomObject[]`. When
piped to `Select-Object -ExpandProperty`, the cmdlet may fail to resolve properties
depending on the pipeline context.

**Fix:** Use `ForEach-Object { $_.propertyName }` instead:

```powershell
# BAD — may fail in PS5.1
$numbers = (gh issue list ... | ConvertFrom-Json) | Select-Object -ExpandProperty number

# GOOD — reliable in PS5.1
$numbers = (gh issue list ... | ConvertFrom-Json) | ForEach-Object { $_.number }
```

Same applies when extracting nested arrays from `--format json` output:

```powershell
# BAD
$items = gh project item-list 5 ... | ConvertFrom-Json | Select-Object -ExpandProperty items

# GOOD
$items = gh project item-list 5 ... | ConvertFrom-Json | ForEach-Object { $_.items }
```

---

### What is no longer a pitfall with `gh`

| REST pitfall | Why it disappears with `gh` |
|-------------|----------------------------|
| Lesson 4 — Manual token retrieval | `gh auth login` handles all auth |
| Lesson 6 — Missing `-Compress` / charset | `gh` handles request serialisation |
| Lesson 8 — `Get-Content -Raw` PSObject | `--body-file` reads the file internally |

---

## 14. Running from the VS Code Terminal

### 12.1 Terminal Setup

Open the integrated terminal (`Ctrl+`` ` ```) and verify PowerShell and `gh`:

```powershell
$PSVersionTable.PSVersion   # Should be 5.1+
gh --version                # Should be 2.x+
gh auth status              # Should show your GitHub username
```

### 12.2 Execution Policy

```powershell
# For the current session only (safe, does not persist)
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
```

Or prefix the script with `-ExecutionPolicy Bypass`:

```powershell
powershell -ExecutionPolicy Bypass -File .\script.ps1 ...
```

### 12.3 Recommended: Capture Output to a Log File

```powershell
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues-gh\scripts\create-github-issues-gh.ps1 `
    -Repo          "owner/repo" `
    -Label         "feature" `
    -ProjectNumber 1 `
    -DataFile      "bin\features-data.json" `
    *>&1 | Tee-Object -FilePath "bin\create-issues.log"
```

### 12.4 Reviewing the Log

```powershell
# Summary: count OK and FAIL
Select-String "\[OK\]|\[FAIL\]" bin\create-issues.log | Measure-Object

# Show only failures
Select-String "\[FAIL\]" bin\create-issues.log
```

---

## 15. Validating Results

### 13.1 Check Issue Count via `gh`

```powershell
gh issue list --repo owner/repo --label feature --state open --json number,title |
    ConvertFrom-Json | Measure-Object
```

### 13.2 Spot-Check a Specific Issue

```powershell
gh issue view 20 --repo owner/repo
```

### 13.3 Check Issue Map Completeness

```powershell
$map = Get-Content bin\issue-map.json | ConvertFrom-Json
$map.PSObject.Properties | Select-Object Name, Value | Format-Table
```

### 13.4 List Project Board Items

```powershell
gh project item-list 1 --owner owner --format json |
    ConvertFrom-Json | Select-Object -ExpandProperty items |
    Select-Object title, type | Format-Table
```

---

## 16. Quick-Reference Cheat Sheet

### Full Run — All Five Steps

```powershell
# Step 1: Validate your data file
Get-Content bin\features-data.json | ConvertFrom-Json | Measure-Object

# Step 2: Create issues
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues-gh\scripts\create-github-issues-gh.ps1 `
    -Repo "owner/repo" -Label "feature" -ProjectNumber 1 `
    -DataFile "bin\features-data.json" `
    *>&1 | Tee-Object -FilePath "bin\create-issues.log"

# Step 3: Add feature issues to project board
$featureNums = (gh issue list --repo owner/repo --state open --json number,title --limit 100 |
    ConvertFrom-Json) | Where-Object { $_.title -match '^F-\d+:' } |
    ForEach-Object { $_.number }
foreach ($n in $featureNums) {
    gh project item-add 5 --owner owner --url "https://github.com/owner/repo/issues/$n" | Out-Null
    Write-Host "  [OK] #$n" -ForegroundColor Green
    Start-Sleep -Milliseconds 400
}

# Step 4 (optional): Replace bodies with full source files
powershell -ExecutionPolicy Bypass `
    -File .github\skills\github-bulk-issues-gh\scripts\update-issues-full-content-gh.ps1 `
    -Repo "owner/repo" -SourceDir "docs\features" `
    -IssueMapFile "bin\issue-map.json" `
    *>&1 | Tee-Object -FilePath "bin\update-issues.log"

# Step 5: Clean up
Remove-Item bin\features-data.json, bin\issue-map.json -Force

# --- Story workflow (after features are created) ---

# Step A: Create story issues (REST script, in-session)
Set-ExecutionPolicy Bypass -Scope Process -Force
& "bin\create-stories-f001.ps1"  # See § 10 for the full inline script template

# Step B: Update story bodies with full source content
# (run inline — see § 10.4 for the full code block)

# Step C: Link stories to parent feature issue via tasklist
# (run inline — see § 10.5 Option B for the full code block)

# Step D: Clean up story temp files
Remove-Item "bin\stories-f001-data.json", "bin\stories-issue-map.json" -Force
```

### Side-by-Side: REST vs `gh` CLI

| Task | REST variant | `gh` CLI variant |
|------|-------------|-----------------|
| Auth setup | `git credential-manager get` | `gh auth login` |
| Create issue | `Invoke-RestMethod POST` | `gh issue create --body-file` |
| Patch issue | `Invoke-RestMethod PATCH` | `gh issue edit --body-file` |
| Create label | `Invoke-RestMethod POST` | `gh label create` |
| Add to project | `Invoke-GQL addProjectV2ItemById` | `gh project item-add` |
| Read file for body | `File::ReadAllText()` | pass path to `--body-file` |

### Pitfall Quick-Reference

| # | Symptom | Root Cause | Fix |
|---|---------|------------|-----|
| A | Truncated/garbled body | `--body` with multi-line string | Use `--body-file` with temp file |
| B | `insufficient scopes` on project | Missing `project` scope | `gh auth refresh --scopes project` |
| C | `Unexpected token` parse error | Em-dash / `&` in PS string | Use JSON data file |
| D | `Variable reference is not valid` | `$var:` in string | Rename var or restructure |
| E | Cascading parse errors | Here-string inside `@()` | Use string concatenation |
| F | HTTP 429 Too Many Requests | Rapid API calls | Add `Start-Sleep` between calls |
| G | Wrong file paths | `$PSScriptRoot` null with `-File` | Use `$MyInvocation` |
| H | Stale terminal output | Background terminal buffer reuse | Capture to file |
| I | `unknown flag: --json` on `gh project list` | `gh project list` uses `--format json`, not `--json` | Change to `--format json`; access `.projects` array after parse |
| J | `Property "number" cannot be found` from `Select-Object -ExpandProperty` | PS5.1 `ConvertFrom-Json` returns `PSCustomObject[]`; `-ExpandProperty` unreliable in pipeline | Use `ForEach-Object { $_.number }` instead |
| K | `update-issues-full-content.ps1` skips all story files | Default `-IdPattern` `^(F-\d+)` only matches feature filenames | Pass `-IdPattern "^(US-\d+\.\d+)"` for story files |
| L | Sub-issues API returns 404 on personal repo | `POST /issues/{n}/sub_issues` requires GitHub Teams or Enterprise | Use Option B: append `## User Stories` tasklist to parent issue body (§ 10.5) |
| M | `repositoryOwner` GraphQL query returns empty `projectsV2.nodes` | For personal accounts `repositoryOwner` does not expose projects; only org accounts work | Use `viewer { projectsV2 ... }` instead — see § 10.3 note below |
| N | `powershell -File script.ps1` fails with `InvalidVariableReferenceWithDrive` or unterminated string | PS5.1 sub-shell chokes on `#$n:` patterns (`:` parsed as drive letter) and em-dashes (`—`) in string literals | Run script in the current session: `Set-ExecutionPolicy Bypass -Scope Process; & "script.ps1"` — also replace `#$n:` with `#${n}:` and `—` with `-` in all strings |

---

## Related Files

| File | Purpose |
|------|---------|
| [docs/GITHUB_ISSUES_FROM_VSCODE_GUIDE.md](GITHUB_ISSUES_FROM_VSCODE_GUIDE.md) | REST/GraphQL variant — no `gh` CLI required |
| [.github/skills/github-bulk-issues/references/data-schema.md](.github/skills/github-bulk-issues/references/data-schema.md) | JSON data file schema (shared by both variants) |
| [docs/GITHUB_BULK_ISSUES_LEARNINGS.md](GITHUB_BULK_ISSUES_LEARNINGS.md) | Detailed lessons learned from the REST variant |
| [.github/scripts/Get-ProjectNumber.ps1](.github/scripts/Get-ProjectNumber.ps1) | Resolves Projects v2 board number by title (supports private boards) |
| [docs/features/stories/F-001/](features/stories/F-001/) | Source markdown files for F-001 user stories (US-001.01 – US-001.09) |

---

*Last updated: February 19, 2026 — v1.3: § 10.3 full inline script template, `viewer` GraphQL pattern in Pass 3, Pitfall N (sub-shell parse errors)*
