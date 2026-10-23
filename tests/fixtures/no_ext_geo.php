<?php

// Roda com `php -n`: leitor MaxMind sem mbstring/gmp/bcmath, como no cPanel do dono.
require __DIR__ . '/../../public/bootstrap.php';

$c = Vermeuip\Config::get();
$r = (new Vermeuip\Geo($c['GEO_CITY_DB'], $c['GEO_ASN_DB']))->lookup('177.76.43.250');
echo json_encode($r, JSON_UNESCAPED_UNICODE), PHP_EOL;
