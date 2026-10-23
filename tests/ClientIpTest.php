<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\ClientIp;

final class ClientIpTest extends TestCase
{
    public function test_forwarded_header_is_ignored_without_trusted_proxy(): void
    {
        $server = ['REMOTE_ADDR' => '1.2.3.4', 'HTTP_X_FORWARDED_FOR' => '9.9.9.9'];
        $this->assertSame('1.2.3.4', ClientIp::resolve($server));
    }

    public function test_forwarded_header_is_used_when_remote_is_trusted(): void
    {
        $server = ['REMOTE_ADDR' => '1.2.3.4', 'HTTP_X_FORWARDED_FOR' => '9.9.9.9, 1.2.3.4'];
        $this->assertSame('9.9.9.9', ClientIp::resolve($server, ['1.2.3.4']));
    }

    public function test_invalid_forwarded_value_falls_back_to_remote(): void
    {
        $server = ['REMOTE_ADDR' => '1.2.3.4', 'HTTP_X_FORWARDED_FOR' => 'lixo'];
        $this->assertSame('1.2.3.4', ClientIp::resolve($server, ['1.2.3.4']));
    }

    public function test_missing_remote_addr_returns_empty_string(): void
    {
        $this->assertSame('', ClientIp::resolve([]));
    }
}
