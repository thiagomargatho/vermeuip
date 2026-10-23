<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Vermeuip\ClientIp;
use Vermeuip\Config;
use Vermeuip\Json;
use Vermeuip\RateLimiter;
use Vermeuip\WhoisEndpoint;

$config = Config::get();
[$status, $body] = WhoisEndpoint::handle(
    $_GET,
    ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']),
    new RateLimiter($config['RATE_DIR'], $config['RATE_LIMIT_PER_MIN'])
);
Json::send($status, $body);
