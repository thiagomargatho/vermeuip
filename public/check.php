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
linha('ext-openssl (obrigatoria p/ whois)', extension_loaded('openssl') ? 'ok' : 'FALTANDO');
foreach (['curl', 'intl', 'mbstring', 'sockets', 'fileinfo'] as $ext) {
    linha("ext-$ext (opcional)", extension_loaded($ext) ? 'ok' : 'ausente (ok, ha alternativa)');
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
try {
    require __DIR__ . '/bootstrap.php';
    linha('autoload Vermeuip\\Config', class_exists('Vermeuip\\Config') ? 'ok' : 'FALHA');
} catch (Throwable $e) {
    linha('autoload Vermeuip\\Config', 'FALHA: ' . $e->getMessage());
}

foreach (['dbip-asn-lite.mmdb', 'dbip-city-lite.mmdb'] as $db) {
    try {
        $reader = new MaxMind\Db\Reader(__DIR__ . '/_data/' . $db);
        $rec = $reader->get('177.76.43.250');
        linha("leitura $db", $rec ? 'ok: ' . json_encode($rec['autonomous_system_organization'] ?? $rec['city']['names']['en'] ?? '?', JSON_UNESCAPED_UNICODE) : 'sem registro');
    } catch (Throwable $e) {
        linha("leitura $db", 'FALHA: ' . get_class($e) . ': ' . $e->getMessage());
    }
}

$errno = 0;
$errstr = '';
$t = microtime(true);
$s = @stream_socket_client('tcp://1.1.1.1:443', $errno, $errstr, 4);
linha('saida TCP 1.1.1.1:443', $s ? 'ok (' . round((microtime(true) - $t) * 1000) . ' ms)' : "BLOQUEADA ($errno $errstr)");
if ($s) {
    fclose($s);
}

try {
    $rdap = Vermeuip\Rdap::httpGet('https://rdap.registro.br/domain/registro.br');
    linha('RDAP (rdap.registro.br)', is_array($rdap) ? 'ok (' . ($rdap['ldhName'] ?? '?') . ')' : 'sem resposta');
} catch (Throwable $e) {
    linha('RDAP (rdap.registro.br)', 'FALHA: ' . $e->getMessage());
}

linha('DNS (dns_get_record google.com)', @dns_get_record('google.com', DNS_A) ? 'ok' : 'FALHA');
linha('REMOTE_ADDR', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
linha('X-Forwarded-For', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '(ausente)'));
