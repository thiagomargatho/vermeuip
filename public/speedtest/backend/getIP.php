<?php

// Devolve so o IP do cliente (usado pelo LibreSpeed). Sem consulta externa e sem registro em log.
require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo Vermeuip\ClientIp::resolve($_SERVER, Vermeuip\Config::get()['TRUSTED_PROXIES']);
