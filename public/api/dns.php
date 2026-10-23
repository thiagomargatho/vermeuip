<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Vermeuip\ClientIp;
use Vermeuip\Config;
use Vermeuip\DnsEndpoint;
use Vermeuip\Json;
use Vermeuip\RateLimiter;

$config = Config::get();
[$status, $body] = DnsEndpoint::handle(
    $_GET,
    ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']),
    new RateLimiter($config['RATE_DIR'], $config['RATE_LIMIT_PER_MIN'])
);
Json::send($status, $body);
