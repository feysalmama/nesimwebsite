$root = 'D:\nesim\laravel\storage\framework\nextjs-src'

Get-ChildItem -LiteralPath (Join-Path $root 'app') -Recurse -Filter '*.tsx' | ForEach-Object {
    $lines = (Get-Content -LiteralPath $_.FullName | Measure-Object -Line).Lines
    $rel = $_.FullName.Substring($root.Length + 1)
    '{0,5}  {1}' -f $lines, $rel
} | Sort-Object

Write-Host '=== components ==='
Get-ChildItem -LiteralPath (Join-Path $root 'components') -Recurse -Filter '*.tsx' | ForEach-Object {
    $lines = (Get-Content -LiteralPath $_.FullName | Measure-Object -Line).Lines
    '{0,5}  {1}' -f $lines, $_.Name
} | Sort-Object

Write-Host '=== api routes ==='
Get-ChildItem -LiteralPath (Join-Path $root 'app\api') -Recurse -Filter '*.ts' | ForEach-Object {
    $rel = $_.FullName.Substring($root.Length + 1)
    '      ' + $rel
}
