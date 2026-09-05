param(
    [string]$Zip = 'D:\nesim-nextjs-source.zip',
    [string]$Dest = 'D:\nesim\laravel\storage\framework\nextjs-src'
)

# Pulls the public page sources and the shared components out of the recovery
# archive so the port can be written against what the site actually rendered.
# Everything under app/[locale] plus components/ plus lib/ - the models and
# specs already cover the data, these cover the presentation.

if (-not (Test-Path -LiteralPath $Zip)) {
    Write-Host "MISSING archive: $Zip"
    exit 1
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::OpenRead($Zip)

if (-not (Test-Path -LiteralPath $Dest)) {
    New-Item -ItemType Directory -Path $Dest -Force | Out-Null
}

# Square brackets are a -like character class, so [locale] would match any one
# of l/o/c/a/e rather than the literal directory. Backtick-escape them.
$wanted = @(
    'app/`[locale`]/*',
    'components/*',
    'lib/*',
    'app/api/*'
)

$count = 0
foreach ($entry in $archive.Entries) {
    if ($entry.Length -eq 0) { continue }

    $match = $false
    foreach ($pattern in $wanted) {
        if ($entry.FullName -like $pattern) { $match = $true; break }
    }
    if (-not $match) { continue }

    $out = Join-Path $Dest $entry.FullName
    $dir = Split-Path -Parent $out
    if (-not (Test-Path -LiteralPath $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }

    [System.IO.Compression.ZipFileExtensions]::ExtractToFile($entry, $out, $true)
    $count++
}

$archive.Dispose()
Write-Host "extracted $count files to $Dest"
Get-ChildItem -LiteralPath (Join-Path $Dest 'app\[locale]') -Directory | ForEach-Object { Write-Host "  $($_.Name)" }
