<#
.SYNOPSIS
    Replaces every GitHub issue body with the complete text of the corresponding source file.

.DESCRIPTION
    Given a directory of source files (e.g., feature spec markdown files) and an issue-map
    (JSON mapping ID -> issue number), this script reads each file using
    [System.IO.File]::ReadAllText() — NOT Get-Content -Raw (see PITFALL note) — and PATCHes
    the corresponding GitHub issue with the full content.

    PITFALL: Get-Content -Raw returns a PSObject carrying PowerShell filesystem metadata.
    When ConvertTo-Json serialises that object, it emits thousands of type-name lines from
    the System.Management.Automation assembly, causing HTTP 422 from GitHub API. Always use
    [System.IO.File]::ReadAllText() to get a plain string.

.PARAMETER Repo
    GitHub repository in "owner/name" format.

.PARAMETER SourceDir
    Directory containing the source files (one per issue).

.PARAMETER FilePattern
    Glob pattern for source files (default: "*.md").

.PARAMETER IdPattern
    Regex to extract the issue ID from the filename.
    Default: "^(F-\d+)" — matches "F-001" from "F-001_SOME_FEATURE.md".

.PARAMETER IssueMapFile
    Path to a JSON file mapping ID strings to issue numbers, e.g. {"F-001":20,"F-002":21}.
    If omitted, the script looks for issue-map.json in the same directory as the script.

.EXAMPLE
    .\update-issues-full-content.ps1 `
        -Repo       "pslits/oai-pmh" `
        -SourceDir  "C:\project\docs\features" `
        -IssueMapFile "C:\project\bin\issue-map.json"
#>
param(
    [Parameter(Mandatory)] [string] $Repo,
    [Parameter(Mandatory)] [string] $SourceDir,
    [string] $FilePattern  = "*.md",
    [string] $IdPattern    = "^(F-\d+)",
    [string] $IssueMapFile = (Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Path) "issue-map.json")
)

# ── Token ───────────────────────────────────────────────────────────────────────
$raw   = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
if (-not $Token) { Write-Error "No GitHub token found."; exit 1 }

$Headers = @{
    "Authorization"        = "Bearer $Token"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

# ── Load issue map ───────────────────────────────────────────────────────────────
$rawMap  = [System.IO.File]::ReadAllText($IssueMapFile, [System.Text.Encoding]::UTF8) | ConvertFrom-Json
$issueMap = @{}
$rawMap.PSObject.Properties | ForEach-Object { $issueMap[$_.Name] = $_.Value }

Write-Host "Loaded issue map with $($issueMap.Count) entries from $IssueMapFile" -ForegroundColor Cyan

# ── Process files ────────────────────────────────────────────────────────────────
$files = Get-ChildItem -Path $SourceDir -Filter $FilePattern | Sort-Object Name
Write-Host "Updating $($files.Count) files in $SourceDir..." -ForegroundColor Cyan

foreach ($file in $files) {
    # Extract ID from filename
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

    # KEY: use File::ReadAllText, NOT Get-Content -Raw
    # Get-Content -Raw returns a PSObject with PS metadata that ConvertTo-Json will serialise.
    $body = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)

    $payload = @{ body = $body } | ConvertTo-Json -Depth 5 -Compress

    try {
        $response = Invoke-RestMethod `
            -Method      PATCH `
            -Uri         "https://api.github.com/repos/$Repo/issues/$issueNum" `
            -Headers     $Headers `
            -Body        $payload `
            -ContentType "application/json; charset=utf-8"

        Write-Host "  [OK] #$issueNum $featureId - $($response.title)" -ForegroundColor Green
    } catch {
        Write-Host "  [FAIL] #$issueNum $featureId : $_" -ForegroundColor Red
    }

    Start-Sleep -Milliseconds 400
}

Write-Host "`nDone." -ForegroundColor Cyan
