param([string]$Filter = '')
$d = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Recurse -Filter php.exe -ErrorAction SilentlyContinue | Select-Object -First 1 -ExpandProperty FullName
if (-not $d) { $d = 'php' }
Set-Location (Join-Path $PSScriptRoot '..')
$args2 = @('public/vendor/bin/phpunit')
if ($Filter) { $args2 += @('--filter', $Filter) }
$out = & $d @args2 *>&1 | Out-String
($out -split "`n") | Select-Object -Last 14
