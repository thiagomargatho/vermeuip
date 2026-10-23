<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\DnsEndpoint;
use Vermeuip\DnsLookup;
use Vermeuip\RateLimiter;

final class DnsLookupTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vermeuip-dns-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        @rmdir($this->dir);
    }

    public function test_mx_record_is_mapped_with_priority_and_ttl(): void
    {
        $resolver = fn (string $d, int $t): array => [['type' => 'MX', 'pri' => 10, 'target' => 'mail.exemplo.com', 'ttl' => 3600]];

        $this->assertSame(
            [['tipo' => 'MX', 'valor' => '10 mail.exemplo.com', 'ttl' => 3600]],
            (new DnsLookup($resolver))->query('exemplo.com', 'MX')
        );
    }

    public function test_txt_entries_are_joined(): void
    {
        $resolver = fn (string $d, int $t): array => [['type' => 'TXT', 'txt' => 'v=spf1 -all', 'ttl' => 60]];

        $this->assertSame('v=spf1 -all', (new DnsLookup($resolver))->query('exemplo.com', 'TXT')[0]['valor']);
    }

    public function test_a_record_uses_ip_field(): void
    {
        $resolver = fn (string $d, int $t): array => [['type' => 'A', 'ip' => '76.76.21.21', 'ttl' => 300]];

        $this->assertSame('76.76.21.21', (new DnsLookup($resolver))->query('exemplo.com', 'A')[0]['valor']);
    }

    public function test_unsupported_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new DnsLookup(fn () => []))->query('exemplo.com', 'ANY');
    }

    public function test_resolver_failure_yields_empty_list(): void
    {
        $this->assertSame([], (new DnsLookup(fn () => false))->query('exemplo.com', 'A'));
    }

    public function test_endpoint_rejects_ip_literal_domain(): void
    {
        [$status, $body] = DnsEndpoint::handle(
            ['dominio' => 'http://127.0.0.1', 'tipo' => 'A'],
            '1.2.3.4',
            new RateLimiter($this->dir, 5),
            fn () => []
        );

        $this->assertSame(400, $status);
        $this->assertSame('Domínio inválido.', $body['erro']);
    }

    public function test_endpoint_rejects_unknown_type(): void
    {
        [$status, $body] = DnsEndpoint::handle(
            ['dominio' => 'exemplo.com', 'tipo' => 'ANY'],
            '1.2.3.4',
            new RateLimiter($this->dir, 5),
            fn () => []
        );

        $this->assertSame(400, $status);
        $this->assertSame('Tipo de registro inválido.', $body['erro']);
    }

    public function test_endpoint_returns_records(): void
    {
        $resolver = fn (string $d, int $t): array => [['type' => 'A', 'ip' => '1.1.1.1', 'ttl' => 30]];

        [$status, $body] = DnsEndpoint::handle(
            ['dominio' => 'Exemplo.com', 'tipo' => 'A'],
            '1.2.3.4',
            new RateLimiter($this->dir, 5),
            $resolver
        );

        $this->assertSame(200, $status);
        $this->assertSame([['tipo' => 'A', 'valor' => '1.1.1.1', 'ttl' => 30]], $body['registros']);
    }

    public function test_endpoint_returns_429_when_rate_limited(): void
    {
        $rl = new RateLimiter($this->dir, 1);
        $query = ['dominio' => 'exemplo.com', 'tipo' => 'A'];
        DnsEndpoint::handle($query, '1.2.3.4', $rl, fn () => []);

        [$status, $body] = DnsEndpoint::handle($query, '1.2.3.4', $rl, fn () => []);

        $this->assertSame(429, $status);
        $this->assertSame('Muitas consultas. Aguarde um minuto.', $body['erro']);
    }
}
