<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Vermeuip\ClientIp;
use Vermeuip\Config;
use Vermeuip\Json;
use Vermeuip\PropagationEndpoint;
use Vermeuip\RateLimiter;

$config = Config::get();
[$status, $body] = PropagationEndpoint::handle(
    $_GET,
    ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']),
    // cada consulta dispara 4 requisicoes de saida, entao o limite e menor que o das outras
    new RateLimiter($config['RATE_DIR'], 6)
);
Json::send($status, $body);
