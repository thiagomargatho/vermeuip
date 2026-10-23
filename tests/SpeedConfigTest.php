<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Config;

final class SpeedConfigTest extends TestCase
{
    public function test_known_engines_are_accepted(): void
    {
        $this->assertSame('cloudflare', Config::normalizeEngine('cloudflare'));
        $this->assertSame('librespeed', Config::normalizeEngine('librespeed'));
    }

    public function test_unknown_engine_falls_back_to_cloudflare(): void
    {
        $this->assertSame('cloudflare', Config::normalizeEngine('speedtest-net'));
        $this->assertSame('cloudflare', Config::normalizeEngine(''));
        $this->assertSame('cloudflare', Config::normalizeEngine('LibreSpeed'));
    }

    public function test_loaded_config_always_has_a_valid_engine(): void
    {
        $this->assertContains(Config::get()['SPEED_ENGINE'], ['cloudflare', 'librespeed']);
    }
}
