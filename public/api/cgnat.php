<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Vermeuip\CgnatEndpoint;
use Vermeuip\ClientIp;
use Vermeuip\Config;
use Vermeuip\Json;
use Vermeuip\RateLimiter;

$config = Config::get();
[$status, $body] = CgnatEndpoint::handle(
    $_GET,
    ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']),
    new RateLimiter($config['RATE_DIR'], $config['RATE_LIMIT_PER_MIN'])
);
Json::send($status, $body);
