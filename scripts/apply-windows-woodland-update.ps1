param(
    [Parameter(Mandatory = $true)]
    [string]$ProjectPath
)
$ErrorActionPreference = 'Stop'
$project = (Resolve-Path -LiteralPath $ProjectPath).Path
if (-not (Test-Path -LiteralPath (Join-Path $project 'artisan')) -or -not (Test-Path -LiteralPath (Join-Path $project 'compose.yaml'))) {
    throw 'Choose the existing Laravel project folder containing artisan and compose.yaml.'
}
$manifestPath = Join-Path $PSScriptRoot 'update-manifest.json'
if (-not (Test-Path -LiteralPath $manifestPath)) {
    throw 'Run the Apply-Woodland-Update.ps1 supplied inside the extracted woodland update ZIP.'
}
$manifest = Get-Content -LiteralPath $manifestPath -Raw | ConvertFrom-Json
if (-not $manifest.files -or $manifest.files.Count -lt 1) { throw 'The update manifest is empty.' }
$payload = Join-Path $PSScriptRoot 'payload'
$protectedHashes = @{}
foreach ($relative in @('.env', 'database/database.sqlite')) {
    $file = Join-Path $project $relative
    if (Test-Path -LiteralPath $file) { $protectedHashes[$relative] = (Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash }
}
# Validate the entire allowlist and every payload hash before replacing any code.
foreach ($item in $manifest.files) {
    $relative = [string]$item.path
    if ($relative -match '(^/|^[A-Za-z]:|\\|(^|/)\.\.(/|$))' -or $relative -notmatch '^(app/|resources/|public/images/woodland/|public/fonts/storybook/|docs/|tests/|scripts/|README\.md$)') {
        throw "Unsafe or unexpected update path: $relative"
    }
    if ($relative -match '(^|/)(\.env[^/]*|vendor|node_modules|storage|database|auth\.json)(/|$)') {
        throw "Protected path in update: $relative"
    }
    $source = Join-Path $payload $relative
    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) { throw "Missing update file: $relative" }
    if ((Get-FileHash -LiteralPath $source -Algorithm SHA256).Hash -ne $item.sha256) { throw "Update checksum mismatch: $relative" }
    $destination = Join-Path $project $relative
    # Refuse reparse points rather than following a link outside the project.
    $ancestor = $destination
    while ($ancestor -and $ancestor.StartsWith($project, [System.StringComparison]::OrdinalIgnoreCase)) {
        if (Test-Path -LiteralPath $ancestor) {
            if ((Get-Item -LiteralPath $ancestor -Force).Attributes -band [IO.FileAttributes]::ReparsePoint) { throw "Linked update path is unsupported: $ancestor" }
        }
        if ($ancestor -eq $project) { break }
        $ancestor = Split-Path -Parent $ancestor
    }
}
$backup = Join-Path (Split-Path -Parent $project) ('Life-of-Pathi-code-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
foreach ($item in $manifest.files) {
    $destination = Join-Path $project $item.path
    if (Test-Path -LiteralPath $destination -PathType Leaf) {
        $old = Join-Path $backup $item.path
        New-Item -ItemType Directory -Path (Split-Path -Parent $old) -Force | Out-Null
        Copy-Item -LiteralPath $destination -Destination $old -Force
    }
    New-Item -ItemType Directory -Path (Split-Path -Parent $destination) -Force | Out-Null
    Copy-Item -LiteralPath (Join-Path $payload $item.path) -Destination $destination -Force
}
foreach ($relative in $protectedHashes.Keys) {
    if ((Get-FileHash -LiteralPath (Join-Path $project $relative) -Algorithm SHA256).Hash -ne $protectedHashes[$relative]) { throw "Unexpected protected-file change: $relative" }
}
Write-Host 'Woodland code and artwork updated. Your environment, database and uploads were not copied or replaced.'
Write-Host "Previous code backed up to: $backup"
Write-Host 'Return to your project folder and follow the rebuild/start commands in docs/windows-update-woodland.md.'
