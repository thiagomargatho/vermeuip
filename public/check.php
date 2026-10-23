<?php

// TEMPORARIO: diagnostico do host. Remover do servidor depois de usar.
// Acesso: /check.php?k=<token impresso pelo scripts/build-package.ps1>
declare(strict_types=1);

if (!hash_equals('__TOKEN__', (string) ($_GET['k'] ?? ''))) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

function linha(string $nome, string $valor): void
{
    echo str_pad($nome, 34) . $valor . PHP_EOL;
}

linha('PHP', PHP_VERSION . ' (' . PHP_SAPI . ')');
foreach (['curl', 'intl', 'mbstring', 'openssl', 'sockets', 'fileinfo'] as $ext) {
    linha("ext-$ext", extension_loaded($ext) ? 'ok' : 'FALTANDO');
}
linha('allow_url_fopen', ini_get('allow_url_fopen') ? 'on' : 'off');
linha('memory_limit', (string) ini_get('memory_limit'));
linha('max_execution_time', (string) ini_get('max_execution_time'));
linha('post_max_size', (string) ini_get('post_max_size'));
linha('disable_functions', (string) (ini_get('disable_functions') ?: '(nenhuma)'));
foreach (['stream_socket_client', 'dns_get_record', 'gethostbyaddr', 'curl_init', 'idn_to_ascii'] as $fn) {
    linha("funcao $fn", function_exists($fn) ? 'ok' : 'INDISPONIVEL');
}

$rateDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vermeuip-rate';
@mkdir($rateDir, 0700, true);
linha('pasta de limite de taxa gravavel', is_writable($rateDir) ? 'ok (' . $rateDir . ')' : 'NAO');

foreach (['dbip-city-lite.mmdb', 'dbip-asn-lite.mmdb'] as $db) {
    $path = __DIR__ . '/_data/' . $db;
    linha($db, is_file($path) ? round(filesize($path) / 1048576, 1) . ' MB' : 'AUSENTE');
}
linha('vendor/autoload.php', is_file(__DIR__ . '/vendor/autoload.php') ? 'ok' : 'AUSENTE');

$errno = 0;
$errstr = '';
$t = microtime(true);
$s = @stream_socket_client('tcp://1.1.1.1:443', $errno, $errstr, 4);
linha('saida TCP 1.1.1.1:443', $s ? 'ok (' . round((microtime(true) - $t) * 1000) . ' ms)' : "BLOQUEADA ($errno $errstr)");
if ($s) {
    fclose($s);
}

$ch = curl_init('https://rdap.registro.br/domain/registro.br');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8]);
curl_exec($ch);
linha('HTTPS de saida (rdap.registro.br)', curl_errno($ch) === 0 ? 'ok HTTP ' . curl_getinfo($ch, CURLINFO_RESPONSE_CODE) : 'FALHA: ' . curl_error($ch));
curl_close($ch);

linha('DNS (dns_get_record google.com)', @dns_get_record('google.com', DNS_A) ? 'ok' : 'FALHA');
linha('REMOTE_ADDR', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
linha('X-Forwarded-For', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '(ausente)'));
