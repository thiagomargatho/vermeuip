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
robocopy "$root\public" "$work\public" /E /XD "$root\public\vendor" /NFL /NDL /NJH /NJS | Out-Null
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
    $src = (Get-Content "$work\public\check.php" -Raw).Replace('__TOKEN__', $token)
    [IO.File]::WriteAllText("$work\public\check.php", $src, (New-Object Text.UTF8Encoding($false)))  # sem BOM: PHP exige
}

# Verificacao do pacote como ele fica no servidor (sem a pasta "public/" acima do vendor):
# as classes Vermeuip\ carregam e os arquivos que o navegador pede existem.
$smoke = Join-Path $root 'dist\smoke.php'
Set-Content $smoke -Encoding ascii -Value @'
<?php
$dir = $argv[1];
require $dir . '/bootstrap.php';
$ok = class_exists('Vermeuip\Config')
    && class_exists('Vermeuip\GeoEndpoint')
    && is_file($dir . '/assets/vendor/cloudflare-speedtest.js')
    && is_file($dir . '/speedtest/speedtest_worker.js');
exit($ok ? 0 : 1);
'@
$ErrorActionPreference = 'Continue'
& $php $smoke "$work\public" *> $null
$smokeExit = $LASTEXITCODE
$ErrorActionPreference = 'Stop'
if ($smokeExit -ne 0) { throw "Verificacao do pacote falhou (codigo $smokeExit): autoload ou arquivos ausentes" }

# Zip com barras normais (Compress-Archive do PS 5.1 grava barras invertidas e o unzip do Linux reclama)
Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem
function New-Zip([string]$Dest, [string]$Base, [string[]]$Files) {
    if (Test-Path $Dest) { Remove-Item $Dest }
    $za = [IO.Compression.ZipFile]::Open($Dest, 'Create')
    foreach ($f in $Files) {
        $rel = $f.Substring($Base.Length).TrimStart('\', '/').Replace('\', '/')
        [void][IO.Compression.ZipFileExtensions]::CreateEntryFromFile($za, $f, $rel, 'Optimal')
    }
    $za.Dispose()
    Write-Host ("Pacote: {0} ({1:N1} MB)" -f $Dest, ((Get-Item $Dest).Length / 1MB))
}
$pub = "$work\public"
$all = Get-ChildItem $pub -Recurse -File -Force | Select-Object -ExpandProperty FullName
New-Zip (Join-Path $root 'dist\vermeuip.zip') $pub $all

# Patch (atualizacao): so codigo, vendor/ e check.php, sem as bases DB-IP nem assets pesados.
$patch = $all | Where-Object { $_ -notmatch '\\_data\\' -and $_ -notmatch '\\assets\\vendor\\' -and $_ -notmatch '\\speedtest\\' }
New-Zip (Join-Path $root 'dist\vermeuip-patch.zip') $pub $patch
if ($token) { Write-Host "check.php token: $token" }
