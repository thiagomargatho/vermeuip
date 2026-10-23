<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Vermeuip\ClientIp;
use Vermeuip\Config;
use Vermeuip\Json;
use Vermeuip\PortsEndpoint;
use Vermeuip\RateLimiter;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    Json::send(405, ['erro' => 'Método não permitido.']);
}

$config = Config::get();
$body = json_decode((string) file_get_contents('php://input'), true);

[$status, $payload] = PortsEndpoint::handle(
    is_array($body) ? $body : [],
    ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']),
    new RateLimiter($config['RATE_DIR'], $config['RATE_LIMIT_PER_MIN'])
);
Json::send($status, $payload);
