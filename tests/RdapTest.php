<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\DomainNotFound;
use Vermeuip\Rdap;
use Vermeuip\RateLimiter;
use Vermeuip\WhoisEndpoint;

final class RdapTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vermeuip-whois-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        @rmdir($this->dir);
    }

    private function sample(): array
    {
        return [
            'ldhName' => 'exemplo.com.br',
            'status' => ['active'],
            'events' => [
                ['eventAction' => 'registration', 'eventDate' => '2019-03-12T10:00:00Z'],
                ['eventAction' => 'expiration', 'eventDate' => '2027-03-12T10:00:00Z'],
            ],
            'nameservers' => [['ldhName' => 'ns1.exemplo.com.br'], ['ldhName' => 'ns2.exemplo.com.br']],
            'entities' => [],
        ];
    }

    public function test_maps_dates_status_and_nameservers(): void
    {
        $r = Rdap::lookup('exemplo.com.br', fn (string $url): array => $this->sample());

        $this->assertSame('exemplo.com.br', $r['dominio']);
        $this->assertSame('12/03/2019', $r['registrado_em']);
        $this->assertSame('12/03/2027', $r['expira_em']);
        $this->assertSame(['Ativo'], $r['status']);
        $this->assertSame(['ns1.exemplo.com.br', 'ns2.exemplo.com.br'], $r['nameservers']);
        $this->assertNull($r['titular']);
    }

    public function test_registrant_name_is_extracted_when_public(): void
    {
        $data = $this->sample();
        $data['entities'] = [[
            'roles' => ['registrant'],
            'vcardArray' => ['vcard', [['version', [], 'text', '4.0'], ['fn', [], 'text', 'Empresa Exemplo Ltda']]],
        ]];

        $this->assertSame('Empresa Exemplo Ltda', Rdap::lookup('exemplo.com.br', fn () => $data)['titular']);
    }

    public function test_br_domain_uses_registro_br_and_others_use_bootstrap(): void
    {
        $urls = [];
        $http = function (string $url) use (&$urls): array {
            $urls[] = $url;
            return $this->sample();
        };

        Rdap::lookup('exemplo.com.br', $http);
        Rdap::lookup('exemplo.com', $http);

        $this->assertSame('https://rdap.registro.br/domain/exemplo.com.br', $urls[0]);
        $this->assertSame('https://rdap.org/domain/exemplo.com', $urls[1]);
    }

    public function test_not_found_throws(): void
    {
        $this->expectException(DomainNotFound::class);
        Rdap::lookup('naoexiste.com.br', fn () => null);
    }

    public function test_endpoint_maps_not_found_to_404(): void
    {
        [$status, $body] = WhoisEndpoint::handle(['dominio' => 'naoexiste.com.br'], '1.2.3.4', new RateLimiter($this->dir, 5), fn () => null);

        $this->assertSame(404, $status);
        $this->assertSame('Domínio não encontrado.', $body['erro']);
    }

    public function test_endpoint_rejects_invalid_domain(): void
    {
        [$status, $body] = WhoisEndpoint::handle(['dominio' => '127.0.0.1'], '1.2.3.4', new RateLimiter($this->dir, 5), fn () => []);

        $this->assertSame(400, $status);
        $this->assertSame('Domínio inválido.', $body['erro']);
    }

    public function test_endpoint_maps_network_failure_to_502(): void
    {
        $http = function (): never {
            throw new RuntimeException('timeout');
        };

        [$status, $body] = WhoisEndpoint::handle(['dominio' => 'exemplo.com'], '1.2.3.4', new RateLimiter($this->dir, 5), $http);

        $this->assertSame(502, $status);
        $this->assertSame('Serviço de consulta indisponível. Tente novamente.', $body['erro']);
    }

    public function test_endpoint_returns_429_when_rate_limited(): void
    {
        $rl = new RateLimiter($this->dir, 1);
        WhoisEndpoint::handle(['dominio' => 'exemplo.com'], '1.2.3.4', $rl, fn () => $this->sample());

        [$status] = WhoisEndpoint::handle(['dominio' => 'exemplo.com'], '1.2.3.4', $rl, fn () => $this->sample());

        $this->assertSame(429, $status);
    }
}
