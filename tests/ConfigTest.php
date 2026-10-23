<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function test_defaults(): void
    {
        $config = require __DIR__ . '/../public/_lib/config.php';

        $this->assertSame('cloudflare', $config['SPEED_ENGINE']);
        $this->assertSame([], $config['TRUSTED_PROXIES']);
        $this->assertSame(20, $config['RATE_LIMIT_PER_MIN']);
    }
}
