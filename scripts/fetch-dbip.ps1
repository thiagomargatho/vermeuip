# Baixa as bases gratuitas DB-IP Lite (cidade + ASN) para public/_data/.
# Licenca CC BY 4.0: manter a atribuicao "IP Geolocation by DB-IP" no rodape.
$ErrorActionPreference = 'Stop'
$dest = Join-Path $PSScriptRoot '..\public\_data'
New-Item -ItemType Directory -Force $dest | Out-Null

$month = (Get-Date).ToString('yyyy-MM')
foreach ($name in 'dbip-city-lite', 'dbip-asn-lite') {
    $gz = Join-Path $dest "$name.mmdb.gz"
    $out = Join-Path $dest "$name.mmdb"
    $url = "https://download.db-ip.com/free/$name-$month.mmdb.gz"
    Write-Host "Baixando $url"
    Invoke-WebRequest -Uri $url -OutFile $gz
    $in = [IO.File]::OpenRead($gz)
    $gzs = New-Object IO.Compression.GzipStream($in, [IO.Compression.CompressionMode]::Decompress)
    $o = [IO.File]::Create($out)
    $gzs.CopyTo($o)
    $o.Close(); $gzs.Close(); $in.Close()
    Remove-Item $gz
    Write-Host ("{0}: {1:N1} MB" -f $out, ((Get-Item $out).Length / 1MB))
}
