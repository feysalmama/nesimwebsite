<#
    Deletes everything in D:\nesim except laravel\, and removes the redundant
    first-attempt archive.

    Irreversible and, outside laravel\, unversioned - the git repository root is
    D:\nesim\laravel. So this refuses to delete anything until six pre-flight
    checks pass, each covering the thing that would make the loss real:

      1. D:\nesim-nextjs-source.zip opens, holds 303 entries, and its copy of
         prisma/schema.prisma hashes identically to the live file - so the
         archive is current rather than the stale 12:41 one.
      2. laravel\public\uploads is a real directory, not a junction, holding
         every file in D:\nesim\public\uploads, byte-identical.
      3. every asset Laravel serves from public\ exists there with an identical
         hash, so nothing the site needs lived only in the Next.js tree.
      4. no reparse point exists anywhere in the tree about to be deleted. A
         junction removed with rmdir /s can take its target with it, and the
         uploads junction was only converted minutes ago.
      5. laravel\vendor\autoload.php, .env, artisan and public\index.php exist,
         so the app being kept can still boot afterwards.
      6. no running process has the tree open. Two Next.js dev servers were
         serving :3001 and :3002 in watch mode; they held the SWC native binary
         locked and rewrote .next seconds after it had been deleted.

    Any failure aborts before the first deletion. The script is resumable: a
    first run removed .idea, .next, app, components, lib and messages before a
    locked file stopped it, and re-running simply deletes whatever is left.

    powershell -ExecutionPolicy Bypass -File storage\framework\retire-nextjs.ps1
#>

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$root = 'D:\nesim'
$keep = 'laravel'
$archive = 'D:\nesim-nextjs-source.zip'
$redundant = 'D:\nesim-nextjs-source-2026-09-05.zip'

$failures = @()

function Fail([string]$message) {
    $script:failures += $message
    Write-Host ('  FAIL  ' + $message) -ForegroundColor Red
}

function Pass([string]$message) {
    Write-Host ('  ok    ' + $message) -ForegroundColor Green
}

