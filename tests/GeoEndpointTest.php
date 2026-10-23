<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\GeoEndpoint;

final class GeoEndpointTest extends TestCase
{
    public function test_local_ip_returns_ip_and_null_details(): void
    {
        $payload = GeoEndpoint::payload(['REMOTE_ADDR' => '127.0.0.1']);

        $this->assertSame('127.0.0.1', $payload['ip']);
        $this->assertNull($payload['cidade']);
        $this->assertNull($payload['ptr']);
        $this->assertNull($payload['isp']);
    }

    public function test_payload_has_all_keys(): void
    {
        $payload = GeoEndpoint::payload(['REMOTE_ADDR' => '127.0.0.1']);

        $this->assertSame(
            ['ip', 'ptr', 'cidade', 'uf', 'pais', 'lat', 'lon', 'isp', 'asn'],
            array_keys($payload)
        );
    }

    public function test_forged_forwarded_header_is_ignored(): void
    {
        $payload = GeoEndpoint::payload(['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '8.8.8.8']);

        $this->assertSame('127.0.0.1', $payload['ip']);
    }

    public function test_json_encode_keeps_utf8(): void
    {
        $this->assertSame('{"cidade":"Ribeirão Preto"}', \Vermeuip\Json::encode(['cidade' => 'Ribeirão Preto']));
    }
}
