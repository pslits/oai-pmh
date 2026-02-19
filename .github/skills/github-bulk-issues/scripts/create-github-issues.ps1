<#
.SYNOPSIS
    Creates GitHub issues in bulk from a JSON data file, assigns a label, cross-links
    dependencies, and adds every issue to a GitHub Projects v2 board.

.DESCRIPTION
    Reads features-data.json (must exist alongside this script or at -DataFile path).
    For each entry:
      1. Creates an issue with the specified label.
      2. After all issues are created, patches every body to replace "F-xxx" IDs
         with live "#n (F-xxx)" issue links (two-pass strategy).
      3. Adds every issue to the named GitHub Projects v2 board via GraphQL.
    Saves issue-map.json next to the data file on completion.

.PARAMETER Repo
    GitHub repository in "owner/name" format.

.PARAMETER Label
    Label name to apply to every issue. Created with a default colour if it doesn't exist.

.PARAMETER ProjectName
    GitHub Projects v2 board name. Created if it doesn't exist.

.PARAMETER DataFile
    Path to the JSON data file. Defaults to features-data.json next to this script.

.EXAMPLE
    .\create-github-issues.ps1 -Repo "pslits/oai-pmh" -Label "feature" -ProjectName "OAI Repository"
#>
param(
    [Parameter(Mandatory)] [string] $Repo,
    [Parameter(Mandatory)] [string] $Label,
    [Parameter(Mandatory)] [string] $ProjectName,
    [string] $DataFile = (Join-Path (Split-Path -Parent $MyInvocation.MyCommand.Path) "features-data.json")
)

# ── Token ───────────────────────────────────────────────────────────────────────
$raw = (echo "protocol=https`nhost=github.com`n" | git credential-manager get 2>$null)
$Token = ($raw | Select-String "^password=").Line -replace "^password=", ""
if (-not $Token) { Write-Error "No GitHub token found. Authenticate with Git first."; exit 1 }

$Owner = $Repo.Split("/")[0]
$RepoName = $Repo.Split("/")[1]

$Headers = @{
    "Authorization"        = "Bearer $Token"
    "Accept"               = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

function Invoke-GH {
    param([string]$Method, [string]$Url, [object]$Body = $null)
    $p = @{ Method = $Method; Uri = $Url; Headers = $Headers }
    if ($null -ne $Body) {
        $p.Body = ($Body | ConvertTo-Json -Depth 10 -Compress)
        $p.ContentType = "application/json; charset=utf-8"
    }
    return Invoke-RestMethod @p
}

function Invoke-GQL {
    param([string]$Query)
    return (Invoke-GH POST "https://api.github.com/graphql" @{ query = $Query })
}

# ── Load data ───────────────────────────────────────────────────────────────────
$features = [System.IO.File]::ReadAllText($DataFile, [System.Text.Encoding]::UTF8) | ConvertFrom-Json
Write-Host "Loaded $($features.Count) features from $DataFile" -ForegroundColor Cyan

# ── Label ────────────────────────────────────────────────────────────────────────
Write-Host "`n=== Label '$Label' ===" -ForegroundColor Cyan
try {
    Invoke-GH GET "https://api.github.com/repos/$Repo/labels/$Label" | Out-Null
    Write-Host "  Already exists" -ForegroundColor Yellow
}
catch {
    Invoke-GH POST "https://api.github.com/repos/$Repo/labels" @{
        name = $Label; color = "0075ca"; description = "New feature or request"
    } | Out-Null
    Write-Host "  Created" -ForegroundColor Green
}

# ── Project ───────────────────────────────────────────────────────────────────────
Write-Host "`n=== Project '$ProjectName' ===" -ForegroundColor Cyan
$ownerGql = "query { repositoryOwner(login: `"$Owner`") { id projectsV2(first: 25) { nodes { id title number } } } }"
$ownerData = Invoke-GQL $ownerGql
$ownerId = $ownerData.data.repositoryOwner.id
$project = $ownerData.data.repositoryOwner.projectsV2.nodes |
Where-Object { $_.title -eq $ProjectName } |
Select-Object -First 1

if (-not $project) {
    $createGql = "mutation { createProjectV2(input: {ownerId: `"$ownerId`", title: `"$ProjectName`"}) { projectV2 { id title number } } }"
    $project = (Invoke-GQL $createGql).data.createProjectV2.projectV2
    Write-Host "  Created (number: $($project.number))" -ForegroundColor Green
}
else {
    Write-Host "  Found (number: $($project.number))" -ForegroundColor Yellow
}
$projectId = $project.id

