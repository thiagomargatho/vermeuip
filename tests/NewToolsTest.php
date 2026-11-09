<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Cgnat;
use Vermeuip\CgnatEndpoint;
use Vermeuip\DnsPropagation;
use Vermeuip\IpLookupEndpoint;
use Vermeuip\PropagationEndpoint;
use Vermeuip\RateLimiter;
use Vermeuip\RequestHeaders;

final class NewToolsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vermeuip-test-' . bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        @rmdir($this->dir);
    }

    private function limiter(int $max = 100): RateLimiter
    {
        return new RateLimiter($this->dir, $max);
    }

    // ---- CGNAT

    /** @dataProvider cgnatCases */
    public function test_cgnat_verdicts(string $wan, string $public, ?string $esperado): void
    {
        $r = Cgnat::classify($wan, $public);
        $this->assertSame($esperado, $r['veredito'] ?? null);
    }

    /** @return array<string,array{string,string,?string}> */
    public static function cgnatCases(): array
    {
        return [
            'faixa cgnat inicio' => ['100.64.0.1', '200.1.2.3', 'cgnat'],
            'faixa cgnat fim' => ['100.127.255.254', '200.1.2.3', 'cgnat'],
            'logo antes da faixa' => ['100.63.255.255', '100.63.255.255', 'sem_cgnat'],
            'logo depois da faixa' => ['100.128.0.1', '100.128.0.1', 'sem_cgnat'],
            'privado 192' => ['192.168.1.2', '200.1.2.3', 'nat_duplo'],
            'privado 10' => ['10.0.0.5', '200.1.2.3', 'nat_duplo'],
            'privado 172' => ['172.20.0.5', '200.1.2.3', 'nat_duplo'],
            '172 fora da faixa privada' => ['172.32.0.5', '172.32.0.5', 'sem_cgnat'],
            'igual ao publico' => ['200.1.2.3', '200.1.2.3', 'sem_cgnat'],
            'publico diferente' => ['200.1.2.3', '201.9.9.9', 'diferente'],
            'visitante em ipv6' => ['200.1.2.3', '2804:14c::1', 'sem_comparacao'],
            'wan ipv6' => ['2804:14c::1', '200.1.2.3', 'ipv6'],
            'com espacos' => [' 100.70.1.1 ', '200.1.2.3', 'cgnat'],
            'texto' => ['abc', '200.1.2.3', null],
            'vazio' => ['', '200.1.2.3', null],
            'loopback' => ['127.0.0.1', '200.1.2.3', null],
            'link local' => ['169.254.1.1', '200.1.2.3', null],
        ];
    }

    public function test_cgnat_endpoint(): void
    {
        [$status, $body] = CgnatEndpoint::handle(['wan' => '100.72.10.5'], '200.1.2.3', $this->limiter());
        $this->assertSame(200, $status);
        $this->assertSame('cgnat', $body['veredito']);
        $this->assertSame('200.1.2.3', $body['ip_publico']);

        [$status] = CgnatEndpoint::handle(['wan' => 'x'], '200.1.2.3', $this->limiter());
        $this->assertSame(400, $status);
    }

    // ---- localizar IP

    public function test_ip_lookup_rejects_invalid_and_private(): void
    {
        foreach (['', 'abc', '999.1.1.1', '192.168.0.1', '127.0.0.1', '10.1.1.1', 'fe80::1', 'exemplo.com'] as $ip) {
            [$status, $body] = IpLookupEndpoint::handle(['ip' => $ip], '200.1.2.3', $this->limiter());
            $this->assertSame(400, $status, $ip);
            $this->assertArrayHasKey('erro', $body);
        }
    }

    public function test_ip_lookup_public_ip_has_all_keys(): void
    {
        [$status, $body] = IpLookupEndpoint::handle(['ip' => ' 8.8.8.8 '], '200.1.2.3', $this->limiter(), static fn () => 'dns.google');
        $this->assertSame(200, $status);
        $this->assertSame(['ip', 'ptr', 'cidade', 'uf', 'pais', 'lat', 'lon', 'isp', 'asn'], array_keys($body));
        $this->assertSame('8.8.8.8', $body['ip']);
        $this->assertSame('dns.google', $body['ptr']);
    }

    public function test_ip_lookup_is_rate_limited(): void
    {
        $limiter = $this->limiter(1);
        IpLookupEndpoint::handle(['ip' => '8.8.8.8'], '200.1.2.3', $limiter, static fn () => false);
        [$status] = IpLookupEndpoint::handle(['ip' => '8.8.8.8'], '200.1.2.3', $limiter, static fn () => false);
        $this->assertSame(429, $status);
    }

    // ---- cabecalhos

    public function test_headers_hide_credentials_and_put_user_agent_first(): void
    {
        $h = RequestHeaders::visible([
            'HTTP_ACCEPT_LANGUAGE' => 'pt-BR',
            'HTTP_COOKIE' => 'sessao=segredo',
            'HTTP_AUTHORIZATION' => 'Bearer x',
            'HTTP_PROXY_AUTHORIZATION' => 'Basic x',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'HTTP_SEC_CH_UA' => '"Chromium"',
            'REMOTE_ADDR' => '200.1.2.3',
            'HTTP_X_LONGO' => str_repeat('a', 900),
        ]);

        $this->assertSame(['User-Agent', 'Accept-Language', 'Sec-Ch-Ua', 'X-Longo'], array_column($h, 'nome'));
        $this->assertSame('Mozilla/5.0', $h[0]['valor']);
        $this->assertSame(500, strlen($h[3]['valor']));
        $this->assertStringNotContainsString('segredo', (string) json_encode($h));
    }

    // ---- propagacao

    public function test_propagation_parses_answers_and_detects_consistency(): void
    {
        $respostas = [
            'dns.google' => '{"Status":0,"Answer":[{"name":"x.","type":5,"TTL":60,"data":"alvo.com."},{"name":"alvo.com.","type":1,"TTL":300,"data":"2.2.2.2"},{"name":"alvo.com.","type":1,"TTL":300,"data":"1.1.1.1"}]}',
            'cloudflare-dns.com' => '{"Status":0,"Answer":[{"type":1,"TTL":120,"data":"1.1.1.1"},{"type":1,"TTL":120,"data":"2.2.2.2"}]}',
            'dns.adguard-dns.com' => null,
            'doh.dns.sb' => 'nao e json',
        ];
        $http = static function (string $url) use ($respostas): ?string {
            return $respostas[parse_url($url, PHP_URL_HOST)];
        };

        $r = (new DnsPropagation($http))->query('exemplo.com.br', 'a');

        $this->assertSame(['Google', 'Cloudflare', 'AdGuard', 'DNS.SB'], array_column($r, 'resolvedor'));
        $this->assertSame(['1.1.1.1', '2.2.2.2'], $r[0]['valores']); // ordenado, CNAME ignorado
        $this->assertSame(300, $r[0]['ttl']);
        $this->assertSame([true, true, false, false], array_column($r, 'ok'));
        $this->assertTrue(DnsPropagation::consistent($r));
    }

    public function test_propagation_inconsistent_nxdomain_and_txt_quotes(): void
    {
        $http = static function (string $url): ?string {
            return match (parse_url($url, PHP_URL_HOST)) {
                'dns.google' => '{"Status":0,"Answer":[{"type":16,"TTL":5,"data":"\"v=spf1 \" \"-all\""}]}',
                'cloudflare-dns.com' => '{"Status":3}',
                default => null,
            };
        };

        $r = (new DnsPropagation($http))->query('exemplo.com.br', 'TXT');

        $this->assertSame(['v=spf1 -all'], $r[0]['valores']);
        $this->assertTrue($r[1]['ok']);
        $this->assertSame([], $r[1]['valores']);
        $this->assertFalse(DnsPropagation::consistent($r));
    }

    public function test_propagation_sends_domain_and_type_and_rejects_bad_input(): void
    {
        $urls = [];
        $http = static function (string $url) use (&$urls): ?string {
            $urls[] = $url;
            return null;
        };

        [$status, $body] = PropagationEndpoint::handle(['dominio' => 'https://Exemplo.com.br/x', 'tipo' => 'mx'], '200.1.2.3', $this->limiter(), $http);
        $this->assertSame(200, $status);
        $this->assertSame('exemplo.com.br', $body['dominio']);
        $this->assertFalse($body['consistente']); // ninguem respondeu
        $this->assertCount(4, $urls);
        $this->assertStringEndsWith('?name=exemplo.com.br&type=MX', $urls[0]);

        [$status] = PropagationEndpoint::handle(['dominio' => '127.0.0.1'], '200.1.2.3', $this->limiter(), $http);
        $this->assertSame(400, $status);
        [$status] = PropagationEndpoint::handle(['dominio' => 'exemplo.com.br', 'tipo' => 'SOA'], '200.1.2.3', $this->limiter(), $http);
        $this->assertSame(400, $status);
        $this->assertCount(4, $urls); // entradas invalidas nao disparam requisicoes
    }
}
