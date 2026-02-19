<#
.SYNOPSIS
    Retrieve a Projects v2 numeric project number by title (supports private boards).

.DESCRIPTION
    Uses `gh project list --json number,title` (no GraphQL, no scope issues) to
    look up the numeric project number by title. Falls back to case-insensitive
    substring matching if an exact match is not found.

    Requires `gh auth login` with at least the `read:project` scope:
        gh auth refresh -s read:project

.PARAMETER Repo
    Repository in "owner/name" format. Used to derive the owner.

.PARAMETER ProjectTitle
    Title of the project board to find (exact match first, then case-insensitive).

.PARAMETER Limit
    Maximum number of projects to retrieve (default: 100).

.EXAMPLE
    .\Get-ProjectNumber.ps1 -Repo "pslits/oai-pmh" -ProjectTitle "OAI Repository"

#>

param(
    [Parameter(Mandatory=$true)][string] $Repo,
    [Parameter(Mandatory=$true)][string] $ProjectTitle,
    [int] $Limit = 100
)

if (-not (Get-Command gh -ErrorAction SilentlyContinue)) {
    Write-Error "gh CLI not found. Install and run 'gh auth login' first."
    exit 2
}

$parts = $Repo.Split('/')
if ($parts.Length -ne 2) {
    Write-Error "Repo must be in 'owner/name' format."
    exit 2
}

$Owner = $parts[0]

# Use gh project list (no GraphQL - avoids Int! type-casting and scope issues)
$raw = gh project list --owner $Owner -L $Limit --format json 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Error "gh project list failed: $raw"
    exit 2
}

$projects = ($raw | ConvertFrom-Json).projects

# 1. Exact match
$match = $projects | Where-Object { $_.title -eq $ProjectTitle } | Select-Object -First 1

# 2. Case-insensitive exact match
if (-not $match) {
    $match = $projects | Where-Object { $_.title -ieq $ProjectTitle } | Select-Object -First 1
}

# 3. Case-insensitive substring match (fallback)
if (-not $match) {
    $match = $projects | Where-Object { $_.title -ilike "*$ProjectTitle*" } | Select-Object -First 1
    if ($match) {
        Write-Host "No exact match - using closest match: '$($match.title)'" -ForegroundColor Yellow
    }
}

if ($match) {
    Write-Output $match.number
    exit 0
} else {
    Write-Host "Available projects for '$Owner':" -ForegroundColor Yellow
    $projects | ForEach-Object { Write-Host "  $($_.number)  $($_.title)" }
    Write-Error "Project titled '$ProjectTitle' not found for owner '$Owner'."
    exit 3
}
