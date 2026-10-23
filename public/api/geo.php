<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Vermeuip\GeoEndpoint;
use Vermeuip\Json;

Json::send(200, GeoEndpoint::payload($_SERVER));
