<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\OutboundBlocked;
use Vermeuip\PortProbe;
use Vermeuip\PortsEndpoint;
use Vermeuip\RateLimiter;

final class PortProbeTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/vermeuip-ports-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        @rmdir($this->dir);
    }

    /** @return array{resource,int} */
    private function listen(): array
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $this->assertNotFalse($server, (string) $errstr);
        $port = (int) substr(strrchr((string) stream_socket_get_name($server, false), ':'), 1);
        return [$server, $port];
    }

    public function test_open_port_is_reported_open(): void
    {
        [$server, $port] = $this->listen();

        $this->assertSame('aberta', PortProbe::check('127.0.0.1', $port, 1.0));

        fclose($server);
    }

    public function test_closed_port_is_reported_closed_or_filtered(): void
    {
        [$server, $port] = $this->listen();
        fclose($server);

        $this->assertSame('fechada_ou_filtrada', PortProbe::check('127.0.0.1', $port, 1.0));
    }

    public function test_connector_that_reports_blocked_outbound_propagates(): void
    {
        $this->expectException(OutboundBlocked::class);
        PortProbe::check('203.0.113.1', 80, 1.0, function (): never {
            throw new OutboundBlocked();
        });
    }

    public function test_endpoint_rejects_private_client_ip(): void
    {
        [$status, $body] = PortsEndpoint::handle(['porta' => '443'], '192.168.0.10', new RateLimiter($this->dir, 5), fn () => true);

        $this->assertSame(400, $status);
        $this->assertSame('Seu IP não é público, então não dá para testar portas.', $body['erro']);
    }

    public function test_endpoint_rejects_invalid_port(): void
    {
        foreach (['0', '70000', '', 'abc'] as $bad) {
            [$status, $body] = PortsEndpoint::handle(['porta' => $bad], '177.76.43.250', new RateLimiter($this->dir, 50), fn () => true);

            $this->assertSame(400, $status, $bad);
            $this->assertSame('Porta inválida. Use um número de 1 a 65535.', $body['erro']);
        }
    }

    public function test_endpoint_always_probes_the_clients_own_ip_ignoring_body_ip(): void
    {
        $probed = null;
        $connector = function (string $address) use (&$probed): bool {
            $probed = $address;
            return true;
        };

        [$status, $body] = PortsEndpoint::handle(
            ['porta' => '443', 'ip' => '8.8.8.8', 'host' => '1.1.1.1'],
            '177.76.43.250',
            new RateLimiter($this->dir, 5),
            $connector
        );

        $this->assertSame(200, $status);
        $this->assertSame('tcp://177.76.43.250:443', $probed);
        $this->assertSame(['ip' => '177.76.43.250', 'porta' => 443, 'estado' => 'aberta'], $body);
    }

    public function test_endpoint_maps_blocked_outbound_to_503(): void
    {
        $connector = function (): never {
            throw new OutboundBlocked();
        };

        [$status, $body] = PortsEndpoint::handle(['porta' => '22'], '177.76.43.250', new RateLimiter($this->dir, 5), $connector);

        $this->assertSame(503, $status);
        $this->assertSame('Este servidor não permite testes de porta de saída.', $body['erro']);
    }

    public function test_endpoint_returns_429_when_rate_limited(): void
    {
        $rl = new RateLimiter($this->dir, 1);
        PortsEndpoint::handle(['porta' => '22'], '177.76.43.250', $rl, fn () => false);

        [$status] = PortsEndpoint::handle(['porta' => '22'], '177.76.43.250', $rl, fn () => false);

        $this->assertSame(429, $status);
    }
}
