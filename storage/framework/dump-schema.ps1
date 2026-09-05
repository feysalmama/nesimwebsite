$s = [System.IO.File]::ReadAllText('D:\nesim\prisma\schema.prisma')
$names = @('AboutContent','LandingContent','PresidentMessage','GlobalSettings','Media','ActivityLog','User')
foreach ($m in $names) {
    $i = $s.IndexOf("model $m {")
    if ($i -lt 0) { Write-Output "MISSING $m"; continue }
    $j = $s.IndexOf("`n}", $i)
    Write-Output ("=" * 24 + " $m")
    Write-Output $s.Substring($i, $j - $i + 2)
}
