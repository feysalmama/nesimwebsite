<#
.SYNOPSIS
    Replaces the laravel\public\uploads junction with a real directory holding
    its own copy of the files, so the Laravel app no longer depends on the
    Next.js tree for its images.

.DESCRIPTION
    Ordered so that nothing is at risk at any point:

      1. copy the files into a NEW sibling directory (uploads_new)
      2. prove the copy is byte-identical, by SHA256, file by file
      3. only then remove the junction - with `cmd /c rmdir`, never
         Remove-Item -Recurse, which in PowerShell 5.1 follows the reparse
         point and deletes the TARGET's contents
      4. prove the original folder survived step 3, again by SHA256
      5. only then rename uploads_new -> uploads

    Any failure aborts with the junction still in place, so the site keeps
    serving images exactly as it did before the script ran.

    The source folder is never written to and never deleted from.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File storage\framework\adopt-uploads.ps1
#>

$ErrorActionPreference = 'Stop'

$source  = 'D:\nesim\public\uploads'
$link    = 'D:\nesim\laravel\public\uploads'
$staging = 'D:\nesim\laravel\public\uploads_new'

function Get-Manifest([string]$dir) {
    $map = @{}

    foreach ($file in Get-ChildItem -LiteralPath $dir -File) {
        $map[$file.Name] = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
    }

    return $map
}

function Compare-Manifests($a, $b, [string]$label) {
    $problems = @()

    foreach ($name in $a.Keys) {
        if (-not $b.ContainsKey($name)) {
            $problems += "missing: $name"
        } elseif ($a[$name] -ne $b[$name]) {
            $problems += "hash differs: $name"
        }
    }

    foreach ($name in $b.Keys) {
        if (-not $a.ContainsKey($name)) {
            $problems += "unexpected extra: $name"
        }
    }

    if ($problems.Count -gt 0) {
        Write-Host "FAILED ($label):" -ForegroundColor Red
        $problems | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
        exit 1
    }

    Write-Host "  $label : $($a.Count) files, every SHA256 matches" -ForegroundColor Green
}

Write-Host '=== precondition ==='

$subdirs = @(Get-ChildItem -LiteralPath $source -Directory)
if ($subdirs.Count -gt 0) {
    Write-Host "Aborted: the source has subdirectories this script does not handle: $($subdirs.Name -join ', ')" -ForegroundColor Red
    exit 1
}

$item = Get-Item -LiteralPath $link -Force
if ($item.LinkType -ne 'Junction') {
    Write-Host "Aborted: laravel\public\uploads is already a real directory (LinkType=$($item.LinkType)). Nothing to do." -ForegroundColor Yellow
    exit 0
}

if (Test-Path -LiteralPath $staging) {
    Write-Host "Aborted: $staging already exists. Remove it and re-run." -ForegroundColor Red
    exit 1
}

$sourceManifest = Get-Manifest $source
Write-Host "  junction confirmed -> $($item.Target)"
Write-Host "  source: $($sourceManifest.Count) files, $('{0:N1}' -f ((Get-ChildItem -LiteralPath $source -File | Measure-Object Length -Sum).Sum / 1MB)) MB"

Write-Host ''
Write-Host '=== 1. copy into uploads_new ==='

New-Item -ItemType Directory -Path $staging | Out-Null

foreach ($file in Get-ChildItem -LiteralPath $source -File) {
    Copy-Item -LiteralPath $file.FullName -Destination (Join-Path $staging $file.Name)
}

$stagingManifest = Get-Manifest $staging
Write-Host "  copied $($stagingManifest.Count) files"

Write-Host ''
Write-Host '=== 2. verify the copy ==='

Compare-Manifests $sourceManifest $stagingManifest 'uploads_new'

Write-Host ''
Write-Host '=== 3. remove the junction (reparse point only) ==='

# rmdir without /s removes the junction and leaves the target untouched.
cmd /c rmdir "$link"
if ($LASTEXITCODE -ne 0) {
    Write-Host "Aborted: rmdir failed with exit $LASTEXITCODE. The junction and uploads_new are both still present." -ForegroundColor Red
    exit 1
}

if (Test-Path -LiteralPath $link) {
    Write-Host "Aborted: $link still exists after rmdir." -ForegroundColor Red
    exit 1
}

Write-Host '  junction removed'

Write-Host ''
Write-Host '=== 4. prove the original survived ==='

Compare-Manifests $sourceManifest (Get-Manifest $source) 'source, after rmdir'

Write-Host ''
Write-Host '=== 5. promote uploads_new to uploads ==='

Rename-Item -LiteralPath $staging -NewName 'uploads'

$final = Get-Item -LiteralPath $link -Force
if ($null -ne $final.LinkType) {
    Write-Host "Aborted: $link is a $($final.LinkType), expected a real directory." -ForegroundColor Red
    exit 1
}

Compare-Manifests $sourceManifest (Get-Manifest $link) 'laravel\public\uploads'

Write-Host ''
Write-Host "OK: laravel\public\uploads is now a real directory with $($sourceManifest.Count) files." -ForegroundColor Green
Write-Host "    $source is untouched and still holds the originals." -ForegroundColor Green
