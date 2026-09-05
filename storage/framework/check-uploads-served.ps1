<#
    Proves every file now in laravel\public\uploads is served by the Laravel
    app over HTTP, with the same byte length it has on disk. Run after
    adopt-uploads.ps1 and with `artisan serve` up on 127.0.0.1:8000.

    powershell -ExecutionPolicy Bypass -File storage\framework\check-uploads-served.ps1
#>

$ErrorActionPreference = 'Continue'

$dir  = 'D:\nesim\laravel\public\uploads'
$base = 'http://127.0.0.1:8000/uploads/'

$files = @(Get-ChildItem -LiteralPath $dir -File)
$ok = 0
$problems = @()

foreach ($file in $files) {
    try {
        $response = Invoke-WebRequest -Uri ($base + $file.Name) -Method Head -TimeoutSec 20 -UseBasicParsing

        $served = [int64] $response.Headers['Content-Length']

        if ($response.StatusCode -eq 200 -and $served -eq $file.Length) {
            $ok++
        } else {
            $problems += ('{0}: HTTP {1}, served {2} bytes, on disk {3}' -f $file.Name, $response.StatusCode, $served, $file.Length)
        }
    } catch {
        $problems += ('{0}: {1}' -f $file.Name, $_.Exception.Message)
    }
}

Write-Host ('served 200 with a matching byte length: {0} / {1}' -f $ok, $files.Count)

if ($problems.Count -gt 0) {
    Write-Host 'PROBLEMS:' -ForegroundColor Red
    $problems | ForEach-Object { Write-Host ('  ' + $_) -ForegroundColor Red }
    exit 1
}

$linkType = (Get-Item -LiteralPath $dir -Force).LinkType

if ($null -ne $linkType) {
    Write-Host ('PROBLEM: {0} is still a {1}' -f $dir, $linkType) -ForegroundColor Red
    exit 1
}

Write-Host 'no problems; public\uploads is a real directory, not a junction.' -ForegroundColor Green
