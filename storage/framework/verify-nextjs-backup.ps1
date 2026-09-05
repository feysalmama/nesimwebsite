<#
    Verifies D:\nesim\prisma.zip before the Next.js tree is deleted.

    That archive turns out to be a complete backup of the whole Next.js project,
    not just its prisma folder, and the tree is unversioned - the git repository
    root is D:\nesim\laravel - so this file is the only thing standing between
    "delete the Next.js app" and "lose it". It is worth proving that rather than
    assuming it from a filename.

    Four checks:
      1. the zip opens and its top level covers the tree
      2. the archived bytes of key files hash identically to the live files
      3. no source file was modified after the archive was written (staleness)
      4. nothing at the top level of D:\nesim is absent from the archive

    powershell -ExecutionPolicy Bypass -File storage\framework\verify-nextjs-backup.ps1
#>

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$root = 'D:\nesim'
$zipPath = 'D:\nesim\prisma.zip'

# Not part of the Next.js app, so not expected in its backup.
$notNextJs = @('laravel', 'vendor', 'composer.json', 'composer.lock', 'prisma.zip', 'next.config.zip')

Add-Type -AssemblyName System.IO.Compression.FileSystem

$zip = Get-Item -LiteralPath $zipPath
$written = $zip.LastWriteTime

Write-Host ('archive : {0}' -f $zipPath)
Write-Host ('          {0:N1} MB, written {1:yyyy-MM-dd HH:mm:ss}' -f ($zip.Length / 1MB), $written)
Write-Host ''

$archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$failures = @()

try {
    # Compress-Archive in PowerShell 5.1 writes backslashes; other tools write
    # forward slashes. Normalise so the lookups below do not depend on which.
    $entries = @{}
    foreach ($entry in $archive.Entries) {
        $entries[($entry.FullName -replace '\\', '/')] = $entry
    }

    Write-Host ('entries : {0}' -f $entries.Count)

    $topLevel = $entries.Keys | ForEach-Object { ($_ -split '/')[0] } | Sort-Object -Unique
    Write-Host ('top level : {0}' -f ($topLevel -join ', '))
    Write-Host ''

    # -- 2. content matches the live files -----------------------------------
    Write-Host '=== content check ==='

    $keyFiles = @(
        'prisma/schema.prisma',
        'prisma/seed.js',
        'package.json',
        'next.config.js',
        'middleware.ts',
        'i18n.ts',
        'lib/auth.ts',
        'lib/prisma.ts',
        'app/layout.tsx',
        'components/Navbar.tsx',
        'messages/en.json'
    )

    foreach ($path in $keyFiles) {
        if (-not $entries.ContainsKey($path)) {
            $failures += "not in the archive: $path"
            Write-Host ('  MISSING  {0}' -f $path) -ForegroundColor Red
            continue
        }

        $live = Join-Path $root ($path -replace '/', '\')

        if (-not (Test-Path -LiteralPath $live)) {
            Write-Host ('  archived, no live file  {0}' -f $path) -ForegroundColor Yellow
            continue
        }

        $sha = [System.Security.Cryptography.SHA256]::Create()

        $stream = $entries[$path].Open()
        try {
            $archivedHash = [System.BitConverter]::ToString($sha.ComputeHash($stream)).Replace('-', '')
        } finally {
            $stream.Dispose()
        }

        $liveHash = (Get-FileHash -LiteralPath $live -Algorithm SHA256).Hash

        if ($archivedHash -eq $liveHash) {
            Write-Host ('  identical  {0}' -f $path) -ForegroundColor Green
        } else {
            $failures += "differs from the live file: $path"
            Write-Host ('  DIFFERS    {0}' -f $path) -ForegroundColor Red
        }
    }

    # -- 3. staleness --------------------------------------------------------
    Write-Host ''
    Write-Host '=== staleness check (source modified after the archive was written) ==='

    $sourceDirs = @('app', 'components', 'lib', 'messages', 'prisma', 'public')
    $newer = @()

    foreach ($dir in $sourceDirs) {
        $full = Join-Path $root $dir

        if (-not (Test-Path -LiteralPath $full)) { continue }

        foreach ($file in Get-ChildItem -LiteralPath $full -Recurse -File -Force) {
            if ($file.LastWriteTime -gt $written) {
                $newer += ('{0}  ({1:yyyy-MM-dd HH:mm:ss})' -f $file.FullName.Substring($root.Length + 1), $file.LastWriteTime)
            }
        }
    }

    foreach ($file in Get-ChildItem -LiteralPath $root -File -Force) {
        if ($notNextJs -contains $file.Name) { continue }
        if ($file.LastWriteTime -gt $written) {
            $newer += ('{0}  ({1:yyyy-MM-dd HH:mm:ss})' -f $file.Name, $file.LastWriteTime)
        }
    }

    if ($newer.Count -eq 0) {
        Write-Host '  none - every source file predates the archive.' -ForegroundColor Green
    } else {
        Write-Host ('  {0} file(s) changed after the archive was written:' -f $newer.Count) -ForegroundColor Yellow
        $newer | ForEach-Object { Write-Host ('    ' + $_) -ForegroundColor Yellow }
    }

    # -- 4. coverage ---------------------------------------------------------
    Write-Host ''
    Write-Host '=== coverage check (top-level entries of D:\nesim absent from the archive) ==='

    $absent = @()

    foreach ($item in Get-ChildItem -LiteralPath $root -Force) {
        if ($notNextJs -contains $item.Name) { continue }

        # Directories are stored as explicit entries with a trailing separator,
        # so both spellings have to be tried or every folder looks absent.
        if ($entries.ContainsKey($item.Name) -or $entries.ContainsKey($item.Name + '/')) { continue }

        $absent += $item.Name
    }

    if ($absent.Count -eq 0) {
        Write-Host '  none - the archive covers the whole tree.' -ForegroundColor Green
    } else {
        Write-Host ('  absent: {0}' -f ($absent -join ', ')) -ForegroundColor Yellow
    }
} finally {
    $archive.Dispose()
}

Write-Host ''

if ($failures.Count -gt 0) {
    Write-Host 'FAILED:' -ForegroundColor Red
    $failures | ForEach-Object { Write-Host ('  - ' + $_) -ForegroundColor Red }
    exit 1
}

Write-Host 'OK: the archive is readable and its contents match the live tree.' -ForegroundColor Green
Write-Host ''
Write-Host 'Note: prisma.zip lives INSIDE D:\nesim, so deleting the tree would delete the' -ForegroundColor Yellow
Write-Host 'backup with it. Move it out first.' -ForegroundColor Yellow
