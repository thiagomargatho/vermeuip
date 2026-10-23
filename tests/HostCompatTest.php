<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Rdap;

final class HostCompatTest extends TestCase
{
    public function test_guard_works_in_php_without_intl_or_mbstring(): void
    {
        $out = [];
        $code = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg(__DIR__ . '/fixtures/no_ext.php') . ' 2>&1', $out, $code);

        $this->assertSame(0, $code, implode("\n", $out));
        $this->assertSame(['xn--bcher-kva.de', 'exemplo.com', 'sem-intl'], $out);
    }

    public function test_status_comes_from_the_last_http_status_line(): void
    {
        $this->assertSame(200, Rdap::statusFromHeaders(['HTTP/1.1 301 Moved Permanently', 'Location: x', 'HTTP/2 200 OK']));
        $this->assertSame(404, Rdap::statusFromHeaders(['HTTP/1.1 404 Not Found']));
        $this->assertSame(0, Rdap::statusFromHeaders([]));
    }
}
