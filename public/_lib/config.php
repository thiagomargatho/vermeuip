<?php

declare(strict_types=1);

$engine = 'cloudflare'; // 'cloudflare' | 'librespeed'

return [
    'SPEED_ENGINE' => in_array($engine, ['cloudflare', 'librespeed'], true) ? $engine : 'cloudflare',
    // IPs de proxies confiaveis (ex.: Cloudflare/nginx na frente). Vazio = usar so REMOTE_ADDR.
    'TRUSTED_PROXIES' => [],
    'RATE_LIMIT_PER_MIN' => 20,
    'RATE_DIR' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vermeuip-rate',
    'GEO_CITY_DB' => __DIR__ . '/../_data/dbip-city-lite.mmdb',
    'GEO_ASN_DB' => __DIR__ . '/../_data/dbip-asn-lite.mmdb',
];