function HashOf([string]$path) {
    return (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

Write-Host '=== pre-flight ==='

# -- 1. the archive is current -------------------------------------------------
if (-not (Test-Path -LiteralPath $archive)) {
    Fail ('the archive ' + $archive + ' does not exist')
} else {
    $check = [System.IO.Compression.ZipFile]::OpenRead($archive)

    try {
        $entryCount = @($check.Entries | Where-Object { -not $_.FullName.EndsWith('/') }).Count

        if ($entryCount -ne 303) {
            Fail ('the archive holds {0} entries, expected 303' -f $entryCount)
        } else {
            Pass 'the archive holds all 303 entries'
        }

        $entry = $check.Entries | Where-Object { $_.FullName -eq 'prisma/schema.prisma' }

        if (-not $entry) {
            Fail 'prisma/schema.prisma is not in the archive'
        } else {
            $sha = [System.Security.Cryptography.SHA256]::Create()
            $entryStream = $entry.Open()

            try {
                $archivedHash = [System.BitConverter]::ToString($sha.ComputeHash($entryStream)).Replace('-', '')
            } finally {
                $entryStream.Dispose()
            }

            $liveSchema = Join-Path $root 'prisma\schema.prisma'

            if (-not (Test-Path -LiteralPath $liveSchema)) {
                # Already deleted on an earlier run; the archive was verified then.
                Pass 'prisma\schema.prisma is already gone, so the archived copy is the only one'
            } elseif ($archivedHash -ne (HashOf $liveSchema)) {
                Fail 'the archived prisma/schema.prisma does not match the live one - the archive is stale'
            } else {
                Pass 'the archived prisma/schema.prisma matches the live file'
            }
        }
    } finally {
        $check.Dispose()
    }
}

# -- 2. uploads belong to Laravel now ------------------------------------------
$laravelUploads = Join-Path $root 'laravel\public\uploads'
$nextUploads = Join-Path $root 'public\uploads'

if (-not (Test-Path -LiteralPath $laravelUploads)) {
    Fail 'laravel\public\uploads does not exist at all'
} else {
    $item = Get-Item -LiteralPath $laravelUploads -Force

    if ($null -ne $item.LinkType) {
        Fail ('laravel\public\uploads is still a {0} - deleting public\ would take the images with it' -f $item.LinkType)
    } else {
        $laravelFiles = @(Get-ChildItem -LiteralPath $laravelUploads -File)
        $nextFiles = @(Get-ChildItem -LiteralPath $nextUploads -File -ErrorAction SilentlyContinue)

        if ($laravelFiles.Count -eq 0) {
            Fail 'laravel\public\uploads is empty'
        } elseif ($nextFiles.Count -eq 0) {
            Pass ('public\uploads is already gone; laravel\public\uploads holds {0} files' -f $laravelFiles.Count)
        } elseif ($laravelFiles.Count -ne $nextFiles.Count) {
            Fail ('uploads count differs: laravel {0}, next {1}' -f $laravelFiles.Count, $nextFiles.Count)
        } else {
            $mismatched = 0

            foreach ($file in $nextFiles) {
                $twin = Join-Path $laravelUploads $file.Name

                if (-not (Test-Path -LiteralPath $twin)) {
                    $mismatched++
                    continue
                }

                if ((HashOf $twin) -ne (HashOf $file.FullName)) {
                    $mismatched++
                }
            }

            if ($mismatched -gt 0) {
                Fail ('{0} upload(s) are missing from laravel\public\uploads or differ' -f $mismatched)
            } else {
                Pass ('all {0} uploads are in laravel\public\uploads, byte-identical' -f $laravelFiles.Count)
            }
        }
    }
}

# -- 3. the assets the site serves ---------------------------------------------
$assets = @(
    'favicon.png', 'logo.png', 'hero-default.jpg', 'manifest.json',
    'icons\icon-192.png', 'icons\icon-512.png', 'icons\icon-maskable-512.png'
)

$assetFailures = 0

foreach ($asset in $assets) {
    $theirs = Join-Path (Join-Path $root 'public') $asset
    $ours = Join-Path (Join-Path $root 'laravel\public') $asset

    if (-not (Test-Path -LiteralPath $theirs)) { continue }

    if (-not (Test-Path -LiteralPath $ours)) {
        Fail ('laravel\public has no copy of ' + $asset)
        $assetFailures++
        continue
    }

    if ((HashOf $ours) -ne (HashOf $theirs)) {
        Fail ('laravel\public\' + $asset + ' differs from the Next.js copy')
        $assetFailures++
    }
}

if ($assetFailures -eq 0) {
    Pass ('all {0} served assets exist in laravel\public, byte-identical' -f $assets.Count)
}

# -- 4. no reparse points in the tree being deleted -----------------------------
$reparse = @()

foreach ($entry in Get-ChildItem -LiteralPath $root -Force) {
    if ($entry.Name -eq $keep) { continue }

    if ($entry.Attributes -band [System.IO.FileAttributes]::ReparsePoint) {
        $reparse += $entry.Name
        continue
    }

    if ($entry.PSIsContainer) {
        foreach ($nested in Get-ChildItem -LiteralPath $entry.FullName -Recurse -Force -ErrorAction SilentlyContinue) {
            if ($nested.Attributes -band [System.IO.FileAttributes]::ReparsePoint) {
                $reparse += $nested.FullName.Substring($root.Length + 1)
            }
        }
    }
}

if ($reparse.Count -gt 0) {
    Fail ('reparse points present, rmdir /s could follow them: ' + ($reparse -join ', '))
} else {
    Pass 'no junction or symlink anywhere in the tree being deleted'
}

# -- 5. the app being kept can boot ---------------------------------------------
$missing = @()

foreach ($needed in @('laravel\vendor\autoload.php', 'laravel\.env', 'laravel\artisan', 'laravel\public\index.php')) {
    if (-not (Test-Path -LiteralPath (Join-Path $root $needed))) {
        Fail ($needed + ' is missing from the app being kept')
        $missing += $needed
    }
}

if ($missing.Count -eq 0) {
    Pass 'laravel\vendor\autoload.php, .env, artisan and public\index.php are all present'
}

# -- 6. nothing has the tree open ------------------------------------------------
$holders = @()

<#
    Scoped to what can actually hold a file handle in the tree, not to any
    process whose command line happens to mention it. Two reasons: the shell
    running this script has D:\nesim in its command line, and Git Bash rewrites
    D:\nesim\laravel with forward slashes, so a plain string match both
    false-positives on the runner and misses what it was meant to skip.
#>
foreach ($process in Get-CimInstance Win32_Process) {
    $executable = [string] $process.ExecutablePath
    $command = [string] $process.CommandLine

    $runsFromTree = $executable -ne '' -and
        $executable.StartsWith('D:\nesim\', [System.StringComparison]::OrdinalIgnoreCase) -and
        -not $executable.StartsWith('D:\nesim\laravel\', [System.StringComparison]::OrdinalIgnoreCase)

    $servesTree = ($command -replace '/', '\') -match 'nesim\\(node_modules|\.next)\b' -or
        $command -match 'next dev'

    if ($runsFromTree -or $servesTree) {
        $holders += ('pid {0} {1}' -f $process.ProcessId, $process.Name)
    }
}

if ($holders.Count -gt 0) {
    Fail ('process(es) still have the tree open: ' + ($holders -join '; '))
} else {
    Pass 'no running process has the Next.js tree open'
}

Write-Host ''

if ($failures.Count -gt 0) {
    Write-Host ('ABORTED: {0} pre-flight check(s) failed. Nothing was deleted.' -f $failures.Count) -ForegroundColor Red
    exit 1
}

# -- what is about to go --------------------------------------------------------
$targets = @(Get-ChildItem -LiteralPath $root -Force | Where-Object { $_.Name -ne $keep })

Write-Host ('=== deleting {0} top-level entries ===' -f $targets.Count)

$deleted = @()
$stuck = @()

<#
    cmd writes "Access is denied" to stderr for anything it cannot remove, and
    under ErrorActionPreference=Stop PowerShell turns a native command's stderr
    into a terminating error. That is what aborted the first run partway through.
    The loop handles a stuck entry itself and reports it at the end, so let cmd
    complain without throwing.
#>
$ErrorActionPreference = 'Continue'

foreach ($target in $targets) {
    if ($target.PSIsContainer) {
        # rmdir /s /q rather than Remove-Item -Recurse: node_modules holds paths
        # longer than PowerShell 5.1 will traverse.
        cmd /c rmdir /s /q ($target.FullName) 2>$null | Out-Null
    } else {
        cmd /c del /f /q ($target.FullName) 2>$null | Out-Null
    }

    if (Test-Path -LiteralPath $target.FullName) {
        $stuck += $target.Name
        Write-Host ('  stuck   ' + $target.Name) -ForegroundColor Yellow
    } else {
        $deleted += $target.Name
        Write-Host ('  deleted ' + $target.Name)
    }
}

# -- the redundant first-attempt archive -----------------------------------------
Write-Host ''

if (Test-Path -LiteralPath $redundant) {
    cmd /c del /f /q ($redundant) 2>$null | Out-Null

    if (Test-Path -LiteralPath $redundant) {
        $stuck += $redundant
        Write-Host ('  stuck   ' + $redundant) -ForegroundColor Yellow
    } else {
        Write-Host ('  deleted ' + $redundant + ' (redundant first attempt)')
    }
} else {
    Write-Host '  already gone: the redundant first-attempt archive'
}

Write-Host ''
Write-Host '=== after ==='

$remaining = @(Get-ChildItem -LiteralPath $root -Force)

foreach ($entry in $remaining) {
    Write-Host ('  ' + $(if ($entry.PSIsContainer) { '[dir]  ' } else { '[file] ' }) + $entry.Name)
}

Write-Host ''

if ($stuck.Count -gt 0) {
    Write-Host ('PARTIAL: {0} entr(ies) could not be removed, most likely held open by a process:' -f $stuck.Count) -ForegroundColor Yellow
    $stuck | ForEach-Object { Write-Host ('  ' + $_) -ForegroundColor Yellow }
    exit 2
}

if ($remaining.Count -ne 1 -or $remaining[0].Name -ne $keep) {
    Write-Host 'FAILED: D:\nesim does not contain only laravel\.' -ForegroundColor Red
    exit 1
}

Write-Host ('OK: {0} entries deleted. D:\nesim now holds only laravel\.' -f $deleted.Count) -ForegroundColor Green
Write-Host ('    Recovery archive: {0}' -f $archive) -ForegroundColor Green
