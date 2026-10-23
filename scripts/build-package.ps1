# Gera dist/vermeuip.zip pronto para subir no public_html do cPanel.
# Inclui dependencias de producao (sem PHPUnit) e as bases DB-IP (public/_data/*.mmdb).
# Imprime o token de acesso do check.php (diagnostico temporario).
param([switch]$SemCheck)
$ErrorActionPreference = 'Stop'
$root = Resolve-Path (Join-Path $PSScriptRoot '..')
$php = Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Recurse -Filter php.exe | Select-Object -First 1 -ExpandProperty FullName
$composer = Join-Path (Split-Path $php) 'composer.phar'

foreach ($m in 'dbip-city-lite.mmdb', 'dbip-asn-lite.mmdb') {
    if (-not (Test-Path "$root\public\_data\$m")) { throw "Falta public\_data\$m. Rode scripts\fetch-dbip.ps1" }
}

$work = Join-Path $root 'dist\pkg'
if (Test-Path $work) { Remove-Item $work -Recurse -Force }
New-Item -ItemType Directory -Force "$work\public" | Out-Null

# copia public/ sem vendor, e instala so as dependencias de producao
robocopy "$root\public" "$work\public" /E /XD vendor /NFL /NDL /NJH /NJS | Out-Null
Copy-Item "$root\composer.json", "$root\composer.lock" $work
Push-Location $work
$ErrorActionPreference = 'Continue'
& $php $composer install --no-dev --optimize-autoloader --no-interaction *> $null
$exit = $LASTEXITCODE
$ErrorActionPreference = 'Stop'
Pop-Location
if ($exit -ne 0) { throw "composer install falhou (codigo $exit)" }
Copy-Item "$root\public\vendor\.htaccess" "$work\public\vendor\.htaccess" -Force

$token = $null
if ($SemCheck) {
    Remove-Item "$work\public\check.php"
} else {
    $token = -join ((48..57) + (97..122) | Get-Random -Count 24 | ForEach-Object { [char]$_ })
    (Get-Content "$work\public\check.php" -Raw).Replace('__TOKEN__', $token) | Set-Content "$work\public\check.php" -Encoding utf8 -NoNewline
}

$zip = Join-Path $root 'dist\vermeuip.zip'
if (Test-Path $zip) { Remove-Item $zip }
Compress-Archive -Path "$work\public\*" -DestinationPath $zip -CompressionLevel Optimal
Write-Host ("Pacote: {0} ({1:N1} MB)" -f $zip, ((Get-Item $zip).Length / 1MB))
if ($token) { Write-Host "check.php token: $token" }
