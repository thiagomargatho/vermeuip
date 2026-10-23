<?php

// Uso: php tests/fixtures/geo_probe.php <ip>  (diagnostico manual da base DB-IP)
require __DIR__ . '/../../public/bootstrap.php';

$c = Vermeuip\Config::get();
$r = new MaxMind\Db\Reader($c['GEO_ASN_DB']);
echo 'asn db: ', json_encode($r->get($argv[1]), JSON_UNESCAPED_UNICODE), PHP_EOL;
$r = new MaxMind\Db\Reader($c['GEO_CITY_DB']);
echo 'city db: ', json_encode($r->get($argv[1]), JSON_UNESCAPED_UNICODE), PHP_EOL;
