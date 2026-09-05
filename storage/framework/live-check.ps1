# Hits every locale-prefixed public route over real HTTP and reports the status
# code, so the port can be confirmed against a running server rather than only
# the test kernel. Bare paths, redirects and 404s are live-redirects.php's job.
# Read-only: GET requests only, nothing is written.
#
# Usage: powershell -File storage/framework/live-check.ps1

# Without this, Invoke-WebRequest writes a progress record per chunk read and
# buries the results in thousands of lines of stream diagnostics.
$ProgressPreference = 'SilentlyContinue'

$base = 'http://127.0.0.1:8123'

$paths = @(
    '/', '/about', '/leadership', '/programs', '/projects', '/services',
    '/news', '/blog', '/gallery', '/resources', '/testimonials', '/insights',
    '/faq', '/impact', '/contact', '/donate', '/membership', '/volunteer',
    '/register'
)

$bad = 0

foreach ($locale in @('en', 'am', 'om')) {
    foreach ($path in $paths) {
        $url = $base + '/' + $locale + $path
        try {
            $r = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 60
            $code = $r.StatusCode
        } catch {
            $code = $_.Exception.Response.StatusCode.value__
            if (-not $code) { $code = 'ERR' }
        }

        if ($code -ne 200) {
            $bad++
            Write-Host ('  {0,-5} {1}' -f $code, $url) -ForegroundColor Red
        } else {
            Write-Host ('  {0,-5} {1}' -f $code, $url) -ForegroundColor DarkGreen
        }
    }
}

# Detail pages: take a real row for each section straight from the database.
Write-Host ''
Write-Host 'detail pages' -ForegroundColor Cyan

php storage/framework/live-detail-urls.php | ForEach-Object {
    $url = $base + $_
    try {
        $r = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 60
        $code = $r.StatusCode
    } catch {
        $code = $_.Exception.Response.StatusCode.value__
        if (-not $code) { $code = 'ERR' }
    }

    if ($code -ne 200) { $bad++ }
    $colour = if ($code -eq 200) { 'DarkGreen' } else { 'Red' }
    Write-Host ('  {0,-5} {1}' -f $code, $url) -ForegroundColor $colour
}

# A path under a valid locale that serves nothing must 404, not redirect, and a
# bare path must be prefixed - but Invoke-WebRequest cannot report a 3xx with
# redirects capped: it throws, and the exception hides the response object, so
# every case here collapsed into one meaningless check. live-redirects.php does
# these in PHP instead, where the status and Location header stay readable.

Write-Host ''
if ($bad -eq 0) {
    Write-Host 'ALL OK' -ForegroundColor Green
} else {
    Write-Host ($bad.ToString() + ' UNEXPECTED') -ForegroundColor Red
}
