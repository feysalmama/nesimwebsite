<#
    Finds what is holding the Next.js tree open, and what keeps recreating .next.
    powershell -ExecutionPolicy Bypass -File storage\framework\find-locks.ps1
#>

$ErrorActionPreference = 'Continue'

Write-Host '=== node.exe processes and their command lines ==='

foreach ($process in Get-CimInstance Win32_Process -Filter "Name = 'node.exe'") {
    $command = [string] $process.CommandLine

    if ($command.Length -gt 260) {
        $command = $command.Substring(0, 260) + '...'
    }

    Write-Host ('  pid {0,-7} {1}' -f $process.ProcessId, $command)
}

Write-Host ''
Write-Host '=== any process whose command line mentions D:\nesim outside laravel ==='

foreach ($process in Get-CimInstance Win32_Process) {
    $command = [string] $process.CommandLine

    if ($command -eq '') { continue }
    if ($command -notmatch 'nesim') { continue }
    if ($command -match 'nesim\\laravel') { continue }

    $short = if ($command.Length -gt 260) { $command.Substring(0, 260) + '...' } else { $command }

    Write-Host ('  pid {0,-7} {1,-16} {2}' -f $process.ProcessId, $process.Name, $short)
}

Write-Host ''
Write-Host '=== .next state ==='

$next = 'D:\nesim\.next'

if (Test-Path -LiteralPath $next) {
    $files = @(Get-ChildItem -LiteralPath $next -Recurse -File -Force -ErrorAction SilentlyContinue)

    Write-Host ('  exists, {0} files' -f $files.Count)

    $files | Sort-Object LastWriteTime -Descending | Select-Object -First 5 | ForEach-Object {
        Write-Host ('    {0:HH:mm:ss}  {1}' -f $_.LastWriteTime, $_.FullName.Substring('D:\nesim\'.Length))
    }
} else {
    Write-Host '  does not exist'
}

Write-Host ''
Write-Host '=== listening TCP ports owned by node.exe ==='

$nodePids = @(Get-Process node -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Id)

foreach ($connection in Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue) {
    if ($nodePids -contains $connection.OwningProcess) {
        Write-Host ('  pid {0,-7} {1}:{2}' -f $connection.OwningProcess, $connection.LocalAddress, $connection.LocalPort)
    }
}

Write-Host ''
Write-Host ('now: {0:HH:mm:ss}' -f (Get-Date))
