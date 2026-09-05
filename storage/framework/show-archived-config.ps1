Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::OpenRead('D:\nesim-nextjs-source.zip')

foreach ($name in @('tailwind.config.ts', 'package.json')) {
    $entry = $archive.Entries | Where-Object { $_.FullName -eq $name } | Select-Object -First 1

    if (-not $entry) {
        Write-Host "=== $name : NOT IN ARCHIVE ==="
        continue
    }

    Write-Host "=== $name ==="
    $reader = New-Object System.IO.StreamReader($entry.Open())
    $reader.ReadToEnd()
    $reader.Close()
}

$archive.Dispose()
