<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Ptr;

final class PtrTest extends TestCase
{
    public function test_private_ip_returns_null_without_resolving(): void
    {
        $called = false;
        $resolver = function (string $ip) use (&$called): string {
            $called = true;
            return 'x.example';
        };
        $this->assertNull(Ptr::lookup('127.0.0.1', $resolver));
        $this->assertFalse($called);
    }

    public function test_returns_hostname_when_it_differs_from_ip(): void
    {
        $resolver = fn (string $ip): string => 'ip-177-76-43-250.user.vivozap.com.br';
        $this->assertSame('ip-177-76-43-250.user.vivozap.com.br', Ptr::lookup('177.76.43.250', $resolver));
    }

    public function test_returns_null_when_resolver_echoes_the_ip(): void
    {
        $resolver = fn (string $ip): string => $ip;
        $this->assertNull(Ptr::lookup('177.76.43.250', $resolver));
    }

    public function test_returns_null_when_resolver_fails(): void
    {
        $resolver = fn (string $ip): string|false => false;
        $this->assertNull(Ptr::lookup('177.76.43.250', $resolver));
    }
}
