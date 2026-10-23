<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vermeuip\Geo;

final class GeoTest extends TestCase
{
    private const KEYS = ['cidade', 'uf', 'pais', 'lat', 'lon', 'isp', 'asn'];

    private function realGeo(): Geo
    {
        $config = require __DIR__ . '/../public/_lib/config.php';
        if (!is_file($config['GEO_ASN_DB'])) {
            $this->markTestSkipped('base DB-IP ausente (rode scripts/fetch-dbip.ps1)');
        }
        return new Geo($config['GEO_CITY_DB'], $config['GEO_ASN_DB']);
    }

    public function test_private_ip_returns_all_null_without_exception(): void
    {
        $geo = new Geo('/nao/existe.mmdb', '/nao/existe2.mmdb');
        $result = $geo->lookup('127.0.0.1');
        $this->assertSame(self::KEYS, array_keys($result));
        $this->assertSame([null], array_values(array_unique($result, SORT_REGULAR)));
    }

    public function test_missing_databases_return_all_null_for_public_ip(): void
    {
        $geo = new Geo('/nao/existe.mmdb', '/nao/existe2.mmdb');
        $result = $geo->lookup('8.8.8.8');
        $this->assertSame([null], array_values(array_unique($result, SORT_REGULAR)));
    }

    public function test_uf_from_state_name(): void
    {
        $this->assertSame('SP', Geo::ufFromName('São Paulo'));
        $this->assertSame('SP', Geo::ufFromName('Sao Paulo'));
        $this->assertNull(Geo::ufFromName('Texas'));
    }

    public function test_asn_and_isp_for_known_ip(): void
    {
        $result = $this->realGeo()->lookup('8.8.8.8');
        $this->assertSame(15169, $result['asn']);
        $this->assertStringContainsString('Google', (string) $result['isp']);
    }
}