# ── Pass 1: Create issues ─────────────────────────────────────────────────────────
Write-Host "`n=== Creating $($features.Count) issues ===" -ForegroundColor Cyan
$issueMap = @{}

foreach ($f in $features) {
    $blockedByStr = if ($f.blockedBy.Count -gt 0) { $f.blockedBy -join ", " } else { "(none)" }
    $blocksStr = if ($f.blocks.Count -gt 0) { $f.blocks -join ", " }    else { "(none)" }

    $body = "**Feature ID:** $($f.id)`n" +
    "**Priority:** $($f.priority)`n" +
    "**Phase:** $($f.phase)`n" +
    "**Bounded Context:** $($f.context)`n`n" +
    "## Description`n`n$($f.description)`n`n" +
    "## Dependencies`n`n" +
    "**Blocked by:** $blockedByStr`n`n" +
    "**Blocks:** $blocksStr"

    try {
        $issue = Invoke-GH POST "https://api.github.com/repos/$Repo/issues" @{
            title  = $f.title
            body   = $body
            labels = @($Label)
        }
        $issueMap[$f.id] = $issue.number
        Write-Host "  [OK] #$($issue.number) - $($f.title)" -ForegroundColor Green
    }
    catch {
        Write-Host "  [FAIL] $($f.id): $_" -ForegroundColor Red
    }
    Start-Sleep -Milliseconds 600
}

# Save map
$mapFile = Join-Path (Split-Path $DataFile) "issue-map.json"
$issueMap | ConvertTo-Json | Set-Content $mapFile -Encoding utf8
Write-Host "Issue map saved to $mapFile" -ForegroundColor Cyan

# ── Pass 2: Patch bodies with live #links ────────────────────────────────────────
Write-Host "`n=== Updating dependency links ===" -ForegroundColor Cyan
foreach ($f in $features) {
    $n = $issueMap[$f.id]
    if (-not $n) { continue }

    $blockedByLinks = if ($f.blockedBy.Count -gt 0) {
        ($f.blockedBy | ForEach-Object { $m = $issueMap[$_]; if ($m) { "#$m ($_)" } else { $_ } }) -join ", "
    }
    else { "(none)" }

    $blocksLinks = if ($f.blocks.Count -gt 0) {
        ($f.blocks | ForEach-Object { $m = $issueMap[$_]; if ($m) { "#$m ($_)" } else { $_ } }) -join ", "
    }
    else { "(none)" }

    $newBody = "**Feature ID:** $($f.id)`n" +
    "**Priority:** $($f.priority)`n" +
    "**Phase:** $($f.phase)`n" +
    "**Bounded Context:** $($f.context)`n`n" +
    "## Description`n`n$($f.description)`n`n" +
    "## Dependencies`n`n" +
    "**Blocked by:** $blockedByLinks`n`n" +
    "**Blocks:** $blocksLinks"

    try {
        Invoke-GH PATCH "https://api.github.com/repos/$Repo/issues/$n" @{ body = $newBody } | Out-Null
        Write-Host "  [OK] Updated #$n ($($f.id))" -ForegroundColor Green
    }
    catch {
        Write-Host "  [FAIL] #$n: $_" -ForegroundColor Red
    }
    Start-Sleep -Milliseconds 400
}

# ── Pass 3: Add to project ────────────────────────────────────────────────────────
Write-Host "`n=== Adding to project '$ProjectName' ===" -ForegroundColor Cyan
foreach ($f in $features) {
    $n = $issueMap[$f.id]
    if (-not $n) { continue }

    $nodeGql = "query { repository(owner: `"$Owner`", name: `"$RepoName`") { issue(number: $n) { id } } }"
    $nodeId = (Invoke-GQL $nodeGql).data.repository.issue.id

    $addGql = "mutation { addProjectV2ItemById(input: {projectId: `"$projectId`", contentId: `"$nodeId`"}) { item { id } } }"
    try {
        Invoke-GQL $addGql | Out-Null
        Write-Host "  [OK] Added #$n ($($f.id))" -ForegroundColor Green
    }
    catch {
        Write-Host "  [FAIL] #$n: $_" -ForegroundColor Red
    }
    Start-Sleep -Milliseconds 400
}

Write-Host "`n=== DONE — $($issueMap.Count) issues created ===" -ForegroundColor Cyan
