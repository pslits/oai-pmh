# GitHub Bulk Issue Creation — Lessons Learned

**Date:** February 19, 2026  
**Context:** Bulk-creating 39 GitHub feature issues from markdown files with labels,
project board assignment, and inter-issue dependency cross-links.  
**Repo:** `pslits/oai-pmh`  
**Tools used:** PowerShell 5.1, GitHub REST API, GitHub GraphQL API (Projects v2)

---

## Lesson 1 — Em-dash and ampersand in PowerShell string literals

**Problem:** Unicode em-dash `—` (`\u2014`) and `&` inside double-quoted multi-line strings
(especially in hashtable values or here-strings inside arrays) cause parse errors:

```
Unexpected token 'Foundation' in expression or statement.
The ampersand (&) character is not allowed.
```

**Fix:** Strip em-dashes; use plain `-` instead. For `&` use the word "and". Move all
user-visible strings to a separate UTF-8 JSON file and load with `ConvertFrom-Json` —
PowerShell parses JSON cleanly regardless of special characters.

---

## Lesson 2 — `$variable:` triggers "drive letter" parse error

**Problem:** Inside a double-quoted string, `$myNumber:` is interpreted as a PS-drive
reference (like `$env:PATH`):

```
Variable reference is not valid. ':' was not followed by a valid variable name character.
```

**Fix:** Rename the variable or restructure the string so no variable is immediately
followed by `:`.

```powershell
# BAD
"[FAIL] Update #$myNumber: $_"

# GOOD
$err = $_
"[FAIL] Update issue $myNumber - $err"
```

---

## Lesson 3 — Here-strings cannot appear inside array or hashtable literals

**Problem:** `@"..."@` here-strings cannot be nested inside `@(...)` or `@{...}` literals,
causing cascading parse errors across the entire block.

**Fix:** Build multi-line strings via concatenation:

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

## Lesson 4 — Retrieve GitHub token without `gh` CLI

When the `gh` CLI is not installed, retrieve the stored token directly from the Windows
credential manager:

```powershell
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
```

Works on any Windows machine where the developer has previously authenticated with GitHub
via Git.

---

## Lesson 5 — GitHub Projects v2 requires GraphQL; no REST endpoint exists

All project board operations (find, create, add items) must go through the GraphQL API.
There is no REST v2 project equivalent.

```powershell
# Find owner and existing projects
$gql = 'query { repositoryOwner(login: "owner") { id projectsV2(first: 25) { nodes { id title number } } } }'

# Create project
$gql = 'mutation { createProjectV2(input: {ownerId: "NODE_ID", title: "My Project"}) { projectV2 { id } } }'

# Get issue node ID
$gql = 'query { repository(owner: "owner", name: "repo") { issue(number: 1) { id } } }'

# Add issue to project
$gql = 'mutation { addProjectV2ItemById(input: {projectId: "PROJ_ID", contentId: "ISSUE_ID"}) { item { id } } }'
```

---

## Lesson 6 — REST helper pattern for GitHub API calls

```powershell
function Invoke-GH {
    param([string]$Method, [string]$Url, [object]$Body = $null)
    $params = @{ Method = $Method; Uri = $Url; Headers = $Headers }
    if ($null -ne $Body) {
        $params.Body        = ($Body | ConvertTo-Json -Depth 10 -Compress)
        $params.ContentType = "application/json; charset=utf-8"
    }
    return Invoke-RestMethod @params
}
```

Always use `-Compress` on `ConvertTo-Json` to keep request bodies on a single line and
avoid edge-case length issues. Always set `charset=utf-8` in `ContentType`.

---

## Lesson 7 — Add rate-limiting courtesy sleeps

Insert short sleeps between API calls to avoid HTTP 429 responses on repos with many
issues:

```powershell
Start-Sleep -Milliseconds 500   # between issue-creation POSTs
Start-Sleep -Milliseconds 400   # between PATCH / GraphQL calls
```

---

## Lesson 8 — `Get-Content -Raw` returns a PSObject, not a plain string

