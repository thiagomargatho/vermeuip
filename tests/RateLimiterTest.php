<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\RateLimiter;

final class RateLimiterTest extends TestCase
{
    private string $dir;
    private int $clock = 1000;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vermeuip-test-' . bin2hex(random_bytes(4));
        $this->clock = 1000;
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        @rmdir($this->dir);
    }

    private function limiter(int $max): RateLimiter
    {
        return new RateLimiter($this->dir, $max, fn (): int => $this->clock);
    }

    public function test_blocks_after_limit_per_key(): void
    {
        $rl = $this->limiter(3);
        $this->assertTrue($rl->hit('a'));
        $this->assertTrue($rl->hit('a'));
        $this->assertTrue($rl->hit('a'));
        $this->assertFalse($rl->hit('a'));
        $this->assertTrue($rl->hit('b'));
    }

    public function test_window_resets_after_sixty_seconds(): void
    {
        $rl = $this->limiter(1);
        $this->assertTrue($rl->hit('a'));
        $this->assertFalse($rl->hit('a'));
        $this->clock += 61;
        $this->assertTrue($rl->hit('a'));
    }

    public function test_key_is_never_stored_in_clear(): void
    {
        $rl = $this->limiter(3);
        $rl->hit('203.0.113.42');
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            $this->assertStringNotContainsString('203.0.113.42', basename($file));
            $this->assertStringNotContainsString('203.0.113.42', (string) file_get_contents($file));
        }
    }
}
