$html = (Invoke-WebRequest -Uri 'http://127.0.0.1:8000/en/about' -UseBasicParsing -TimeoutSec 90).Content

Write-Output ('bytes=' + $html.Length)

# A leaked brace means LocaleText fell through to "not JSON, return the raw string".
Write-Output ('contains {"en": = ' + $html.Contains('{"en":'))
Write-Output ('contains &quot;en&quot; = ' + $html.Contains('&quot;en&quot;'))

foreach ($needle in @('To empower underserved', 'An Ethiopia where every child', 'Community-first thinking', 'Our History', 'Founded in 2015')) {
    Write-Output ('  ' + $needle.PadRight(32) + ' -> ' + $html.Contains($needle))
}

# Show the mission paragraph as it actually renders.
$i = $html.IndexOf('To empower underserved')
if ($i -ge 0) {
    $start = [Math]::Max(0, $i - 90)
    Write-Output '--- rendered around missionText ---'
    Write-Output $html.Substring($start, [Math]::Min(620, $html.Length - $start))
}