**Problem:** `Get-Content -Raw` returns a `PSObject` carrying PowerShell file metadata
(PSDrive, PSPath, PSChildName, and dozens of `System.Management.Automation` assembly type
names). When passed to `ConvertTo-Json`, all metadata is serialised into the request body,
making it hundreds of KB and causing a GitHub API HTTP 422:

```
{"message":"Invalid request.\n\nFor 'properties/body',
{\"value\" => \"# Feature F-001...\", \"PSPath\" => \"C:\\\\...\",
\"PSDrive\" => {\"Name\" => \"C\", ...}
```

**Fix:** Use the .NET `File` class to get a guaranteed plain `string`:

```powershell
# BAD
$body = Get-Content $file.FullName -Raw -Encoding utf8

# GOOD
$body = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
```

---

## Lesson 9 — `$PSScriptRoot` is empty when launched via `powershell -File`

**Problem:** `$PSScriptRoot` is only populated reliably when dot-sourcing or using
modules. When launched as `powershell -File path\to\script.ps1` from a different
directory, it can be `$null`, causing `Join-Path $PSScriptRoot "..."` to produce wrong
paths.

**Fix:**

```powershell
# BAD (unreliable with -File)
$FeaturesDir = Join-Path $PSScriptRoot "..\docs\features"

# GOOD
$ScriptDir   = Split-Path -Parent $MyInvocation.MyCommand.Path
$FeaturesDir = Join-Path $ScriptDir "..\docs\features"

# ALSO GOOD for non-interactive automation
$FeaturesDir = "C:\absolute\path\to\docs\features"
```

---

## Lesson 10 — Background terminal output can show stale content

**Problem:** When reusing a background terminal (VS Code Copilot `isBackground: true`),
`get_terminal_output` may show the tail of a *previous* command's buffer. The tool may
also report "PREVIOUS OUTPUT TRUNCATED" with stale Automation assembly type names rather
than the script's actual output.

**Fix:** For scripts that must be verified, either:

- Run in a **foreground** terminal (`isBackground: false`) and capture to a log file:

  ```powershell
  powershell -File script.ps1 *>&1 | Out-File log.txt
  Get-Content log.txt | Select-String "\[OK\]|\[FAIL\]"
  ```

- Or validate by **querying the API directly** after the run rather than relying on
  terminal output.

---

## Two-pass strategy for dependency cross-links

Issue numbers are only known after creation, so dependency references must be resolved in
a second pass:

1. **Pass 1 (create):** Write plain `F-xxx` IDs in the `blockedBy`/`blocks` fields.
2. **Pass 2 (update):** After all issues exist, build a `$issueMap` (`F-xxx → #n`) and
   re-`PATCH` every issue body replacing `F-xxx` with `#n (F-xxx)`.

This avoids complex ordering logic and keeps the creation script simple.

---

## Quick-reference: what these lessons fixed

| # | Error / symptom | Root cause | Fix |
|---|-----------------|------------|-----|
| 1 | `Unexpected token` parse error | Em-dash / `&` in PS string | Use JSON data file |
| 2 | `Variable reference is not valid` | `$var:` in string | Rename var or restructure string |
| 3 | Cascading parse errors in hashtable | Here-string inside `@()` | Use string concatenation |
| 4 | `gh` not found | CLI not installed | Use `git credential-manager get` |
| 5 | `404` on project REST endpoint | Projects v2 is GraphQL-only | Use GraphQL mutations |
| 6 | Malformed JSON body | Missing `-Compress` / charset | Add `-Compress` and `charset=utf-8` |
| 7 | HTTP 429 | Too many rapid API calls | Add `Start-Sleep` between calls |
| 8 | HTTP 422, body contains `PSPath` | `Get-Content -Raw` returns PSObject | Use `File::ReadAllText()` |
| 9 | Wrong file paths | `$PSScriptRoot` null with `-File` | Use `$MyInvocation` or absolute paths |
| 10 | Stale terminal output | Background terminal buffer reuse | Capture to file or query API directly |
